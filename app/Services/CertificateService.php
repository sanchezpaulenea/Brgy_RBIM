<?php

namespace App\Services;

use App\Models\Certificate\Certificate;
use App\Models\Certificate\TypeOfCertificate;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\CertificateRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Sociocivic\SociocivicRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use App\Repositories\Interfaces\TypeOfCertificateRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateService
{
    public const MISSING_MIGRATION_WARNING = 'Resident type is not recorded (no Migration record).';

    public const NON_SOLO_PARENT_WARNING = 'Resident is recorded as Non-Solo Parent.';

    public const SIGNATURE_MISSING_WARNING = 'No Punong Barangay signature file is on record. The certificate was generated with a blank signature line.';

    public function __construct(
        protected CertificateRepositoryInterface $certificateRepository,
        protected TypeOfCertificateRepositoryInterface $typeOfCertificateRepository,
        protected ResidentRepositoryInterface $residentRepository,
        protected MigrationRepositoryInterface $migrationRepository,
        protected SociocivicRepositoryInterface $sociocivicRepository,
        protected SettingRepositoryInterface $settingRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listTypes(): array
    {
        return $this->typeOfCertificateRepository
            ->all()
            ->map(function (TypeOfCertificate $type): array {
                $config = $this->typeConfig((int) $type->type_of_certificate_id);

                return [
                    'type_of_certificate_id' => $type->type_of_certificate_id,
                    'type_of_certificate' => $type->type_of_certificate,
                    'title' => $config['title'] ?? $type->type_of_certificate,
                    'kind' => $config['kind'] ?? null,
                    'allows_signature' => (bool) ($config['allows_signature'] ?? false),
                    'print_purpose' => (bool) ($config['print_purpose'] ?? true),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchResidents(string $term, ?int $typeId = null): array
    {
        $type = $typeId === null ? null : $this->typeOfCertificateRepository->findById($typeId);

        return $this->residentRepository
            ->searchByName($term, 20)
            ->map(function (Resident $resident) use ($type): array {
                $eligibility = $this->eligibility($resident, $type instanceof TypeOfCertificate ? $type : null);

                return [
                    'resident_id' => $resident->resident_id,
                    'name' => $this->residentListName($resident),
                    'eligible' => $eligibility['eligible'],
                    'reason' => $eligibility['reason'],
                    'warnings' => $eligibility['warnings'],
                ];
            })
            ->all();
    }

    /**
     * One row per resident, ordered by their most recent certificate.
     *
     * @return array<string, mixed>
     */
    public function historyResidents(?string $search, int $page = 1): array
    {
        $paginator = $this->certificateRepository->paginateResidentsWithCertificates($search, 20, $page);
        $residents = collect($paginator->items());
        $latest = $this->certificateRepository->latestForResidents(
            $residents->pluck('resident_id')->map(fn ($id): int => (int) $id)->all(),
        );

        return [
            'residents' => [
                'data' => $residents->map(function (Resident $resident) use ($latest): array {
                    $certificate = $latest->get($resident->resident_id);

                    return [
                        'resident_id' => $resident->resident_id,
                        'name' => $this->residentListName($resident),
                        'resident_status' => $resident->status?->resident_status,
                        'certificate_count' => (int) $resident->getAttribute('certificate_count'),
                        'latest_type_of_certificate' => $certificate?->type?->type_of_certificate,
                        'latest_purpose_of_request' => $certificate?->purpose_of_request,
                        'latest_date_of_issue' => $certificate?->date_of_issue
                            ?->timezone(config('app.timezone'))
                            ->format('Y-m-d H:i:s'),
                    ];
                })->all(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function history(Resident $resident, int $page = 1): array
    {
        $paginator = $this->certificateRepository->paginateForResident($resident->resident_id, 10, $page);

        $items = collect($paginator->items())->map(function (Certificate $certificate): array {
            return [
                'certificate_id' => $certificate->certificate_id,
                'type_of_certificate' => $certificate->type?->type_of_certificate,
                'purpose_of_request' => $certificate->purpose_of_request,
                'type_of_certificate_id' => $certificate->type_of_certificate_id,
                'representative_name' => $certificate->representative_name,
                'representative_relationship' => $certificate->representative_relationship,
                'date_of_death' => $certificate->date_of_death?->toDateString(),
                'include_income_amount' => (bool) $certificate->include_income_amount,
                'stated_income_amount' => $certificate->stated_income_amount,
                'place_of_death' => $certificate->place_of_death,
                'cause_of_death' => $certificate->cause_of_death,
                'ethnic_group' => $certificate->ethnic_group,
                'resident_since_year' => $certificate->resident_since_year,
                'stated_civil_status' => $certificate->stated_civil_status,
                'solo_parent_circumstance' => $certificate->soloParent?->circumstance,
                'solo_parent_president_name' => $certificate->soloParent?->president_name,
                'solo_parent_president_address' => $certificate->soloParent?->president_address,
                'solo_parent_neighbor_name' => $certificate->soloParent?->neighbor_name,
                'solo_parent_neighbor_address' => $certificate->soloParent?->neighbor_address,
                'solo_parent_kagawad_name' => $certificate->soloParent?->kagawad_name,
                'children' => $certificate->soloParentChildren->map(fn ($child): array => [
                    'child_name' => $child->child_name,
                    'date_of_birth' => $child->date_of_birth?->toDateString(),
                ])->all(),
                'date_of_issue' => $certificate->date_of_issue
                    ?->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s'),
                'issuer_name' => $this->issuerHistoryName($certificate->issuer),
            ];
        })->all();

        $resident->loadMissing('status');

        return [
            'resident' => [
                'resident_id' => $resident->resident_id,
                'name' => $this->residentListName($resident),
                'resident_status' => $resident->status?->resident_status,
            ],
            'certificates' => [
                'data' => $items,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'counts_by_type' => $this->certificateRepository
                ->countsByTypeForResident($resident->resident_id)
                ->map(fn (object $row): array => [
                    'type_of_certificate_id' => (int) $row->type_of_certificate_id,
                    'type_of_certificate' => $row->type_of_certificate,
                    'count' => (int) $row->certificate_count,
                ])
                ->all(),
        ];
    }

    /**
     * Hard blocks are resident status and a latest migration row of Transient.
     * A missing migration row is eligible and carries a warning. A Deceased
     * resident may receive only a Death Certificate, and a Death Certificate
     * requires that status. The solo-parent warning is added only for an
     * Affidavit of Solo Parent and does not block issuance.
     *
     * @return array{eligible: bool, reason: ?string, warnings: list<string>}
     */
    public function eligibility(Resident $resident, ?TypeOfCertificate $type = null): array
    {
        $resident->loadMissing('status');
        $statusName = trim((string) ($resident->status?->resident_status ?? ''));
        $statusKey = mb_strtolower($statusName);
        $isDeceased = $statusKey === 'deceased';
        $isDeath = $this->kind($type) === 'death';

        if ($isDeceased && ! $isDeath) {
            return [
                'eligible' => false,
                'reason' => 'A deceased resident can only be issued a Death Certificate.',
                'warnings' => [],
            ];
        }

        if ($isDeath && ! $isDeceased) {
            return [
                'eligible' => false,
                'reason' => 'A Death Certificate can only be issued when the resident status is Deceased.',
                'warnings' => [],
            ];
        }

        if (! $isDeceased && $statusKey !== 'active') {
            $label = $statusName !== '' ? $statusName : 'unknown';

            return [
                'eligible' => false,
                'reason' => "Resident status is {$label}.",
                'warnings' => [],
            ];
        }

        $migration = $this->migrationRepository->latestForResident((int) $resident->resident_id);
        $warnings = [];

        if (! $migration instanceof Migration) {
            $warnings[] = self::MISSING_MIGRATION_WARNING;
        } elseif ($this->isTransient($migration)) {
            return [
                'eligible' => false,
                'reason' => 'Transient residents are not eligible for certificates.',
                'warnings' => [],
            ];
        }

        if ($this->kind($type) === 'solo_parent') {
            $sociocivic = $this->sociocivicRepository->findByResidentId((int) $resident->resident_id);
            $soloParent = mb_strtolower(trim((string) ($sociocivic?->soloParentStatus?->solo_parent_status ?? '')));

            if ($sociocivic !== null && $soloParent === 'non-solo parent') {
                $warnings[] = self::NON_SOLO_PARENT_WARNING;
            }
        }

        return [
            'eligible' => true,
            'reason' => null,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function preview(array $input, ?User $issuer = null): array
    {
        $type = $this->requireType((int) $input['type_of_certificate_id']);
        $resident = $this->requireResident((int) $input['resident_id']);
        $eligibility = $this->eligibility($resident, $type);
        $issuedAt = now();
        $issuer?->loadMissing('personnel.position');
        $details = $this->certificateDetails($input, $type);
        $embedSignature = false;
        $document = $this->buildDocument(
            $type,
            $resident,
            $details['purpose_of_request'],
            $issuedAt,
            $this->overrides($input),
            $embedSignature,
            $issuer,
            $details,
        );

        return [
            'eligible' => $eligibility['eligible'],
            'reason' => $eligibility['reason'],
            'warnings' => $eligibility['warnings'],
            'issue_block' => $this->issuerBlock($issuer),
            'document' => $document,
        ];
    }

    /**
     * Inserts the certificate, writes both audit rows, and renders the PDF
     * in one transaction. A render failure rolls the insert back.
     *
     * @param  array<string, mixed>  $input
     * @return array{pdf: string, certificate_id: int, filename: string, warnings: list<string>}
     */
    public function issue(array $input, User $issuer): array
    {
        $type = $this->requireType((int) $input['type_of_certificate_id']);
        $resident = $this->requireResident((int) $input['resident_id']);
        $eligibility = $this->eligibility($resident, $type);

        if (! $eligibility['eligible']) {
            throw ValidationException::withMessages([
                'resident_id' => $eligibility['reason'] ?? 'This resident is not eligible for a certificate.',
            ]);
        }

        $issuer->loadMissing('personnel.position');

        if ($issuer->personnel_id === null || $issuer->personnel === null) {
            throw ValidationException::withMessages([
                'issuer' => 'Your account has no linked personnel record, so it cannot issue a certificate.',
            ]);
        }

        $attachSignature = (bool) ($input['attach_pb_signature'] ?? false);
        $this->assertSignatureAllowed($type, $attachSignature);
        $signature = $this->signatureChoice($attachSignature);

        $details = $this->certificateDetails($input, $type);
        $overrides = $this->overrides($input);
        $issuedAt = now();

        return DB::transaction(function () use ($type, $resident, $details, $issuedAt, $overrides, $signature, $issuer): array {
            $certificate = $this->certificateRepository->create([
                'resident_id' => $resident->resident_id,
                'date_of_issue' => $issuedAt->toDateTimeString(),
                'type_of_certificate_id' => $type->type_of_certificate_id,
                'issuer_id' => $issuer->user_id,
                ...$this->storedAttributes($details),
            ]);

            $document = $this->buildDocument(
                $type,
                $resident,
                $details['purpose_of_request'],
                $issuedAt,
                $overrides,
                $signature['embed'],
                $issuer,
                $details,
                (int) $certificate->certificate_id,
            );

            $this->auditLogRepository->log(
                performedByUserId: $issuer->user_id,
                actionId: Action::CREATE,
                recordId: (int) $certificate->certificate_id,
                description: 'Create certificate',
                oldValue: null,
                newValue: $this->clipAudit((string) $type->type_of_certificate, false),
                target: 'record',
                entity: 'certificate',
            );

            $this->logPrint($issuer, (int) $certificate->certificate_id, $signature['audit']);

            $pdf = $this->renderPdf($document);

            return [
                'pdf' => $pdf,
                'certificate_id' => (int) $certificate->certificate_id,
                'filename' => (Str::slug((string) $type->type_of_certificate) ?: 'certificate').'.pdf',
                'warnings' => $signature['warnings'],
            ];
        });
    }

    /**
     * Updates an issued certificate and prints it again. Issuer, resident,
     * certificate type, and the original issue date stay as stored.
     *
     * @param  array<string, mixed>  $input
     * @return array{pdf: string, certificate_id: int, filename: string, warnings: list<string>}
     */
    public function revise(array $input, Certificate $certificate, User $editor): array
    {
        $type = $this->requireType((int) $certificate->type_of_certificate_id);
        $resident = $this->requireResident((int) $certificate->resident_id);

        if ((int) $input['type_of_certificate_id'] !== (int) $type->type_of_certificate_id) {
            throw ValidationException::withMessages([
                'type_of_certificate_id' => 'The certificate type cannot be changed after it is issued.',
            ]);
        }

        if ((int) $input['resident_id'] !== (int) $resident->resident_id) {
            throw ValidationException::withMessages([
                'resident_id' => 'The certificate owner cannot be changed after it is issued.',
            ]);
        }

        $eligibility = $this->eligibility($resident, $type);

        if (! $eligibility['eligible']) {
            throw ValidationException::withMessages([
                'resident_id' => $eligibility['reason'] ?? 'This resident is not eligible for a certificate.',
            ]);
        }

        $editor->loadMissing('personnel.position');
        $attachSignature = (bool) ($input['attach_pb_signature'] ?? false);
        $this->assertSignatureAllowed($type, $attachSignature);
        $signature = $this->signatureChoice($attachSignature);
        $details = $this->certificateDetails($input, $type);
        $overrides = $this->overrides($input);
        $issuedAt = $certificate->date_of_issue ?? now();

        $certificate->loadMissing(['soloParent', 'soloParentChildren']);

        return DB::transaction(function () use ($certificate, $type, $resident, $details, $issuedAt, $overrides, $signature, $editor): array {
            $this->auditFieldChanges($certificate, $details, $editor);

            $certificate = $this->certificateRepository->update($certificate, $this->storedAttributes($details));

            $document = $this->buildDocument(
                $type,
                $resident,
                $details['purpose_of_request'],
                $issuedAt,
                $overrides,
                $signature['embed'],
                $editor,
                $details,
                (int) $certificate->certificate_id,
            );

            $this->logPrint($editor, (int) $certificate->certificate_id, $signature['audit']);
            $pdf = $this->renderPdf($document);

            return [
                'pdf' => $pdf,
                'certificate_id' => (int) $certificate->certificate_id,
                'filename' => (Str::slug((string) $type->type_of_certificate) ?: 'certificate').'.pdf',
                'warnings' => $signature['warnings'],
            ];
        });
    }

    public function signatureResponse(): StreamedResponse
    {
        $path = $this->availableSignaturePath();

        if ($path === null) {
            abort(404, 'No Punong Barangay signature uploaded.');
        }

        return Storage::disk((string) config('certificates.signature_disk'))->response(
            $path,
            'punong_barangay.png',
            ['Content-Type' => 'image/png'],
        );
    }

    public function signatureIsAvailable(): bool
    {
        return $this->availableSignaturePath() !== null;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    public function renderPdf(array $document): string
    {
        return Pdf::loadView('certificates.show', ['document' => $document])
            ->setPaper(
                (string) config('certificates.paper', 'a4'),
                (string) config('certificates.orientation', 'portrait'),
            )
            ->output();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function typeConfig(int $typeId): ?array
    {
        $config = config('certificate_types.'.$typeId);

        return is_array($config) ? $config : null;
    }

    private function requireType(int $typeId): TypeOfCertificate
    {
        $type = $this->typeOfCertificateRepository->findById($typeId);

        if (! $type instanceof TypeOfCertificate || $this->typeConfig($typeId) === null) {
            throw ValidationException::withMessages([
                'type_of_certificate_id' => 'Select a certificate type.',
            ]);
        }

        return $type;
    }

    private function requireResident(int $residentId): Resident
    {
        $resident = $this->residentRepository->findForCertificate($residentId);

        if (! $resident instanceof Resident) {
            throw ValidationException::withMessages([
                'resident_id' => 'Select a resident.',
            ]);
        }

        return $resident;
    }

    private function isTransient(Migration $migration): bool
    {
        $typeName = mb_strtolower(trim((string) ($migration->residentType?->resident_type ?? '')));

        return $typeName === 'transient';
    }

    private function kind(?TypeOfCertificate $type): ?string
    {
        if (! $type instanceof TypeOfCertificate) {
            return null;
        }

        $kind = $this->typeConfig((int) $type->type_of_certificate_id)['kind'] ?? null;

        return is_string($kind) && $kind !== '' ? $kind : null;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{resident_name: ?string, address: ?string, requested_by: ?string}
     */
    private function overrides(array $input): array
    {
        $overrides = is_array($input['overrides'] ?? null) ? $input['overrides'] : [];

        return [
            'resident_name' => $this->blankToNull($overrides['resident_name'] ?? null),
            'address' => $this->blankToNull($overrides['address'] ?? null),
            'requested_by' => $this->blankToNull($overrides['requested_by'] ?? null),
        ];
    }

    private function blankToNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function titleCase(string $value): string
    {
        return Str::of($value)->squish()->title()->toString();
    }

    private function relationshipCase(string $value): string
    {
        return Str::ucfirst(Str::lower(Str::squish($value)));
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     purpose_of_request: string,
     *     representative_name: ?string,
     *     representative_relationship: ?string,
     *     date_of_death: ?string,
     *     include_income_amount: int,
     *     stated_income_amount: ?string,
     *     place_of_death: ?string,
     *     cause_of_death: ?string,
     *     ethnic_group: ?string,
     *     resident_since_year: ?int,
     *     stated_civil_status: ?string,
     *     solo_parent: ?array<string, mixed>
     * }
     */
    private function certificateDetails(array $input, TypeOfCertificate $type): array
    {
        $kind = $this->kind($type);
        $requested = (bool) ($input['representative_requested'] ?? false);
        $representativeName = null;
        $relationship = null;

        if ($requested) {
            $representativeName = $this->titleCase((string) ($input['representative_name'] ?? ''));
            $relationship = $this->relationshipCase((string) ($input['representative_relationship'] ?? ''));
            $representativeName = $representativeName !== '' ? $representativeName : null;
            $relationship = $relationship !== '' ? $relationship : null;
        }

        $dateOfDeath = null;
        $placeOfDeath = null;
        $causeOfDeath = null;
        $ethnicGroup = null;

        if ($kind === 'death') {
            if (! empty($input['date_of_death'])) {
                $dateOfDeath = \Illuminate\Support\Carbon::parse((string) $input['date_of_death'])->toDateString();
            }

            $placeOfDeath = $this->lowerText($input['place_of_death'] ?? null);
            $causeOfDeath = $this->lowerText($input['cause_of_death'] ?? null);
            $ethnicGroup = $this->blankToNull($input['ethnic_group'] ?? null);
            $ethnicGroup = $ethnicGroup === null ? null : $this->titleCase($ethnicGroup);
        }

        $includeIncome = $kind === 'low_income' && (bool) ($input['include_income_amount'] ?? false);
        $amount = null;

        if ($includeIncome && isset($input['stated_income_amount']) && $input['stated_income_amount'] !== null && $input['stated_income_amount'] !== '') {
            $amount = number_format((float) $input['stated_income_amount'], 2, '.', '');
        }

        $needsSince = $kind === 'low_income' && ! $includeIncome;
        $needsCivil = $kind === 'good_moral' || $needsSince;
        $sinceYear = null;

        if ($needsSince && isset($input['resident_since_year']) && $input['resident_since_year'] !== null && $input['resident_since_year'] !== '') {
            $sinceYear = (int) $input['resident_since_year'];
        }

        $civilStatus = null;

        if ($needsCivil) {
            $civilStatus = $this->blankToNull($input['stated_civil_status'] ?? null);
            $civilStatus = $civilStatus === null ? null : Str::ucfirst(Str::lower(Str::squish($civilStatus)));
        }

        return [
            'purpose_of_request' => $this->titleCase((string) $input['purpose_of_request']),
            'representative_name' => $representativeName,
            'representative_relationship' => $relationship,
            'date_of_death' => $dateOfDeath,
            'include_income_amount' => $includeIncome ? 1 : 0,
            'stated_income_amount' => $includeIncome ? $amount : null,
            'place_of_death' => $placeOfDeath,
            'cause_of_death' => $causeOfDeath,
            'ethnic_group' => $ethnicGroup,
            'resident_since_year' => $sinceYear,
            'stated_civil_status' => $civilStatus,
            'solo_parent' => $kind === 'solo_parent' ? $this->soloParentDetails($input) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function storedAttributes(array $details): array
    {
        return [
            'purpose_of_request' => $details['purpose_of_request'],
            'representative_name' => $details['representative_name'],
            'representative_relationship' => $details['representative_relationship'],
            'date_of_death' => $details['date_of_death'],
            'include_income_amount' => $details['include_income_amount'],
            'stated_income_amount' => $details['stated_income_amount'],
            'place_of_death' => $details['place_of_death'],
            'cause_of_death' => $details['cause_of_death'],
            'ethnic_group' => $details['ethnic_group'],
            'resident_since_year' => $details['resident_since_year'],
            'stated_civil_status' => $details['stated_civil_status'],
            'solo_parent' => $details['solo_parent'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function soloParentDetails(array $input): array
    {
        $children = [];

        foreach (is_array($input['children'] ?? null) ? $input['children'] : [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            $name = $this->blankToNull($child['child_name'] ?? null);
            $born = $this->blankToNull(isset($child['date_of_birth']) ? (string) $child['date_of_birth'] : null);

            if ($name === null || $born === null) {
                continue;
            }

            $children[] = [
                'child_name' => $this->titleCase($name),
                'date_of_birth' => \Illuminate\Support\Carbon::parse($born)->toDateString(),
            ];
        }

        return [
            'circumstance' => $this->lowerText($input['solo_parent_circumstance'] ?? null) ?? '',
            'president_name' => $this->titleCase((string) ($input['solo_parent_president_name'] ?? '')),
            'president_address' => Str::squish((string) ($input['solo_parent_president_address'] ?? '')),
            'neighbor_name' => $this->titleCase((string) ($input['solo_parent_neighbor_name'] ?? '')),
            'neighbor_address' => Str::squish((string) ($input['solo_parent_neighbor_address'] ?? '')),
            'kagawad_name' => $this->titleCase((string) ($input['solo_parent_kagawad_name'] ?? '')),
            'children' => $children,
        ];
    }

    private function lowerText(mixed $value): ?string
    {
        $text = $this->blankToNull(is_string($value) ? $value : null);

        if ($text === null) {
            return null;
        }

        $lower = Str::of($text)->squish()->lower()->toString();

        return $lower === '' ? null : $lower;
    }

    private function deathDateText(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        return \Illuminate\Support\Carbon::parse($value)
            ->timezone(config('app.timezone'))
            ->format('F j, Y');
    }

    private function logPrint(User $user, int $certificateId, string $signatureAudit): void
    {
        $this->auditLogRepository->log(
            performedByUserId: $user->user_id,
            actionId: Action::PRINT,
            recordId: $certificateId,
            description: 'Print certificate',
            oldValue: null,
            newValue: $this->clipAudit($signatureAudit, false),
            target: 'signature',
            entity: 'certificate',
        );
    }

    /**
     * @param  array{
     *     purpose_of_request: string,
     *     representative_name: ?string,
     *     representative_relationship: ?string,
     *     date_of_death: ?string,
     *     include_income_amount: int,
     *     stated_income_amount: ?string
     * }  $details
     */
    private function auditFieldChanges(Certificate $certificate, array $details, User $editor): void
    {
        foreach ($details as $field => $newValue) {
            if ($field === 'solo_parent') {
                continue;
            }

            $old = $this->storedField($certificate, $field);
            $new = $this->normalizeAudit($field, $newValue);

            if ($old === $new) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $editor->user_id,
                actionId: Action::UPDATE,
                recordId: (int) $certificate->certificate_id,
                description: 'Update certificate',
                oldValue: $this->clipAudit($old, true),
                newValue: $this->clipAudit($new, false) ?? '(empty)',
                target: $field,
                entity: 'certificate',
            );
        }

        $this->auditSoloParent($certificate, is_array($details['solo_parent'] ?? null) ? $details['solo_parent'] : null, $editor);
    }

    /**
     * @param  array<string, mixed>|null  $soloParent
     */
    private function auditSoloParent(Certificate $certificate, ?array $soloParent, User $editor): void
    {
        $current = $certificate->soloParent;
        $pairs = [
            'circumstance' => [$current?->circumstance, $soloParent['circumstance'] ?? null],
            'president_name' => [$current?->president_name, $soloParent['president_name'] ?? null],
            'president_address' => [$current?->president_address, $soloParent['president_address'] ?? null],
            'neighbor_name' => [$current?->neighbor_name, $soloParent['neighbor_name'] ?? null],
            'neighbor_address' => [$current?->neighbor_address, $soloParent['neighbor_address'] ?? null],
            'kagawad_name' => [$current?->kagawad_name, $soloParent['kagawad_name'] ?? null],
        ];

        foreach ($pairs as $field => [$old, $new]) {
            $oldText = $this->blankToNull(is_string($old) ? $old : null);
            $newText = $this->blankToNull(is_string($new) ? $new : null);

            if ($oldText === $newText) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $editor->user_id,
                actionId: Action::UPDATE,
                recordId: (int) $certificate->certificate_id,
                description: 'Update certificate',
                oldValue: $this->clipAudit($oldText, true),
                newValue: $this->clipAudit($newText, false) ?? '(empty)',
                target: $field,
                entity: 'certificate',
            );
        }

        $oldChildren = $certificate->soloParentChildren
            ->map(fn ($child): string => trim($child->child_name.' '.($child->date_of_birth?->toDateString() ?? '')))
            ->implode('; ');
        $newChildren = collect($soloParent['children'] ?? [])
            ->map(fn (array $child): string => trim(($child['child_name'] ?? '').' '.($child['date_of_birth'] ?? '')))
            ->implode('; ');
        $oldChildren = $oldChildren === '' ? null : $oldChildren;
        $newChildren = $newChildren === '' ? null : $newChildren;

        if ($oldChildren === $newChildren) {
            return;
        }

        $this->auditLogRepository->log(
            performedByUserId: $editor->user_id,
            actionId: Action::UPDATE,
            recordId: (int) $certificate->certificate_id,
            description: 'Update certificate',
            oldValue: $this->clipAudit($oldChildren, true),
            newValue: $this->clipAudit($newChildren, false) ?? '(empty)',
            target: 'children',
            entity: 'certificate',
        );
    }

    private function storedField(Certificate $certificate, string $field): ?string
    {
        $value = $certificate->getAttribute($field);

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        return $this->normalizeAudit($field, $value);
    }

    private function normalizeAudit(string $field, mixed $value): ?string
    {
        if ($value === null) {
            return $field === 'include_income_amount' ? '0' : null;
        }

        if ($field === 'include_income_amount') {
            return ((int) $value) === 1 ? '1' : '0';
        }

        if ($field === 'stated_income_amount') {
            return number_format((float) $value, 2, '.', '');
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function clipAudit(?string $value, bool $nullable): ?string
    {
        if ($value === null || $value === '') {
            return $nullable ? null : '(empty)';
        }

        $clipped = mb_strimwidth($value, 0, 45, '...');

        if ($clipped === '') {
            return $nullable ? null : '(empty)';
        }

        return $clipped;
    }

    private function assertSignatureAllowed(TypeOfCertificate $type, bool $attachSignature): void
    {
        if (! $attachSignature) {
            return;
        }

        $config = $this->typeConfig((int) $type->type_of_certificate_id);

        if (! (bool) ($config['allows_signature'] ?? false)) {
            throw ValidationException::withMessages([
                'attach_pb_signature' => 'Not available for this certificate type.',
            ]);
        }
    }

    /**
     * @return array{embed: bool, warnings: list<string>, audit: string}
     */
    private function signatureChoice(bool $attachSignature): array
    {
        if (! $attachSignature) {
            return [
                'embed' => false,
                'warnings' => [],
                'audit' => 'Without signature',
            ];
        }

        if (! $this->signatureIsAvailable()) {
            return [
                'embed' => false,
                'warnings' => [self::SIGNATURE_MISSING_WARNING],
                'audit' => 'Signature file missing',
            ];
        }

        return [
            'embed' => true,
            'warnings' => [],
            'audit' => 'With signature',
        ];
    }

    /**
     * @param  array{resident_name: ?string, address: ?string, requested_by: ?string}  $overrides
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private function buildDocument(
        TypeOfCertificate $type,
        Resident $resident,
        string $purpose,
        CarbonInterface $issuedAt,
        array $overrides,
        bool $attachSignature,
        ?User $issuer,
        array $details = [],
        ?int $certificateId = null,
    ): array {
        $resident->loadMissing(['ethnicity', 'maritalStatus', 'nationality', 'sociocivic.soloParentStatus']);
        $config = $this->typeConfig((int) $type->type_of_certificate_id) ?? [];
        $barangayName = $this->settingValue('barangay_name');
        $cityName = $this->settingValue('city_name');
        $residentName = $overrides['resident_name'] ?? $this->personName($resident);
        $address = $overrides['address'] ?? $this->address($resident, $barangayName, $cityName);
        $requester = $overrides['requested_by'] ?? $residentName;
        $logos = $this->logos();
        $issuerUser = $issuer?->loadMissing('personnel.position');
        $localIssued = $issuedAt->timezone(config('app.timezone'));

        $ownerName = $this->personName($resident);
        $representativeName = $this->blankToNull($details['representative_name'] ?? null);
        $relationship = $this->blankToNull($details['representative_relationship'] ?? null);
        $representativeSentence = null;

        if ($representativeName !== null && $relationship !== null) {
            $representativeSentence = 'This Certification was issued upon the request of {representative_name} to process the {certificate_type} of {possessive} {relationship}, {owner_name}.';
        }

        $incomeAmount = $this->blankToNull(isset($details['stated_income_amount']) ? (string) $details['stated_income_amount'] : null);
        $includeIncome = (bool) ($details['include_income_amount'] ?? false);
        $soloParent = is_array($details['solo_parent'] ?? null) ? $details['solo_parent'] : null;
        $courtesy = $this->courtesy($resident, (string) ($details['stated_civil_status'] ?? ''));
        $civilStatus = (string) ($details['stated_civil_status'] ?? '');
        $yearsOld = $this->yearsOldPhrase($resident);

        $tokens = [
            'name' => mb_strtoupper($residentName, 'UTF-8'),
            'owner_name' => mb_strtoupper($ownerName, 'UTF-8'),
            'age_phrase' => $this->agePhrase($resident),
            'years_old' => $yearsOld,
            'years_of_age' => str_replace(['years old', 'year old'], ['years of age', 'year of age'], $yearsOld),
            'address' => $address,
            'pronoun' => $this->pronoun($resident),
            'subject_pronoun' => $this->subjectPronoun($resident),
            'possessive' => $this->possessivePronoun($resident),
            'requester' => mb_strtoupper($requester, 'UTF-8'),
            'purpose' => $purpose,
            'purpose_clause' => ($config['print_purpose'] ?? true) ? ' for '.$purpose : '',
            'issued_date' => $this->issuedDateText($issuedAt),
            'issued_on' => $localIssued->format('jS').' day of '.$localIssued->format('F').' '.$localIssued->format('Y'),
            'issued_on_comma' => $localIssued->format('jS').' day of '.$localIssued->format('F').', '.$localIssued->format('Y'),
            'issued_ordinal' => mb_strtoupper($localIssued->format('jS'), 'UTF-8'),
            'issued_month' => $localIssued->format('F'),
            'issued_year' => $localIssued->format('Y'),
            'barangay' => $barangayName,
            'barangay_short' => $this->barangayShort($barangayName),
            'city' => $cityName,
            'city_name' => $this->cityNameOnly($cityName),
            'city_of' => $this->cityOf($cityName),
            'representative_name' => mb_strtoupper($representativeName ?? '', 'UTF-8'),
            'certificate_type' => (string) $type->type_of_certificate,
            'relationship' => $relationship ?? '',
            'courtesy' => $courtesy,
            'nationality' => trim((string) ($resident->nationality?->nationality ?? 'Filipino')),
            'civil_status' => $civilStatus,
            'civil_status_lower' => $civilStatus === '' ? '' : mb_strtolower($civilStatus, 'UTF-8'),
            'resident_since' => isset($details['resident_since_year']) && $details['resident_since_year'] !== null
                ? (string) $details['resident_since_year']
                : '',
            'place_of_death' => (string) ($details['place_of_death'] ?? ''),
            'cause_of_death' => (string) ($details['cause_of_death'] ?? ''),
            'ethnic_group' => (string) ($details['ethnic_group'] ?? ''),
            'circumstance' => (string) ($soloParent['circumstance'] ?? ''),
            'income_words' => $incomeAmount !== null ? $this->amountInWords($incomeAmount) : '',
            'income_figure' => $incomeAmount !== null ? $this->amountFigure($incomeAmount) : '',
        ];

        $shared = [
            'header' => [
                'city_name' => $cityName,
                'barangay_name' => mb_strtoupper($barangayName, 'UTF-8'),
                'barangay_email' => $this->settingValue('barangay_email'),
                'barangay_contact_no' => $this->settingValue('barangay_contact_no'),
            ],
            'logos' => $logos['images'],
            'missing_logos' => $logos['missing'],
            'suggestions' => [
                'ethnic_group' => trim((string) ($resident->ethnicity?->ethnicity ?? '')),
                'stated_civil_status' => trim((string) ($resident->maritalStatus?->marital_status ?? '')),
                'solo_parent_circumstance' => $this->soloCircumstanceSuggestion($resident),
            ],
            'fields' => [
                'resident_name' => $residentName,
                'address' => $address,
                'requested_by' => $requester,
                'purpose' => $purpose,
                'age_phrase' => $tokens['age_phrase'],
                'pronoun' => $tokens['pronoun'],
                'issued_date' => $tokens['issued_date'],
                'witness_date' => $localIssued->format('F j, Y'),
                'date_of_issue' => $localIssued->format('Y-m-d H:i:s'),
                'issuer_name' => $issuerUser instanceof User ? $this->issuerPrintName($issuerUser) : '',
                'issuer_position' => trim((string) ($issuerUser?->personnel?->position?->position_name ?? '')),
                'punong_barangay_name' => mb_strtoupper($this->settingValue('punong_barangay_name'), 'UTF-8'),
                'barangay_short' => $tokens['barangay_short'],
                'city' => $cityName,
            ],
            'allows_signature' => (bool) ($config['allows_signature'] ?? false),
            'signature_available' => $this->signatureIsAvailable(),
            'attach_pb_signature' => $attachSignature,
            'signature_data_uri' => $attachSignature ? $this->signatureDataUri() : null,
            'certificate_number' => $certificateId,
        ];

        $sheetConfigs = ! empty($config['packet'])
            ? [$this->typeConfig(3) ?? [], $this->typeConfig(4) ?? []]
            : [$config];
        $sheets = [];

        foreach ($sheetConfigs as $index => $sheetConfig) {
            $sheets[] = array_merge($shared, $this->sheetContent(
                $sheetConfig,
                $tokens,
                $index === 0 ? $representativeSentence : null,
                $includeIncome && $index === 0,
                is_array($soloParent) ? $soloParent : [],
                $issuedAt,
                $relationship,
            ));
        }

        $document = $sheets[0];

        if (count($sheets) > 1) {
            $document['pages'] = $sheets;
        }

        return $document;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, string>  $tokens
     * @param  array<string, mixed>  $soloParent
     * @return array<string, mixed>
     */
    private function sheetContent(
        array $config,
        array $tokens,
        ?string $representativeSentence,
        bool $includeIncome,
        array $soloParent,
        CarbonInterface $issuedAt,
        ?string $relationship,
    ): array {
        $templates = $includeIncome && isset($config['paragraphs_with_amount'])
            ? $config['paragraphs_with_amount']
            : ($config['paragraphs'] ?? []);
        $paragraphs = [];
        $childrenAfter = null;
        $replacedRequest = false;

        foreach ($templates as $template) {
            $template = (string) $template;

            if (trim($template) === '{children_table}') {
                $childrenAfter = count($paragraphs);

                continue;
            }

            $isDeathRequest = trim($template) === '{death_request}';

            if ($isDeathRequest) {
                $asker = ($tokens['representative_name'] ?? '') !== ''
                    ? '**{representative_name}**'
                    : '**{requester}**';
                $relation = mb_strtolower($relationship ?? '');
                $template = $relation !== ''
                    ? "This Certification was issued upon the request of {$asker} to process the **DEATH** Certificate of {possessive} {$relation} {owner_name}."
                    : "This Certification was issued upon the request of {$asker} to process the **DEATH** Certificate of {owner_name}.";
                $replacedRequest = true;
            }

            $flush = str_starts_with($template, '~');

            if ($flush) {
                $template = substr($template, 1);
            }

            if (! $isDeathRequest && $representativeSentence !== null && str_contains(mb_strtolower($template), 'issued upon the request')) {
                $replaced = preg_replace(
                    '/[^.]*issued upon the request[^.]*\./iu',
                    $representativeSentence,
                    $template,
                    1,
                );
                $template = is_string($replaced) ? $replaced : $template;
                $replacedRequest = true;
            } elseif ($representativeSentence !== null && ! $replacedRequest && preg_match('/^(Issued|Signed)\b/', $template) === 1) {
                $paragraphs[] = [
                    'flush' => false,
                    'parts' => $this->paragraphParts($representativeSentence, $tokens),
                ];
                $replacedRequest = true;
            }

            $paragraphs[] = [
                'flush' => $flush,
                'parts' => $this->paragraphParts($template, $tokens),
            ];
        }

        if ($representativeSentence !== null && ! $replacedRequest) {
            $paragraphs[] = [
                'flush' => false,
                'parts' => $this->paragraphParts($representativeSentence, $tokens),
            ];
        }

        $title = $includeIncome && isset($config['title_with_amount'])
            ? (string) $config['title_with_amount']
            : (string) ($config['title'] ?? '');
        $salutation = array_key_exists('salutation', $config)
            ? $config['salutation']
            : 'To Whom It May Concern:';

        if ($includeIncome && array_key_exists('salutation_with_amount', $config)) {
            $salutation = $config['salutation_with_amount'];
        }

        $signatureStyle = (string) ($config['signature'] ?? 'authority');

        return [
            'title' => mb_strtoupper($title, 'UTF-8'),
            'subtitle' => isset($config['subtitle']) ? (string) $config['subtitle'] : null,
            'office' => $config['office'] ?? null,
            'salutation' => $salutation,
            'revised_note' => $config['revised_note'] ?? null,
            'meta_right' => (bool) ($config['meta_right'] ?? false),
            'show_certificate_number' => (bool) ($config['show_certificate_number'] ?? false),
            'signature_style' => $signatureStyle,
            'show_stamp' => (bool) ($config['stamp'] ?? ($signatureStyle === 'authority')),
            'bold_issuer' => (bool) ($config['bold_issuer'] ?? false),
            'title_bold' => (bool) ($config['title_bold'] ?? true),
            'officer_line' => (bool) ($config['officer_line'] ?? true),
            'authority_line' => (string) ($config['authority_line'] ?? 'By The Authority of the Punong Barangay;'),
            'footer_split' => (bool) ($config['footer_split'] ?? false),
            'dense' => (bool) ($config['dense'] ?? false),
            'footer_notes' => array_values($config['footer_notes'] ?? []),
            'children_after' => $childrenAfter,
            'children' => $this->childRows($soloParent['children'] ?? [], $issuedAt),
            'affidavit' => $signatureStyle === 'affidavit' ? [
                'president_name' => (string) ($soloParent['president_name'] ?? ''),
                'president_address' => (string) ($soloParent['president_address'] ?? ''),
                'neighbor_name' => (string) ($soloParent['neighbor_name'] ?? ''),
                'neighbor_address' => (string) ($soloParent['neighbor_address'] ?? ''),
                'kagawad_name' => (string) ($soloParent['kagawad_name'] ?? ''),
            ] : null,
            'paragraphs' => $paragraphs,
        ];
    }

    /**
     * @param  array<string, string>  $tokens
     * @return list<array{text: string, bold: bool}>
     */
    private function paragraphParts(string $template, array $tokens): array
    {
        $pieces = preg_split('/(\*\*.*?\*\*|__.*?__|~~.*?~~)/us', $template, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$template];
        $parts = [];

        foreach ($pieces as $piece) {
            if ($piece === '') {
                continue;
            }

            $bold = str_starts_with($piece, '**') && str_ends_with($piece, '**');
            $underline = str_starts_with($piece, '__') && str_ends_with($piece, '__');
            $italic = str_starts_with($piece, '~~') && str_ends_with($piece, '~~');

            if ($bold || $underline || $italic) {
                $piece = substr($piece, 2, -2);
            }

            foreach ($this->fillTokens($piece, $tokens, $bold) as $part) {
                $part['bold'] = $part['bold'] || $bold;
                $part['italic'] = $italic;
                $part['underline'] = $underline;
                $parts[] = $part;
            }
        }

        return $this->emphasizeCertification($parts);
    }

    /**
     * @param  array<string, string>  $tokens
     * @return list<array{text: string, bold: bool, italic: bool, underline: bool}>
     */
    private function fillTokens(string $text, array $tokens, bool $alreadyBold): array
    {
        $boldKeys = $alreadyBold ? [] : ['name', 'owner_name', 'representative_name'];

        foreach ($tokens as $key => $value) {
            if (in_array($key, $boldKeys, true)) {
                continue;
            }

            $text = str_replace('{'.$key.'}', (string) $value, $text);
        }

        $parts = [['text' => $text, 'bold' => false, 'italic' => false, 'underline' => false]];

        foreach ($boldKeys as $key) {
            $next = [];

            foreach ($parts as $part) {
                if ($part['bold'] || ! str_contains($part['text'], '{'.$key.'}')) {
                    $next[] = $part;

                    continue;
                }

                $chunks = explode('{'.$key.'}', $part['text']);

                foreach ($chunks as $index => $chunk) {
                    if ($chunk !== '') {
                        $next[] = ['text' => $chunk, 'bold' => false, 'italic' => false, 'underline' => false];
                    }

                    if ($index < count($chunks) - 1) {
                        $next[] = ['text' => (string) ($tokens[$key] ?? ''), 'bold' => true, 'italic' => false, 'underline' => false];
                    }
                }
            }

            $parts = $next;
        }

        return $parts;
    }

    /**
     * @param  list<array{text: string, bold: bool}>  $parts
     * @return list<array{text: string, bold: bool}>
     */
    private function emphasizeCertification(array $parts): array
    {
        $emphasized = [];

        foreach ($parts as $part) {
            if ($part['bold'] || ! str_contains($part['text'], 'CERTIFICATION')) {
                $emphasized[] = $part;

                continue;
            }

            $pieces = preg_split('/(CERTIFICATION)/', $part['text'], -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

            foreach ($pieces as $piece) {
                if ($piece === '') {
                    continue;
                }

                $emphasized[] = [
                    'text' => $piece,
                    'bold' => $piece === 'CERTIFICATION',
                    'italic' => (bool) ($part['italic'] ?? false),
                    'underline' => (bool) ($part['underline'] ?? false),
                ];
            }
        }

        return $emphasized;
    }

    private function personName(Resident $resident): string
    {
        return collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->last_name,
            $resident->suffix,
        ])->map(fn (mixed $part) => trim((string) $part))->filter()->implode(' ');
    }

    private function residentListName(Resident $resident): string
    {
        $last = trim((string) $resident->last_name);
        $rest = collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->suffix,
        ])->map(fn (mixed $part) => trim((string) $part))->filter()->implode(' ');

        if ($last !== '' && $rest !== '') {
            return $last.', '.$rest;
        }

        return $last !== '' ? $last : $rest;
    }

    private function address(Resident $resident, string $barangayName, string $cityName): string
    {
        $houseLot = trim((string) ($resident->household?->house_number ?? ''));
        $street = trim((string) ($resident->household?->street?->street_name ?? ''));

        return collect([
            $houseLot !== '' ? $houseLot : null,
            $street !== '' ? $street : null,
            $barangayName !== '' ? $barangayName : null,
            $cityName !== '' ? $cityName : null,
        ])->filter()->implode(', ');
    }

    private function agePhrase(Resident $resident): string
    {
        $age = $resident->age();

        if ($age === null) {
            return 'of unknown age';
        }

        if ($age >= 18) {
            return 'of legal age';
        }

        return max(0, $age).' years old';
    }

    private function yearsOldPhrase(Resident $resident): string
    {
        $age = $resident->age();

        if ($age === null) {
            return 'of unknown age';
        }

        if ($age < 1) {
            $months = max(0, (int) ($resident->ageInMonths() ?? 0));

            return $months.'-month old';
        }

        return $age.' '.($age === 1 ? 'year old' : 'years old');
    }

    private function subjectPronoun(Resident $resident): string
    {
        $sex = mb_strtolower(trim((string) ($resident->sex?->sex ?? '')));

        return match ($sex) {
            'female' => 'she',
            'male' => 'he',
            default => 'they',
        };
    }

    private function courtesy(Resident $resident, string $civilStatus): string
    {
        $sex = mb_strtolower(trim((string) ($resident->sex?->sex ?? '')));
        $status = $civilStatus !== '' ? $civilStatus : (string) ($resident->maritalStatus?->marital_status ?? '');
        $married = str_contains(mb_strtolower($status), 'married');

        $title = match ($sex) {
            'male' => 'Mr.',
            'female' => $married ? 'Mrs.' : 'Ms.',
            default => '',
        };

        return $title === '' ? '' : $title.' ';
    }

    private function cityNameOnly(string $cityName): string
    {
        $city = trim($cityName);
        $city = preg_replace('/^city of\s+/iu', '', $city) ?? $city;
        $city = preg_replace('/\s+city$/iu', '', $city) ?? $city;

        return trim($city);
    }

    private function cityOf(string $cityName): string
    {
        $city = trim($cityName);

        if (preg_match('/^city of\s+/iu', $city) === 1) {
            return $city;
        }

        return 'City of '.$this->cityNameOnly($city);
    }

    private function soloCircumstanceSuggestion(Resident $resident): string
    {
        $status = trim((string) ($resident->sociocivic?->soloParentStatus?->solo_parent_status ?? ''));

        if ($status === '' || mb_strtolower($status) === 'non-solo parent') {
            return '';
        }

        return $status;
    }

    /**
     * @param  list<array{child_name?: string, date_of_birth?: string}>  $children
     * @return list<array{name: string, date_of_birth: string, age: string}>
     */
    private function childRows(array $children, CarbonInterface $issuedAt): array
    {
        $rows = [];

        foreach ($children as $child) {
            $born = $this->blankToNull($child['date_of_birth'] ?? null);

            if ($born === null) {
                continue;
            }

            $date = \Illuminate\Support\Carbon::parse($born);
            $years = $date->diff($issuedAt)->y;
            $age = $years >= 1
                ? $years.' '.($years === 1 ? 'Year Old' : 'Years Old')
                : max(0, $date->diff($issuedAt)->m).' Months Old';

            $rows[] = [
                'name' => mb_strtoupper((string) ($child['child_name'] ?? ''), 'UTF-8'),
                'date_of_birth' => $date->format('F j, Y'),
                'age' => $age,
            ];
        }

        return $rows;
    }

    private function amountFigure(string $amount): string
    {
        return str_replace(',', ', ', number_format((float) $amount, 2, '.', ','));
    }

    private function amountInWords(string $amount): string
    {
        $normalized = number_format((float) $amount, 2, '.', '');
        [$whole, $fraction] = explode('.', $normalized);
        $words = $this->wholeInWords((int) $whole);

        if ((int) $fraction === 0) {
            return $words;
        }

        return $words.' point '.$this->decimalWords((int) $fraction);
    }

    private function wholeInWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $scales = ['', 'thousand', 'million', 'billion'];
        $groups = [];
        $remaining = $number;

        while ($remaining > 0) {
            $groups[] = $remaining % 1000;
            $remaining = intdiv($remaining, 1000);
        }

        $parts = [];

        for ($index = count($groups) - 1; $index >= 0; $index--) {
            if ($groups[$index] === 0) {
                continue;
            }

            $chunk = $this->underThousand($groups[$index]);
            $scale = $scales[$index] ?? '';
            $parts[] = trim($chunk.($scale !== '' ? ' '.$scale : ''));
        }

        if (count($parts) > 1 && ($groups[0] ?? 0) !== 0) {
            $last = array_pop($parts);

            return implode(' ', $parts).' and '.$last;
        }

        return implode(' ', $parts);
    }

    private function decimalWords(int $fraction): string
    {
        if (intdiv($fraction, 10) === 0) {
            return 'zero '.$this->underHundred($fraction);
        }

        return $this->underHundred($fraction);
    }

    private function underThousand(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $words[] = $this->underHundred(intdiv($number, 100)).' hundred';
            $number %= 100;
        }

        if ($number > 0) {
            $words[] = $this->underHundred($number);
        }

        return implode(' ', $words);
    }

    private function underHundred(int $number): string
    {
        $ones = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven',
            12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen',
            17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        ];
        $tens = [2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty', 6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'];

        if ($number < 20) {
            return $ones[$number];
        }

        $ten = intdiv($number, 10);
        $one = $number % 10;

        return $tens[$ten].($one > 0 ? ' '.$ones[$one] : '');
    }

    private function pronoun(Resident $resident): string
    {
        $sex = mb_strtolower(trim((string) ($resident->sex?->sex ?? '')));

        return match ($sex) {
            'male' => 'him',
            'female' => 'her',
            default => 'them',
        };
    }

    private function possessivePronoun(Resident $resident): string
    {
        $sex = mb_strtolower(trim((string) ($resident->sex?->sex ?? '')));

        return match ($sex) {
            'female' => 'her',
            'male' => 'his',
            default => 'their',
        };
    }

    private function issuedDateText(CarbonInterface $date): string
    {
        $local = $date->timezone(config('app.timezone'));

        return $local->format('jS').' day of '.$local->format('F').', '.$local->format('Y');
    }

    private function barangayShort(string $barangayName): string
    {
        $short = preg_replace('/^barangay\s+/iu', '', trim($barangayName));

        return trim(is_string($short) ? $short : $barangayName);
    }

    private function settingValue(string $key): string
    {
        return trim((string) ($this->settingRepository->findByKey($key)?->setting_value ?? ''));
    }

    /**
     * @return array{images: array<string, ?string>, missing: list<string>}
     */
    private function logos(): array
    {
        $images = [];
        $missing = [];

        foreach (config('certificates.logos', []) as $key => $filename) {
            $path = resource_path('certificates/logos/'.$filename);

            if (! is_file($path)) {
                $images[$key] = null;
                $missing[] = (string) $filename;

                continue;
            }

            $image = $this->dataUri($path);
            $images[$key] = $image;

            if ($image === null) {
                $missing[] = (string) $filename;
            }
        }

        return [
            'images' => $images,
            'missing' => $missing,
        ];
    }

    private function signatureDataUri(): ?string
    {
        $path = $this->availableSignaturePath();

        if ($path === null) {
            return null;
        }

        return $this->dataUri(Storage::disk((string) config('certificates.signature_disk'))->path($path));
    }

    private function availableSignaturePath(): ?string
    {
        $relative = (string) config('certificates.signature_path');
        $setting = trim($this->settingValue('punong_barangay_signature'));
        $disk = (string) config('certificates.signature_disk');

        if ($setting === '' || $setting !== $relative || ! Storage::disk($disk)->exists($relative)) {
            return null;
        }

        return $relative;
    }

    private function dataUri(string $absolutePath): ?string
    {
        if (! is_file($absolutePath) || filesize($absolutePath) > 1_500_000) {
            return null;
        }

        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($absolutePath) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function issuerHistoryName(?User $user): string
    {
        if (! $user instanceof User) {
            return '';
        }

        $personnel = $user->personnel;

        if ($personnel !== null) {
            $last = trim((string) $personnel->personnel_last_name);
            $first = trim((string) $personnel->personnel_first_name);

            if ($last !== '' && $first !== '') {
                return $last.', '.$first;
            }

            if ($last !== '' || $first !== '') {
                return $last !== '' ? $last : $first;
            }
        }

        return User::formatForDisplay((string) $user->username);
    }

    private function issuerBlock(?User $issuer): ?string
    {
        if ($issuer instanceof User && $issuer->personnel_id !== null && $issuer->personnel !== null) {
            return null;
        }

        return 'Your account has no linked personnel record, so it cannot issue a certificate.';
    }

    private function issuerPrintName(User $user): string
    {
        $personnel = $user->personnel;

        if ($personnel === null) {
            return '';
        }

        $name = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_last_name,
            $personnel->personnel_suffix,
        ])->map(fn (mixed $part) => trim((string) $part))->filter()->implode(' ');

        return $name !== '' ? $name : '';
    }
}
