<?php

namespace Tests\Unit\ResidentManagement;

use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Services\ResidentManagement\Migration\MigrationClassifier;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MigrationClassifierTest extends TestCase
{
    #[DataProvider('sameAddressCases')]
    public function test_same_address_is_non_migrant(string $previousBrgy, string $previousCity, string $currentBrgy, string $currentCity): void
    {
        $same = MigrationClassifier::sameAsCurrentResidence($previousBrgy, $previousCity, $currentBrgy, $currentCity);

        $this->assertTrue($same);
        $this->assertSame(
            ResidentType::NON_MIGRANT,
            MigrationClassifier::classify($same, 2),
        );
    }

    public function test_different_barangays_with_the_same_city_are_not_the_same_address(): void
    {
        $this->assertFalse(
            MigrationClassifier::sameAsCurrentResidence('Irisan', 'Baguio City', 'Happy Hallow', 'Baguio City'),
        );
    }

    public function test_missing_barangay_does_not_match_when_cities_differ(): void
    {
        $this->assertFalse(
            MigrationClassifier::sameAsCurrentResidence('Happy Hallow', 'Baguio City', '', 'La Trinidad'),
        );
    }

    public function test_different_address_with_six_months_or_more_is_migrant(): void
    {
        $this->assertSame(
            ResidentType::MIGRANT,
            MigrationClassifier::classify(false, 6),
        );
        $this->assertSame(
            ResidentType::MIGRANT,
            MigrationClassifier::classify(false, 18),
        );
    }

    public function test_different_address_under_six_months_is_transient(): void
    {
        $this->assertSame(
            ResidentType::TRANSIENT,
            MigrationClassifier::classify(false, 0),
        );
        $this->assertSame(
            ResidentType::TRANSIENT,
            MigrationClassifier::classify(false, 5),
        );
        $this->assertSame(
            ResidentType::TRANSIENT,
            MigrationClassifier::classify(false, null),
        );
    }

    public function test_length_of_stay_is_computed_from_transfer_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-09'));

        $this->assertSame(8, MigrationClassifier::lengthOfStayMonths('2026-01'));
        $this->assertSame(8, MigrationClassifier::lengthOfStayMonths('2026-01-15'));
        $this->assertSame('0 years / 8 months', MigrationClassifier::lengthOfStayLabel(8));
        $this->assertSame('1 years / 2 months', MigrationClassifier::lengthOfStayLabel(14));

        Carbon::setTestNow();
    }

    public function test_place_normalization_title_cases_and_collapses_spaces(): void
    {
        $this->assertSame('Baguio City', MigrationClassifier::normalizePlace('  baguio   city '));
        $this->assertTrue(MigrationClassifier::placesMatch('Baguio City', 'Baguio'));
        $this->assertTrue(MigrationClassifier::placesMatch('La Trinidad', 'la  trinidad'));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function sameAddressCases(): array
    {
        return [
            'exact match' => ['Irisan', 'Baguio City', 'Irisan', 'Baguio City'],
            'city suffix' => ['Irisan', 'Baguio City', 'Irisan', 'Baguio'],
            'case and spacing' => ['irisan', 'baguio city', 'Irisan', 'Baguio'],
            'missing current barangay' => ['Happy Hallow', 'Baguio City', '', 'Baguio City'],
            'missing previous barangay' => ['', 'Baguio City', 'Happy Hallow', 'Baguio'],
            'missing both barangays' => ['', 'Baguio City', '', 'Baguio City'],
            'barangay prefix' => ['Barangay Happy Hallow', 'Baguio City', 'Happy Hallow', 'Baguio City'],
        ];
    }
}
