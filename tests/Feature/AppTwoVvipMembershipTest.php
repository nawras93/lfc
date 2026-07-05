<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\OfferAudience;
use App\Filament\AppTwo\Resources\MembershipTiers\MembershipTierResource;
use App\Filament\AppTwo\Resources\MembershipTiers\Pages\CreateMembershipTier;
use App\Filament\AppTwo\Resources\VvipMembers\Pages\CreateVvipMember;
use App\Filament\AppTwo\Resources\VvipMembers\VvipMemberResource;
use App\Models\MembershipBenefit;
use App\Models\MembershipTier;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\User;
use App\Support\AppContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppTwoVvipMembershipTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();
    }

    protected function tearDown(): void
    {
        app(AppContext::class)->clear();
        Filament::setCurrentPanel(null);

        parent::tearDown();
    }

    public function test_vvip_member_resource_create_sets_vvip_defaults_and_query_returns_only_vvip_members(): void
    {
        $this->actingAs($this->admin);
        app(AppContext::class)->setCurrent(AppKey::AppTwo);
        Filament::setCurrentPanel('admin-app-two');

        $tier = MembershipTier::factory()->create([
            'app' => AppKey::AppTwo,
            'name' => 'Gold',
            'level' => 2,
        ]);

        Livewire::test(CreateVvipMember::class)
            ->fillForm([
                'name' => 'Gold Supporter',
                'email' => 'gold.supporter@example.com',
                'password' => 'secret123',
                'phone' => '+97450000000',
                'membership_tier_id' => $tier->id,
                'member_number' => 'LSC-000123',
                'membership_valid_until' => '2027-06-30',
            ])
            ->call('create')
            ->assertHasNoErrors();

        $member = ParentAccount::withoutAppScope()
            ->where('email', 'gold.supporter@example.com')
            ->firstOrFail();

        $this->assertSame(AccountType::VvipMember, $member->account_type);
        $this->assertTrue($member->is_vvip);
        $this->assertSame(AppKey::AppTwo, $member->app);
        $this->assertSame($tier->id, $member->membership_tier_id);
        $this->assertNotNull($member->accepted_at);

        ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::VvipMember,
            'is_vvip' => true,
        ]);
        ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
        ]);

        $memberIds = VvipMemberResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($member->id, $memberIds);
        $this->assertTrue(VvipMemberResource::getEloquentQuery()->get()->every(
            fn (ParentAccount $record): bool => $record->app === AppKey::AppTwo
                && $record->account_type === AccountType::VvipMember,
        ));
    }

    public function test_me_benefits_returns_localized_ordered_benefits_for_vvip_member_and_null_for_non_membership_accounts(): void
    {
        $tier = MembershipTier::factory()->create([
            'app' => AppKey::AppTwo,
            'name' => 'Gold',
            'name_ar' => 'ذهبي',
            'level' => 2,
            'accent_color' => '#C8A24A',
        ]);

        MembershipBenefit::factory()->create([
            'membership_tier_id' => $tier->id,
            'title' => 'Priority ticketing',
            'title_ar' => 'أولوية التذاكر',
            'description' => 'Early access to match tickets',
            'description_ar' => 'أولوية الوصول إلى تذاكر المباريات',
            'icon' => 'heroicon-o-ticket',
            'sort_order' => 2,
        ]);

        MembershipBenefit::factory()->create([
            'membership_tier_id' => $tier->id,
            'title' => 'Lounge access',
            'title_ar' => 'دخول الصالة',
            'description' => 'Access to the members lounge',
            'description_ar' => 'الدخول إلى صالة الأعضاء',
            'icon' => 'heroicon-o-star',
            'sort_order' => 1,
        ]);

        $member = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::VvipMember,
            'is_vvip' => true,
            'membership_tier_id' => $tier->id,
            'member_number' => 'LSC-000123',
            'membership_valid_until' => '2027-06-30',
        ]);

        $this->actingAs($member, 'sanctum')
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/me/benefits')
            ->assertOk()
            ->assertJsonPath('data.tier.name', 'ذهبي')
            ->assertJsonPath('data.tier.level', 2)
            ->assertJsonPath('data.tier.accent_color', '#C8A24A')
            ->assertJsonPath('data.member_number', 'LSC-000123')
            ->assertJsonPath('data.valid_until', '2027-06-30')
            ->assertJsonPath('data.benefits.0.title', 'دخول الصالة')
            ->assertJsonPath('data.benefits.0.description', 'الدخول إلى صالة الأعضاء')
            ->assertJsonPath('data.benefits.1.title', 'أولوية التذاكر');

        $normalMember = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
            'is_vvip' => false,
        ]);

        $this->actingAs($normalMember, 'sanctum')
            ->getJson('/api/v1/me/benefits')
            ->assertOk()
            ->assertExactJson(['data' => null]);

        $appOneParent = ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::Parent,
        ]);

        $this->actingAs($appOneParent, 'sanctum')
            ->getJson('/api/v1/me/benefits')
            ->assertOk()
            ->assertExactJson(['data' => null]);
    }

    public function test_me_includes_membership_card_block_for_vvip_member_and_null_for_normal_member(): void
    {
        $tier = MembershipTier::factory()->create([
            'app' => AppKey::AppTwo,
            'name' => 'Platinum',
            'level' => 3,
        ]);

        $member = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::VvipMember,
            'is_vvip' => true,
            'membership_tier_id' => $tier->id,
            'member_number' => 'LSC-999999',
            'membership_valid_until' => '2027-12-31',
        ]);

        $this->actingAs($member, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.membership.tier_name', 'Platinum')
            ->assertJsonPath('data.membership.tier_level', 3)
            ->assertJsonPath('data.membership.member_number', 'LSC-999999')
            ->assertJsonPath('data.membership.valid_until', '2027-12-31');

        $normalMember = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
        ]);

        $this->actingAs($normalMember, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.membership', null);
    }

    public function test_offers_are_isolated_by_app_and_vvip_audience(): void
    {
        $appOneAll = Offer::query()->create([
            'app' => AppKey::AppOne,
            'title' => 'App One All',
            'body' => 'Visible to app one',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $appOneVvip = Offer::query()->create([
            'app' => AppKey::AppOne,
            'title' => 'App One VVIP',
            'body' => 'Visible to app one VVIP',
            'audience' => OfferAudience::VVIP,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $appTwoAll = Offer::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'App Two All',
            'body' => 'Visible to app two',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $appTwoVvip = Offer::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'App Two VVIP',
            'body' => 'Visible to app two VVIP',
            'audience' => OfferAudience::VVIP,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $appTwoVvipMember = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::VvipMember,
            'is_vvip' => true,
        ]);

        $appTwoNormalMember = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
            'is_vvip' => false,
        ]);

        $appOneVvipClient = ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::VvipClient,
            'is_vvip' => true,
        ]);

        $appTwoVvipTitles = collect($this->actingAs($appTwoVvipMember, 'sanctum')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->json('data'))->pluck('title')->all();

        $this->assertContains('App Two VVIP', $appTwoVvipTitles);
        $this->assertContains('App Two All', $appTwoVvipTitles);
        $this->assertNotContains($appOneAll->title, $appTwoVvipTitles);
        $this->assertNotContains($appOneVvip->title, $appTwoVvipTitles);

        $appTwoMemberTitles = collect($this->actingAs($appTwoNormalMember, 'sanctum')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->json('data'))->pluck('title')->all();

        $this->assertContains('App Two All', $appTwoMemberTitles);
        $this->assertNotContains($appTwoVvip->title, $appTwoMemberTitles);

        $appOneTitles = collect($this->actingAs($appOneVvipClient, 'sanctum')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->json('data'))->pluck('title')->all();

        $this->assertContains('App One VVIP', $appOneTitles);
        $this->assertContains('App One All', $appOneTitles);
        $this->assertNotContains($appTwoAll->title, $appOneTitles);
        $this->assertNotContains($appTwoVvip->title, $appOneTitles);
        $this->assertNotContains('Supporter scarf bundle for opening night', $appOneTitles);
    }

    public function test_membership_tiers_created_in_app_two_context_are_scoped_and_benefits_cascade_delete(): void
    {
        $this->actingAs($this->admin);
        app(AppContext::class)->setCurrent(AppKey::AppTwo);
        Filament::setCurrentPanel('admin-app-two');

        Livewire::test(CreateMembershipTier::class)
            ->fillForm([
                'name' => 'Diamond',
                'name_ar' => 'ماسي',
                'level' => 4,
                'accent_color' => '#111111',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $createdTier = MembershipTier::withoutAppScope()->where('name', 'Diamond')->firstOrFail();

        $this->assertSame(AppKey::AppTwo, $createdTier->app);

        MembershipTier::factory()->create([
            'app' => AppKey::AppOne,
            'name' => 'App One Tier',
        ]);

        $tierIds = MembershipTierResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($createdTier->id, $tierIds);
        $this->assertTrue(MembershipTierResource::getEloquentQuery()->get()->every(
            fn (MembershipTier $tier): bool => $tier->app === AppKey::AppTwo,
        ));

        $benefit = MembershipBenefit::factory()->create([
            'membership_tier_id' => $createdTier->id,
        ]);

        $createdTier->delete();

        $this->assertDatabaseMissing('membership_benefits', [
            'id' => $benefit->id,
        ]);
    }
}
