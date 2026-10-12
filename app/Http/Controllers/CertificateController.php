<?php

namespace App\Http\Controllers;

use App\Http\Requests\Certificate\CertificatePreviewRequest;
use App\Http\Requests\Certificate\SearchCertificateResidentRequest;
use App\Http\Requests\Certificate\StoreCertificateRequest;
use App\Models\Certificate\Certificate;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
    ) {}

    /**
     * GET /api/v1/certificates/types
     */
    public function types(): JsonResponse
    {
        $this->authorize('viewAny', Certificate::class);

        return response()->json([
            'types' => $this->certificateService->listTypes(),
        ]);
    }

    /**
     * GET /api/v1/certificates/residents
     */
    public function residents(SearchCertificateResidentRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Certificate::class);

        return response()->json([
            'residents' => $this->certificateService->searchResidents(
                $request->string('search')->toString(),
            ),
        ]);
    }

    /**
     * GET /api/v1/certificates/residents/{resident}/history
     */
    public function history(Request $request, Resident $resident): JsonResponse
    {
        $this->authorize('viewAny', Certificate::class);

        return response()->json(
            $this->certificateService->history($resident, max(1, $request->integer('page', 1))),
        );
    }

    /**
     * POST /api/v1/certificates/preview
     */
    public function preview(CertificatePreviewRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Certificate::class);
        $this->authorizeOverrides($request);

        /** @var User $user */
        $user = $request->user();

        return response()->json(
            $this->certificateService->preview($request->validated(), $user),
        );
    }

    /**
     * POST /api/v1/certificates
     */
    public function store(StoreCertificateRequest $request): Response
    {
        $this->authorize('create', Certificate::class);
        $this->authorizeOverrides($request);

        /** @var User $user */
        $user = $request->user();

        $payload = $request->validated();
        $payload['attach_pb_signature'] = $request->boolean('attach_pb_signature');

        $issued = $this->certificateService->issue($payload, $user);

        return response($issued['pdf'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$issued['filename'].'"',
            'X-Certificate-Id' => (string) $issued['certificate_id'],
        ]);
    }

    /**
     * GET /api/v1/certificates/signature
     */
    public function signature(): StreamedResponse
    {
        $this->authorize('viewAny', Certificate::class);

        return $this->certificateService->signatureResponse();
    }

    private function authorizeOverrides(CertificatePreviewRequest|StoreCertificateRequest $request): void
    {
        if ($request->overrideValues() === []) {
            return;
        }

        $this->authorize('edit', Certificate::class);
    }
}
