<?php

namespace Tests\Unit\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Services\BarangayPersonnel\PersonnelPositionService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

class PersonnelPositionServiceTest extends TestCase
{
    private const USER_ID = 1;

    private const POSITION_ID = 9;

    /** @var PersonnelPositionRepositoryInterface&MockInterface */
    private $personnelPositionRepository;

    /** @var AuditLogRepositoryInterface&MockInterface */
    private $auditLogRepository;

    private PersonnelPositionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personnelPositionRepository = Mockery::mock(PersonnelPositionRepositoryInterface::class);
        $this->auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
        $this->service = new PersonnelPositionService(
            $this->personnelPositionRepository,
            $this->auditLogRepository,
        );
    }

    public function test_it_prevents_deleting_a_position_assigned_to_personnel(): void
    {
        $position = $this->makePosition();

        $this->personnelPositionRepository
            ->shouldReceive('lockById')
            ->once()
            ->with(self::POSITION_ID)
            ->andReturn($position);

        $this->personnelPositionRepository
            ->shouldReceive('isInUse')
            ->once()
            ->with($position)
            ->andReturn(true);

        $this->personnelPositionRepository->shouldNotReceive('delete');
        $this->auditLogRepository->shouldNotReceive('log');

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage(
            'This personnel position is assigned to one or more personnel records and cannot be deleted.'
        );

        $this->service->deletePosition($this->makeUser(), $position);
    }

    public function test_it_deletes_an_unassigned_position(): void
    {
        $position = $this->makePosition();

        $this->personnelPositionRepository
            ->shouldReceive('lockById')
            ->once()
            ->with(self::POSITION_ID)
            ->andReturn($position);

        $this->personnelPositionRepository
            ->shouldReceive('isInUse')
            ->once()
            ->with($position)
            ->andReturn(false);

        $this->personnelPositionRepository
            ->shouldReceive('delete')
            ->once()
            ->with($position)
            ->andReturn(true);

        $this->auditLogRepository
            ->shouldReceive('log')
            ->once()
            ->with(
                self::USER_ID,
                Action::UPDATE,
                self::POSITION_ID,
                'Delete personnel position',
                'Guest Role',
                '',
                'position_name',
                'personnel_position',
            );

        $this->service->deletePosition($this->makeUser(), $position);

        $this->personnelPositionRepository->shouldHaveReceived('delete')->once();
    }

    private function makeUser(): User
    {
        $user = new User(['username' => 'admin']);
        $user->user_id = self::USER_ID;

        return $user;
    }

    private function makePosition(): PersonnelPosition
    {
        $position = new PersonnelPosition(['position_name' => 'Guest Role']);
        $position->position_id = self::POSITION_ID;

        return $position;
    }
}
