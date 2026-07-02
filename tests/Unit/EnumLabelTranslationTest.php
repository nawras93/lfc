<?php

namespace Tests\Unit;

use App\Enums\Country;
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
}
