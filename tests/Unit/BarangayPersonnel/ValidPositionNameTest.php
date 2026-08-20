<?php

namespace Tests\Unit\BarangayPersonnel;

use App\Rules\ValidPositionName;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidPositionNameTest extends TestCase
{
    #[DataProvider('validPositionNames')]
    public function test_it_accepts_valid_position_names(string $name): void
    {
        $validator = Validator::make(
            ['position_name' => $name],
            ['position_name' => [new ValidPositionName]],
        );

        $this->assertFalse($validator->fails(), $name);
    }

    #[DataProvider('invalidPositionNames')]
    public function test_it_rejects_invalid_position_names(string $name, string $message): void
    {
        $validator = Validator::make(
            ['position_name' => $name],
            ['position_name' => [new ValidPositionName]],
        );

        $this->assertTrue($validator->fails(), $name);
        $this->assertSame($message, $validator->errors()->first('position_name'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validPositionNames(): array
    {
        return [
            'letters and spaces' => ['Barangay Secretary'],
            'hyphenated title' => ['Vice-Chairperson'],
            'letters and numbers' => ['Kagawad 1'],
            'abbreviation' => ['SK Chairperson'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidPositionNames(): array
    {
        return [
            'at sign' => ['Captain@Office', ValidPositionName::MESSAGE],
            'hash' => ['Chair#1', ValidPositionName::MESSAGE],
            'underscore' => ['SK_Chairperson', ValidPositionName::MESSAGE],
            'ampersand' => ['Peace & Order', ValidPositionName::MESSAGE],
            'exclamation' => ['Treasurer!', ValidPositionName::MESSAGE],
            'slash' => ['Tanod/Guard', ValidPositionName::MESSAGE],
            'period' => ['Secretary.', ValidPositionName::MESSAGE],
            'parentheses' => ['Kagawad (1)', ValidPositionName::MESSAGE],
            'only symbols' => ['@@@', ValidPositionName::MESSAGE],
            'only digits' => ['123', ValidPositionName::NUMBERS_ONLY_MESSAGE],
            'single digit' => ['1', ValidPositionName::NUMBERS_ONLY_MESSAGE],
            'digits with spaces' => ['12 34', ValidPositionName::NUMBERS_ONLY_MESSAGE],
            'digits with hyphen' => ['1-2', ValidPositionName::NUMBERS_ONLY_MESSAGE],
        ];
    }
}
