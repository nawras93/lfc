<?php

namespace Tests\Unit;

use App\Models\Offer;
use Tests\TestCase;

class LocalizedContentTest extends TestCase
{
    public function test_localized_returns_arabic_value_when_locale_is_arabic_and_field_is_present(): void
    {
        app()->setLocale('ar');

        $offer = new Offer([
            'title' => 'English title',
            'title_ar' => 'عنوان عربي',
        ]);

        $this->assertSame('عنوان عربي', $offer->localized('title'));
    }

    public function test_localized_falls_back_to_base_value_when_arabic_field_is_empty(): void
    {
        app()->setLocale('ar');

        $offer = new Offer([
            'title' => 'English title',
            'title_ar' => '',
        ]);

        $this->assertSame('English title', $offer->localized('title'));
    }

    public function test_localized_returns_base_value_in_english_locale(): void
    {
        app()->setLocale('en');

        $offer = new Offer([
            'title' => 'English title',
            'title_ar' => 'عنوان عربي',
        ]);

        $this->assertSame('English title', $offer->localized('title'));
    }
}
