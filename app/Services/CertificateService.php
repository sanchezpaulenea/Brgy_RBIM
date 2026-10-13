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
                    'allows_signature' => (bool) ($config['allows_signature'] ?? false),
                    'print_purpose' => (bool) ($config['print_purpose'] ?? true),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchResidents(string $term): array
    {
        return $this->residentRepository
            ->searchByName($term, 20)
            ->map(function (Resident $resident): array {
                $eligibility = $this->eligibility($resident);

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
                'date_of_issue' => $certificate->date_of_issue
                    ?->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s'),
                'issuer_name' => $this->issuerHistoryName($certificate->issuer),
            ];
        })->all();

        return [
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
     * A missing migration row is eligible and carries a warning. The solo-parent
     * warning is added only when the requested certificate is a Solo Parent Certificate.
     *
     * @return array{eligible: bool, reason: ?string, warnings: list<string>}
     */
    public function eligibility(Resident $resident, ?TypeOfCertificate $type = null): array
    {
        $resident->loadMissing('status');
        $statusName = trim((string) ($resident->status?->resident_status ?? ''));

        if (mb_strtolower($statusName) !== 'active') {
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

        if ($this->isSoloParentCertificate($type)) {
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
        $document = $this->buildDocument(
            $type,
            $resident,
            $this->titleCase((string) $input['purpose_of_request']),
            $issuedAt,
            $this->overrides($input),
            false,
            $issuer,
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
     * @return array{pdf: string, certificate_id: int, filename: string}
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

        $purpose = $this->titleCase((string) $input['purpose_of_request']);
        $overrides = $this->overrides($input);
        $issuedAt = now();

        return DB::transaction(function () use ($type, $resident, $purpose, $issuedAt, $overrides, $attachSignature, $issuer): array {
            $certificate = $this->certificateRepository->create([
                'resident_id' => $resident->resident_id,
                'date_of_issue' => $issuedAt->toDateTimeString(),
                'purpose_of_request' => $purpose,
                'type_of_certificate_id' => $type->type_of_certificate_id,
                'issuer_id' => $issuer->user_id,
            ]);

            $document = $this->buildDocument(
                $type,
                $resident,
                $purpose,
                $issuedAt,
                $overrides,
                $attachSignature,
                $issuer,
            );

            $this->auditLogRepository->log(
                performedByUserId: $issuer->user_id,
                actionId: Action::CREATE,
                recordId: (int) $certificate->certificate_id,
                description: 'Create certificate',
                oldValue: null,
                newValue: (string) $type->type_of_certificate,
                target: 'record',
                entity: 'certificate',
            );

            $this->auditLogRepository->log(
                performedByUserId: $issuer->user_id,
                actionId: Action::PRINT,
                recordId: (int) $certificate->certificate_id,
                description: 'Print certificate',
                oldValue: null,
                newValue: $attachSignature ? 'With signature' : 'Without signature',
                target: 'signature',
                entity: 'certificate',
            );

            $pdf = $this->renderPdf($document);

            return [
                'pdf' => $pdf,
                'certificate_id' => (int) $certificate->certificate_id,
                'filename' => (Str::slug((string) $type->type_of_certificate) ?: 'certificate').'.pdf',
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

    private function isSoloParentCertificate(?TypeOfCertificate $type): bool
    {
        if (! $type instanceof TypeOfCertificate) {
            return false;
        }

        return mb_strtolower(trim((string) $type->type_of_certificate)) === 'solo parent certificate';
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

        if (! $this->signatureIsAvailable()) {
            throw ValidationException::withMessages([
                'attach_pb_signature' => 'No Punong Barangay signature uploaded. Ask a Super Admin to upload it in Settings.',
            ]);
        }
    }

    /**
     * @param  array{resident_name: ?string, address: ?string, requested_by: ?string}  $overrides
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
    ): array {
        $config = $this->typeConfig((int) $type->type_of_certificate_id) ?? [];
        $barangayName = $this->settingValue('barangay_name');
        $cityName = $this->settingValue('city_name');
        $residentName = $overrides['resident_name'] ?? $this->personName($resident);
        $address = $overrides['address'] ?? $this->address($resident, $barangayName, $cityName);
        $requester = $overrides['requested_by'] ?? $residentName;
        $logos = $this->logos();
        $issuerUser = $issuer?->loadMissing('personnel.position');

        $tokens = [
            'name' => mb_strtoupper($residentName, 'UTF-8'),
            'age_phrase' => $this->agePhrase($resident),
            'address' => $address,
            'pronoun' => $this->pronoun($resident),
            'requester' => $requester,
            'purpose' => $purpose,
            'purpose_clause' => ($config['print_purpose'] ?? true) ? ' for '.$purpose : '',
            'issued_date' => $this->issuedDateText($issuedAt),
            'barangay' => $barangayName,
            'barangay_short' => $this->barangayShort($barangayName),
            'city' => $cityName,
        ];

        $paragraphs = [];

        foreach ($config['paragraphs'] ?? [] as $template) {
            $paragraphs[] = $this->paragraphParts((string) $template, $tokens);
        }

        return [
            'title' => mb_strtoupper((string) ($config['title'] ?? $type->type_of_certificate), 'UTF-8'),
            'header' => [
                'city_name' => $cityName,
                'barangay_name' => mb_strtoupper($barangayName, 'UTF-8'),
                'barangay_email' => $this->settingValue('barangay_email'),
                'barangay_contact_no' => $this->settingValue('barangay_contact_no'),
            ],
            'logos' => $logos['images'],
            'missing_logos' => $logos['missing'],
            'paragraphs' => $paragraphs,
            'fields' => [
                'resident_name' => $residentName,
                'address' => $address,
                'requested_by' => $requester,
                'purpose' => $purpose,
                'age_phrase' => $tokens['age_phrase'],
                'pronoun' => $tokens['pronoun'],
                'issued_date' => $tokens['issued_date'],
                'date_of_issue' => $issuedAt->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
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
        ];
    }

    /**
     * @param  array<string, string>  $tokens
     * @return list<array{text: string, bold: bool}>
     */
    private function paragraphParts(string $template, array $tokens): array
    {
        $text = $template;

        foreach ($tokens as $key => $value) {
            if ($key === 'name') {
                continue;
            }

            $text = str_replace('{'.$key.'}', $value, $text);
        }

        $chunks = explode('{name}', $text);
        $parts = [];

        foreach ($chunks as $index => $chunk) {
            if ($chunk !== '') {
                $parts[] = ['text' => $chunk, 'bold' => false];
            }

            if ($index < count($chunks) - 1) {
                $parts[] = ['text' => $tokens['name'], 'bold' => true];
            }
        }

        return $this->emphasizeCertification($parts);
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
        $houseLot = trim((string) ($resident->household?->house_lot ?? ''));
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

    private function pronoun(Resident $resident): string
    {
        $sex = mb_strtolower(trim((string) ($resident->sex?->sex ?? '')));

        return match ($sex) {
            'male' => 'him',
            'female' => 'her',
            default => 'them',
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
