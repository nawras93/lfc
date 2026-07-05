<?php

namespace Tests\Unit;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\Country;
use App\Enums\LedgerUnit;
use App\Enums\Nationality;
use App\Enums\PlayingPosition;
use Tests\TestCase;

class EnumLabelTranslationTest extends TestCase
{
    public function test_playing_position_labels_are_translated_in_arabic(): void
    {
        app()->setLocale('ar');

        $this->assertSame('حارس مرمى', PlayingPosition::Goalkeeper->getLabel());
    }

    public function test_playing_position_labels_are_translated_in_english(): void
    {
        app()->setLocale('en');

        $this->assertSame('Goalkeeper', PlayingPosition::Goalkeeper->getLabel());
    }

    public function test_country_and_nationality_labels_are_translated_in_arabic(): void
    {
        app()->setLocale('ar');

        $this->assertSame('قطر', Country::Qatar->getLabel());
        $this->assertSame('قطري', Nationality::Qatari->getLabel());
    }

    public function test_app_key_account_type_and_ledger_unit_labels_are_translated_with_locale_parity(): void
    {
        app()->setLocale('en');

        $this->assertSame('App Two', AppKey::AppTwo->getLabel());
        $this->assertSame('Member', AccountType::Member->getLabel());
        $this->assertSame('VVIP Member', AccountType::VvipMember->getLabel());
        $this->assertSame('Discount %', LedgerUnit::DiscountPct->getLabel());

        app()->setLocale('ar');

        $this->assertSame('التطبيق الثاني', AppKey::AppTwo->getLabel());
        $this->assertSame('عضو', AccountType::Member->getLabel());
        $this->assertSame('عضو كبار الشخصيات', AccountType::VvipMember->getLabel());
        $this->assertSame('نسبة خصم', LedgerUnit::DiscountPct->getLabel());
    }
}
