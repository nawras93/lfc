<?php

namespace Tests\Unit;

use App\Rules\LatinText;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LatinTextTest extends TestCase
{
    private LatinText $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new LatinText;
    }

    #[DataProvider('passesProvider')]
    public function test_passes(string $value): void
    {
        $failed = false;
        $this->rule->validate('attr', $value, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed, "Expected [{$value}] to pass");
    }

    #[DataProvider('failsProvider')]
    public function test_fails(string $value): void
    {
        $failed = false;
        $this->rule->validate('attr', $value, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed, "Expected [{$value}] to fail");
    }

    public static function passesProvider(): array
    {
        return [
            'null' => [''],           // empty passes (required/nullable handles presence)
            'simple latin' => ['Yousef Al'],
            'digits and symbols' => ['Player . - , 123'],
            'mixed latin with new lines' => ["John\nDoe"],
        ];
    }

    public static function failsProvider(): array
    {
        return [
            'arabic letters' => ['يوسف'],
            'arabic with latin' => ['Player يوسف'],
            'arabic supplement' => ['ݖ'],  // U+0756 Arabic letter beh with small v
            'arabic presentation form' => ['ﷺ'],  // U+FDFA
            'arabic digits only' => ['١٢٣'],  // Arabic-Indic digits
        ];
    }
}
