<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Enums\LedgerUnit;
use App\Enums\OfferAudience;
use App\Enums\PointTransactionType;
use App\Models\AttendanceScan;
use App\Models\Fixture;
use App\Models\MembershipTier;
use App\Models\NewsPost;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Standing;
use App\Models\User;
use App\Services\PointsEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class AppTwoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNews();
        $fixtures = $this->seedFixtures();
        $this->seedStandings();
        $tiers = $this->seedMembership();
        $this->seedOffers();
        $this->seedAccounts($fixtures, $tiers['Platinum']);
    }

    private function seedNews(): void
    {
        $posts = [
            [
                'title' => 'Lusail SC launches supporter membership for the new season',
                'title_ar' => 'لوسيل الرياضي يطلق عضوية المشجعين للموسم الجديد',
                'excerpt' => 'Supporters can now follow fixtures, results, and member perks in one place.',
                'excerpt_ar' => 'يمكن للمشجعين الآن متابعة المباريات والنتائج ومزايا العضوية في مكان واحد.',
                'body' => 'Lusail SC has opened supporter membership for the new season, giving fans a single place to follow fixtures, results, club news, and member-only experiences throughout the campaign.',
                'body_ar' => 'فتح نادي لوسيل الرياضي باب عضوية المشجعين للموسم الجديد، ليمنح الجماهير منصة واحدة لمتابعة المباريات والنتائج وأخبار النادي والتجارب الحصرية للأعضاء طوال الموسم.',
                'image_path' => 'news/lusail-supporter-membership.jpg',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Matchday guide: gates open early for family activities',
                'title_ar' => 'دليل يوم المباراة: فتح البوابات مبكراً للفعاليات العائلية',
                'excerpt' => 'Families are encouraged to arrive early for the pre-match plaza and junior drills.',
                'excerpt_ar' => 'يُنصح العائلات بالحضور مبكراً للاستمتاع بساحة ما قبل المباراة وتدريبات الناشئين.',
                'body' => 'Supporters attending this weekend can enter early to enjoy the pre-match plaza, community activations, and junior football drills before kickoff.',
                'body_ar' => 'يمكن للمشجعين الحاضرين هذا الأسبوع الدخول مبكراً للاستمتاع بساحة ما قبل المباراة والفعاليات المجتمعية وتدريبات كرة القدم للناشئين قبل صافرة البداية.',
                'image_path' => null,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Head coach praises comeback spirit after away draw',
                'title_ar' => 'المدرب يشيد بروح العودة بعد التعادل خارج الأرض',
                'excerpt' => 'The squad recovered from a first-half deficit to earn a point on the road.',
                'excerpt_ar' => 'استعاد الفريق توازنه بعد التأخر في الشوط الأول ليحصد نقطة خارج ملعبه.',
                'body' => 'The head coach praised the squad for its discipline and resilience after Lusail SC fought back from behind to secure an away draw in league play.',
                'body_ar' => 'أشاد المدرب بانضباط الفريق وروحه القتالية بعد أن عاد لوسيل من التأخر ليحصد تعادلاً خارج أرضه في الدوري.',
                'image_path' => 'news/comeback-draw.jpg',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Platinum members invited to exclusive pre-match lounge',
                'title_ar' => 'دعوة أعضاء البلاتينوم إلى صالة حصرية قبل المباراة',
                'excerpt' => 'The next home fixture includes a lounge experience for Platinum members and guests.',
                'excerpt_ar' => 'تتضمن المباراة المقبلة على أرضنا تجربة صالة خاصة لأعضاء البلاتينوم وضيوفهم.',
                'body' => 'Platinum members will receive access to the pre-match hospitality lounge, reserved seating guidance, and a hosted welcome before the next home fixture.',
                'body_ar' => 'سيحصل أعضاء البلاتينوم على دخول إلى صالة الضيافة قبل المباراة، مع إرشادات للمقاعد المخصصة واستقبال خاص قبل اللقاء المقبل على أرضنا.',
                'image_path' => 'news/platinum-lounge.jpg',
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($posts as $post) {
            NewsPost::query()->updateOrCreate(
                [
                    'app' => AppKey::AppTwo->value,
                    'title' => $post['title'],
                ],
                [
                    'app' => AppKey::AppTwo->value,
                    'title_ar' => $post['title_ar'],
                    'excerpt' => $post['excerpt'],
                    'excerpt_ar' => $post['excerpt_ar'],
                    'body' => $post['body'],
                    'body_ar' => $post['body_ar'],
                    'image_path' => $post['image_path'],
                    'is_published' => true,
                    'published_at' => $post['published_at'],
                ],
            );
        }
    }

    /**
     * @return array<string, Fixture>
     */
    private function seedFixtures(): array
    {
        $fixtures = [];
        $rows = [
            'umm-salal-result' => [
                'opponent' => 'Umm Salal SC',
                'competition' => 'QSL',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(35),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 2,
                'opponent_score' => 1,
            ],
            'al-wakrah-result' => [
                'opponent' => 'Al Wakrah SC',
                'competition' => 'QSL',
                'venue' => 'Al Janoub Stadium',
                'kickoff_at' => now()->subDays(28),
                'is_home' => false,
                'status' => FixtureStatus::Closed,
                'our_score' => 1,
                'opponent_score' => 1,
            ],
            'al-khor-result' => [
                'opponent' => 'Al Khor SC',
                'competition' => 'Amir Cup',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(21),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 3,
                'opponent_score' => 0,
            ],
            'qatar-sc-result' => [
                'opponent' => 'Qatar SC',
                'competition' => 'QSL',
                'venue' => 'Suhaim Bin Hamad Stadium',
                'kickoff_at' => now()->subDays(14),
                'is_home' => false,
                'status' => FixtureStatus::Closed,
                'our_score' => 0,
                'opponent_score' => 2,
            ],
            'al-shahaniya-result' => [
                'opponent' => 'Al Shahaniya SC',
                'competition' => 'QSL',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(7),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 2,
                'opponent_score' => 2,
            ],
            'al-arabi-fixture' => [
                'opponent' => 'Al Arabi SC',
                'competition' => 'QSL',
                'venue' => 'Grand Hamad Stadium',
                'kickoff_at' => now()->addDays(4),
                'is_home' => false,
                'status' => FixtureStatus::Scheduled,
                'our_score' => null,
                'opponent_score' => null,
            ],
            'al-markhiya-open' => [
                'opponent' => 'Al Markhiya SC',
                'competition' => 'QSL',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->addDays(9),
                'is_home' => true,
                'status' => FixtureStatus::OpenForScanning,
                'scan_opens_at' => now()->subHours(2),
                'scan_closes_at' => now()->addHours(8),
                'our_score' => null,
                'opponent_score' => null,
            ],
            'al-ahli-fixture' => [
                'opponent' => 'Al Ahli SC',
                'competition' => 'Amir Cup',
                'venue' => 'Al Thumama Stadium',
                'kickoff_at' => now()->addDays(15),
                'is_home' => false,
                'status' => FixtureStatus::Scheduled,
                'our_score' => null,
                'opponent_score' => null,
            ],
        ];

        foreach ($rows as $key => $row) {
            $fixtures[$key] = Fixture::query()->updateOrCreate(
                [
                    'app' => AppKey::AppTwo->value,
                    'opponent' => $row['opponent'],
                ],
                [
                    'app' => AppKey::AppTwo->value,
                    'team_id' => null,
                    'season_id' => null,
                    'competition' => $row['competition'],
                    'is_home' => $row['is_home'],
                    'venue' => $row['venue'],
                    'kickoff_at' => $row['kickoff_at'],
                    'scan_opens_at' => $row['scan_opens_at'] ?? null,
                    'scan_closes_at' => $row['scan_closes_at'] ?? null,
                    'status' => $row['status'],
                    'our_score' => $row['our_score'],
                    'opponent_score' => $row['opponent_score'],
                ],
            );
        }

        return $fixtures;
    }

    private function seedStandings(): void
    {
        $rows = [
            ['club_name' => 'Al Duhail SC', 'club_name_ar' => 'الدحيل', 'played' => 22, 'won' => 15, 'drawn' => 4, 'lost' => 3, 'goals_for' => 45, 'goals_against' => 18, 'points' => 49, 'is_own_club' => false],
            ['club_name' => 'Al Sadd SC', 'club_name_ar' => 'السد', 'played' => 22, 'won' => 14, 'drawn' => 5, 'lost' => 3, 'goals_for' => 41, 'goals_against' => 20, 'points' => 47, 'is_own_club' => false],
            ['club_name' => 'Al Gharafa SC', 'club_name_ar' => 'الغرافة', 'played' => 22, 'won' => 12, 'drawn' => 5, 'lost' => 5, 'goals_for' => 36, 'goals_against' => 24, 'points' => 41, 'is_own_club' => false],
            ['club_name' => 'Al Arabi SC', 'club_name_ar' => 'العربي', 'played' => 22, 'won' => 11, 'drawn' => 6, 'lost' => 5, 'goals_for' => 33, 'goals_against' => 25, 'points' => 39, 'is_own_club' => false],
            ['club_name' => 'Umm Salal SC', 'club_name_ar' => 'أم صلال', 'played' => 22, 'won' => 10, 'drawn' => 5, 'lost' => 7, 'goals_for' => 29, 'goals_against' => 26, 'points' => 35, 'is_own_club' => false],
            ['club_name' => 'Lusail SC', 'club_name_ar' => 'لوسيل', 'played' => 22, 'won' => 9, 'drawn' => 6, 'lost' => 7, 'goals_for' => 28, 'goals_against' => 27, 'points' => 33, 'is_own_club' => true],
            ['club_name' => 'Qatar SC', 'club_name_ar' => 'قطر', 'played' => 22, 'won' => 8, 'drawn' => 5, 'lost' => 9, 'goals_for' => 24, 'goals_against' => 31, 'points' => 29, 'is_own_club' => false],
            ['club_name' => 'Al Khor SC', 'club_name_ar' => 'الخور', 'played' => 22, 'won' => 5, 'drawn' => 4, 'lost' => 13, 'goals_for' => 19, 'goals_against' => 38, 'points' => 19, 'is_own_club' => false],
        ];

        foreach ($rows as $row) {
            Standing::query()->updateOrCreate(
                [
                    'app' => AppKey::AppTwo->value,
                    'club_name' => $row['club_name'],
                ],
                [
                    'app' => AppKey::AppTwo->value,
                    'club_name_ar' => $row['club_name_ar'],
                    'played' => $row['played'],
                    'won' => $row['won'],
                    'drawn' => $row['drawn'],
                    'lost' => $row['lost'],
                    'goals_for' => $row['goals_for'],
                    'goals_against' => $row['goals_against'],
                    'points' => $row['points'],
                    'is_own_club' => $row['is_own_club'],
                ],
            );
        }
    }

    /**
     * @return array<string, MembershipTier>
     */
    private function seedMembership(): array
    {
        $tiers = [];
        $definitions = [
            'Gold' => [
                'name_ar' => 'ذهبي',
                'level' => 1,
                'accent_color' => '#C8A24A',
                'benefits' => [
                    ['title' => 'Priority ticket access', 'title_ar' => 'أولوية الوصول إلى التذاكر', 'description' => 'Early access window for selected home matches.', 'description_ar' => 'نافذة وصول مبكر لمباريات مختارة على أرضنا.', 'icon' => 'ticket'],
                    ['title' => 'Members lounge entry', 'title_ar' => 'دخول صالة الأعضاء', 'description' => 'Access to the supporters lounge before kickoff.', 'description_ar' => 'الدخول إلى صالة المشجعين قبل انطلاق المباراة.', 'icon' => 'lounge'],
                    ['title' => 'Official gift pack', 'title_ar' => 'حقيبة هدايا رسمية', 'description' => 'Seasonal welcome pack with club merchandise.', 'description_ar' => 'حقيبة ترحيبية موسمية تتضمن منتجات النادي.', 'icon' => 'gift'],
                ],
            ],
            'Platinum' => [
                'name_ar' => 'بلاتينوم',
                'level' => 2,
                'accent_color' => '#B9C6D6',
                'benefits' => [
                    ['title' => 'Hosted lounge experience', 'title_ar' => 'تجربة صالة بضيافة خاصة', 'description' => 'Premium pre-match lounge access with hosted welcome.', 'description_ar' => 'دخول إلى صالة مميزة قبل المباراة مع استقبال خاص.', 'icon' => 'lounge'],
                    ['title' => 'Reserved parking', 'title_ar' => 'مواقف مخصصة', 'description' => 'Dedicated parking access for eligible matchdays.', 'description_ar' => 'إمكانية الوصول إلى مواقف مخصصة في أيام المباريات المؤهلة.', 'icon' => 'parking'],
                    ['title' => 'Premium ticket allocation', 'title_ar' => 'تخصيص تذاكر مميزة', 'description' => 'Preferred seating allocations for flagship fixtures.', 'description_ar' => 'تخصيص مقاعد مفضلة للمباريات الجماهيرية الكبرى.', 'icon' => 'ticket'],
                    ['title' => 'Matchday hospitality credit', 'title_ar' => 'رصيد ضيافة يوم المباراة', 'description' => 'Complimentary hospitality credit for select home fixtures.', 'description_ar' => 'رصيد ضيافة مجاني في مباريات مختارة على أرضنا.', 'icon' => 'food'],
                ],
            ],
        ];

        foreach ($definitions as $name => $definition) {
            $tier = MembershipTier::query()->updateOrCreate(
                [
                    'app' => AppKey::AppTwo->value,
                    'name' => $name,
                ],
                [
                    'app' => AppKey::AppTwo->value,
                    'name_ar' => $definition['name_ar'],
                    'level' => $definition['level'],
                    'accent_color' => $definition['accent_color'],
                    'is_active' => true,
                ],
            );

            foreach ($definition['benefits'] as $index => $benefit) {
                $tier->benefits()->updateOrCreate(
                    ['title' => $benefit['title']],
                    [
                        'title_ar' => $benefit['title_ar'],
                        'description' => $benefit['description'],
                        'description_ar' => $benefit['description_ar'],
                        'icon' => $benefit['icon'],
                        'sort_order' => $index + 1,
                    ],
                );
            }

            $tiers[$name] = $tier;
        }

        return $tiers;
    }

    private function seedOffers(): void
    {
        $offers = [
            [
                'title' => 'Supporter scarf bundle for opening night',
                'title_ar' => 'باقة وشاح المشجعين لليلة الافتتاح',
                'body' => 'All supporters can claim the opening-night scarf bundle during the current promotion window.',
                'body_ar' => 'يمكن لجميع المشجعين الحصول على باقة وشاح ليلة الافتتاح خلال فترة العرض الحالية.',
                'audience' => OfferAudience::All,
            ],
            [
                'title' => 'Platinum hospitality suite invitation',
                'title_ar' => 'دعوة إلى جناح الضيافة البلاتيني',
                'body' => 'Platinum members receive an invitation to the hosted hospitality suite for the next marquee home fixture.',
                'body_ar' => 'يحصل أعضاء البلاتينوم على دعوة إلى جناح الضيافة الخاص في المباراة الجماهيرية المقبلة على أرضنا.',
                'audience' => OfferAudience::VVIP,
            ],
            [
                'title' => 'Members training ground tour draw',
                'title_ar' => 'سحب جولة ملعب التدريب للأعضاء',
                'body' => 'All members can enter the draw for a behind-the-scenes tour at the club training ground.',
                'body_ar' => 'يمكن لجميع الأعضاء الدخول في سحب لجولة خلف الكواليس في ملعب تدريب النادي.',
                'audience' => OfferAudience::All,
            ],
        ];

        foreach ($offers as $offer) {
            Offer::query()->updateOrCreate(
                [
                    'app' => AppKey::AppTwo->value,
                    'title' => $offer['title'],
                ],
                [
                    'app' => AppKey::AppTwo->value,
                    'title_ar' => $offer['title_ar'],
                    'body' => $offer['body'],
                    'body_ar' => $offer['body_ar'],
                    'audience' => $offer['audience'],
                    'is_published' => true,
                    'valid_from' => now()->subDays(3),
                    'valid_until' => now()->addDays(45),
                ],
            );
        }
    }

    /**
     * @param  array<string, Fixture>  $fixtures
     */
    private function seedAccounts(array $fixtures, MembershipTier $platinumTier): void
    {
        $member = ParentAccount::query()->updateOrCreate(
            ['email' => 'member.demo@lfc.test'],
            [
                'app' => AppKey::AppTwo->value,
                'name' => 'Nasser Al-Marri',
                'password' => 'password',
                'phone' => '555130130',
                'whatsapp' => '555130131',
                'is_vvip' => false,
                'account_type' => AccountType::Member,
                'accepted_at' => now(),
                'invited_at' => null,
                'invitation_token' => null,
                'membership_tier_id' => null,
                'member_number' => null,
                'membership_valid_until' => null,
            ],
        );

        $vvip = ParentAccount::query()->updateOrCreate(
            ['email' => 'vvip.member.demo@lfc.test'],
            [
                'app' => AppKey::AppTwo->value,
                'name' => 'Maha Al-Sulaiti',
                'password' => 'password',
                'phone' => '555140140',
                'whatsapp' => '555140141',
                'is_vvip' => true,
                'account_type' => AccountType::VvipMember,
                'accepted_at' => now(),
                'invited_at' => null,
                'invitation_token' => null,
                'membership_tier_id' => $platinumTier->id,
                'member_number' => 'LSC-000123',
                'membership_valid_until' => '2027-06-30',
            ],
        );

        $admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->first();

        foreach ([
            $fixtures['umm-salal-result'],
            $fixtures['al-wakrah-result'],
            $fixtures['al-khor-result'],
            $fixtures['qatar-sc-result'],
            $fixtures['al-shahaniya-result'],
        ] as $index => $fixture) {
            $scan = AttendanceScan::query()->firstOrCreate(
                [
                    'parent_account_id' => $member->id,
                    'fixture_id' => $fixture->id,
                ],
                [
                    'scanned_by' => $admin?->id,
                    'scanned_at' => $fixture->kickoff_at?->copy()->subMinutes(25 + ($index * 5)) ?? now(),
                ],
            );

            $this->ensureDiscountTransaction($member, 50, $scan);
        }

        // The seeded VVIP member intentionally has no discount-accrual entries.
    }

    private function ensureDiscountTransaction(ParentAccount $account, int $bp, Model $source): void
    {
        $existing = PointTransaction::query()
            ->where('parent_account_id', $account->id)
            ->where('type', PointTransactionType::Earn)
            ->where('unit', LedgerUnit::DiscountPct->value)
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->first();

        if ($existing) {
            return;
        }

        app(PointsEngine::class)->creditAttendanceDiscount($account, $bp, $source);
    }
}
