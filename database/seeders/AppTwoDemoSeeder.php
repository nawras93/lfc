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
                'title' => 'Eyyal Al-Freej community tournament concludes',
                'title_ar' => 'ختام بطولة عيال الفريج',
                'excerpt' => 'Lusail SC wrapped up its Eyyal Al-Freej community tournament under the patronage of the Ministry of Sports and Youth.',
                'excerpt_ar' => 'اختتم نادي لوسيل بطولة عيال الفريج المجتمعية تحت رعاية وزارة الرياضة والشباب.',
                'body' => 'Lusail SC concluded the Eyyal Al-Freej community tournament with strong participation from local families and youth teams. The closing programme highlighted the club\'s community role and its partnership with the Ministry of Sports and Youth in supporting grassroots sport.',
                'body_ar' => 'اختتم نادي لوسيل بطولة عيال الفريج المجتمعية بمشاركة واسعة من العائلات والفرق العمرية. وأبرز الحفل الختامي دور النادي المجتمعي وتعاونه مع وزارة الرياضة والشباب في دعم الرياضة القاعدية.',
                'image_path' => 'news/lusail-news-1.jpg',
                'published_at' => now()->subDays(9),
            ],
            [
                'title' => 'Lusail held to a draw by Al-Bedaa in the second-division finale',
                'title_ar' => 'نادينا يتعادل مع نادي البدع الرياضي',
                'excerpt' => 'Our team drew with Al-Bedaa SC in the final round of the Qatari Second Division.',
                'excerpt_ar' => 'تعادل فريقنا أمام نادي البدع الرياضي ضمن منافسات الجولة الأخيرة لدوري الدرجة الثانية.',
                'body' => 'Lusail SC shared the points with Al-Bedaa SC in the last round of the Qatari Second Division campaign. The result closed the season with another competitive display from our squad and preserved momentum for the next stage of preparation.',
                'body_ar' => 'اقتسم نادي لوسيل النقاط مع نادي البدع الرياضي في الجولة الأخيرة من دوري الدرجة الثانية القطري. واختتمت النتيجة الموسم بأداء تنافسي جديد من فريقنا مع الحفاظ على الزخم للمرحلة المقبلة من التحضير.',
                'image_path' => 'news/lusail-news-2.jpg',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Club president honoured with the Sports & Youth Excellence Award',
                'title_ar' => 'رئيس نادي لوسيل يحصل على جائزة التميز الرياضي والشبابي',
                'excerpt' => 'Lusail SC president Nawaf Mohammed Al-Mudahka received the Sports and Youth Excellence Award on behalf of the Sumaismah Youth Centre.',
                'excerpt_ar' => 'حصل رئيس نادي لوسيل نواف محمد المضاحكة على جائزة التميز الرياضي والشبابي ممثلاً عن مركز شباب سميسمة.',
                'body' => 'Lusail SC president Nawaf Mohammed Al-Mudahka was honoured with the Sports and Youth Excellence Award while representing the Sumaismah Youth Centre. The recognition reflects his broader contribution to youth and community sport in Qatar as well as the club\'s public-service mission.',
                'body_ar' => 'حصل رئيس نادي لوسيل نواف محمد المضاحكة على جائزة التميز الرياضي والشبابي ممثلاً عن مركز شباب سميسمة. ويعكس هذا التكريم إسهامه الأوسع في خدمة الرياضة والشباب في قطر إلى جانب الدور المجتمعي الذي يقدمه النادي.',
                'image_path' => 'news/lusail-news-3.jpg',
                'published_at' => now()->subDays(3),
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
                'opponent' => 'Umm Salal',
                'opponent_ar' => 'أم صلال',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(35),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 2,
                'opponent_score' => 1,
            ],
            'al-wakrah-result' => [
                'opponent' => 'Al Wakrah',
                'opponent_ar' => 'الوكرة',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
                'venue' => 'Al Janoub Stadium',
                'kickoff_at' => now()->subDays(28),
                'is_home' => false,
                'status' => FixtureStatus::Closed,
                'our_score' => 1,
                'opponent_score' => 1,
            ],
            'al-khor-result' => [
                'opponent' => 'Al Duhail',
                'opponent_ar' => 'الدحيل',
                'competition' => 'Amir Cup',
                'competition_ar' => 'كأس الأمير',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(21),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 3,
                'opponent_score' => 0,
            ],
            'qatar-sc-result' => [
                'opponent' => 'Al Gharafa',
                'opponent_ar' => 'الغرافة',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
                'venue' => 'Suhaim Bin Hamad Stadium',
                'kickoff_at' => now()->subDays(14),
                'is_home' => false,
                'status' => FixtureStatus::Closed,
                'our_score' => 0,
                'opponent_score' => 2,
            ],
            'al-shahaniya-result' => [
                'opponent' => 'Al Arabi',
                'opponent_ar' => 'العربي',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->subDays(7),
                'is_home' => true,
                'status' => FixtureStatus::Closed,
                'our_score' => 2,
                'opponent_score' => 2,
            ],
            'al-arabi-fixture' => [
                'opponent' => 'Al Rayyan',
                'opponent_ar' => 'الريان',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
                'venue' => 'Ahmad bin Ali Stadium',
                'kickoff_at' => now()->addDays(4),
                'is_home' => false,
                'status' => FixtureStatus::Scheduled,
                'our_score' => null,
                'opponent_score' => null,
            ],
            'al-markhiya-open' => [
                'opponent' => 'Al Sadd',
                'opponent_ar' => 'السد',
                'competition' => 'Qatar Stars League',
                'competition_ar' => 'دوري نجوم قطر',
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
                'opponent' => 'Qatar SC',
                'opponent_ar' => 'نادي قطر',
                'competition' => 'Amir Cup',
                'competition_ar' => 'كأس الأمير',
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
                    'opponent_ar' => $row['opponent_ar'],
                    'competition' => $row['competition'],
                    'competition_ar' => $row['competition_ar'],
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
            ['club_name' => 'Al Sadd', 'club_name_ar' => 'السد', 'played' => 22, 'won' => 14, 'drawn' => 3, 'lost' => 5, 'goals_for' => 43, 'goals_against' => 18, 'points' => 45, 'is_own_club' => false],
            ['club_name' => 'Al Shamal', 'club_name_ar' => 'الشمال', 'played' => 22, 'won' => 12, 'drawn' => 4, 'lost' => 6, 'goals_for' => 35, 'goals_against' => 22, 'points' => 40, 'is_own_club' => false],
            ['club_name' => 'Al Rayyan', 'club_name_ar' => 'الريان', 'played' => 22, 'won' => 11, 'drawn' => 5, 'lost' => 6, 'goals_for' => 34, 'goals_against' => 24, 'points' => 38, 'is_own_club' => false],
            ['club_name' => 'Al Gharafa', 'club_name_ar' => 'الغرافة', 'played' => 22, 'won' => 10, 'drawn' => 6, 'lost' => 6, 'goals_for' => 32, 'goals_against' => 25, 'points' => 36, 'is_own_club' => false],
            ['club_name' => 'Al Duhail', 'club_name_ar' => 'الدحيل', 'played' => 22, 'won' => 9, 'drawn' => 6, 'lost' => 7, 'goals_for' => 33, 'goals_against' => 27, 'points' => 33, 'is_own_club' => false],
            ['club_name' => 'Qatar SC', 'club_name_ar' => 'نادي قطر', 'played' => 22, 'won' => 9, 'drawn' => 5, 'lost' => 8, 'goals_for' => 29, 'goals_against' => 26, 'points' => 32, 'is_own_club' => false],
            ['club_name' => 'Al Arabi', 'club_name_ar' => 'العربي', 'played' => 22, 'won' => 8, 'drawn' => 8, 'lost' => 6, 'goals_for' => 28, 'goals_against' => 26, 'points' => 32, 'is_own_club' => false],
            ['club_name' => 'Lusail SC', 'club_name_ar' => 'نادي لوسيل', 'played' => 22, 'won' => 8, 'drawn' => 6, 'lost' => 8, 'goals_for' => 27, 'goals_against' => 27, 'points' => 30, 'is_own_club' => true],
            ['club_name' => 'Al Wakrah', 'club_name_ar' => 'الوكرة', 'played' => 22, 'won' => 7, 'drawn' => 6, 'lost' => 9, 'goals_for' => 25, 'goals_against' => 30, 'points' => 27, 'is_own_club' => false],
            ['club_name' => 'Al Ahli', 'club_name_ar' => 'الأهلي', 'played' => 22, 'won' => 7, 'drawn' => 5, 'lost' => 10, 'goals_for' => 24, 'goals_against' => 31, 'points' => 26, 'is_own_club' => false],
            ['club_name' => 'Al Sailiya', 'club_name_ar' => 'السيلية', 'played' => 22, 'won' => 5, 'drawn' => 7, 'lost' => 10, 'goals_for' => 20, 'goals_against' => 33, 'points' => 22, 'is_own_club' => false],
            ['club_name' => 'Al Shahania', 'club_name_ar' => 'الشحانية', 'played' => 22, 'won' => 5, 'drawn' => 6, 'lost' => 11, 'goals_for' => 19, 'goals_against' => 34, 'points' => 21, 'is_own_club' => false],
            ['club_name' => 'Umm Salal', 'club_name_ar' => 'أم صلال', 'played' => 22, 'won' => 5, 'drawn' => 5, 'lost' => 12, 'goals_for' => 18, 'goals_against' => 36, 'points' => 20, 'is_own_club' => false],
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
