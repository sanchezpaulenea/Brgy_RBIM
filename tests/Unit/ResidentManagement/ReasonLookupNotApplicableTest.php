<?php

namespace Tests\Unit\ResidentManagement;

use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\ResidentManagement\Migration\ReasonForLeaving;
use App\Models\ResidentManagement\Migration\ReasonForTransfer;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Services\ResidentManagement\Migration\MigrationService;
use App\Services\SystemSetting\SystemSettingService;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReasonLookupNotApplicableTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[DataProvider('notApplicableLabels')]
    public function test_reason_for_leaving_detects_not_applicable(string $label): void
    {
        $reason = new ReasonForLeaving(['reason_for_leaving' => $label]);

        $this->assertTrue($reason->indicatesNotApplicable());
    }

    #[DataProvider('notApplicableLabels')]
    public function test_reason_for_transfer_detects_not_applicable(string $label): void
    {
        $reason = new ReasonForTransfer(['reason_for_transfer' => $label]);

        $this->assertTrue($reason->indicatesNotApplicable());
    }

    public function test_real_reasons_are_not_treated_as_not_applicable(): void
    {
        $this->assertFalse((new ReasonForLeaving(['reason_for_leaving' => 'Lack of employment']))->indicatesNotApplicable());
        $this->assertFalse((new ReasonForTransfer(['reason_for_transfer' => 'Housing']))->indicatesNotApplicable());
    }

    public function test_format_record_hides_not_applicable_fields_for_non_migrants(): void
    {
        $migration = new Migration([
            'previous_residence_6mos_brgy' => 'Happy Hallow',
            'previous_residence_6mos_city_municipality' => 'Baguio',
            'previous_residence_5yrs_brgy' => 'Happy Hallow',
            'previous_residence_5yrs_city_municipality' => 'Baguio',
            'resident_type_id' => ResidentType::NON_MIGRANT,
            'reason_for_leaving_id' => 16,
            'will_return_to_previous_residence' => false,
            'reason_for_transfer_id' => 6,
        ]);
        $migration->migration_id = 1;
        $migration->resident_id = 11;

        $residentType = new ResidentType(['resident_type' => 'Non-Migrant']);
        $residentType->resident_type_id = ResidentType::NON_MIGRANT;

        $leaving = new ReasonForLeaving(['reason_for_leaving' => ReasonForLeaving::NOT_APPLICABLE]);
        $leaving->reason_for_leaving_id = 16;

        $transfer = new ReasonForTransfer(['reason_for_transfer' => ReasonForTransfer::NOT_APPLICABLE]);
        $transfer->reason_for_transfer_id = 6;

        $migration->setRelation('residentType', $residentType);
        $migration->setRelation('reasonForLeaving', $leaving);
        $migration->setRelation('reasonForTransfer', $transfer);

        $service = new MigrationService(
            Mockery::mock(MigrationRepositoryInterface::class),
            Mockery::mock(AuditLogRepositoryInterface::class),
            Mockery::mock(SystemSettingService::class),
        );

        $formatted = $service->formatRecord($migration);

        $this->assertNull($formatted['reason_for_leaving_id']);
        $this->assertNull($formatted['reason_for_leaving']);
        $this->assertNull($formatted['reason_for_transfer_id']);
        $this->assertNull($formatted['reason_for_transfer']);
        $this->assertNull($formatted['will_return_to_previous_residence']);
        $this->assertNull($formatted['date_of_transfer_in_brgy']);
        $this->assertNull($formatted['duration_of_stay']);
        $this->assertSame(ResidentType::NON_MIGRANT, $formatted['resident_type_id']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function notApplicableLabels(): array
    {
        return [
            'not applicable' => ['Not Applicable'],
            'n/a' => ['N/A'],
            'na' => ['NA'],
            'none' => ['None'],
        ];
    }
}
