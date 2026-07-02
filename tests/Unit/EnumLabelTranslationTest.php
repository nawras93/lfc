<?php

namespace Tests\Unit;

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
}
