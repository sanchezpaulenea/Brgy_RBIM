<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\ReportOptionsRequest;
use App\Http\Requests\Report\ReportPreviewRequest;
use App\Models\Report;
use App\Models\UserManagement\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    /**
     * GET /api/v1/reports/categories
     */
    public function index(): JsonResponse
    {
        $this->authorize('view', Report::class);

        return response()->json([
            'categories' => $this->reportService->listCategories(),
        ]);
    }

    /**
     * GET /api/v1/reports/options
     */
    public function options(ReportOptionsRequest $request): JsonResponse
    {
        $this->authorize('view', Report::class);

        return response()->json([
            'items' => $this->reportService->getFilterOptions(
                $request->string('category')->toString(),
                $request->string('filter')->toString(),
                $request->validated('search'),
                $request->boolean('all'),
                $request->validated('mode'),
            ),
        ]);
    }

    /**
     * POST /api/v1/reports/preview
     */
    public function preview(ReportPreviewRequest $request): JsonResponse
    {
        $this->authorize('view', Report::class);

        $validated = $request->validated();

        return response()->json(
            $this->reportService->preview(
                $validated,
                $validated['selected_columns'],
            ),
        );
    }

    /**
     * POST /api/v1/reports/export
     */
    public function export(ReportPreviewRequest $request): Response
    {
        $this->authorize('create', Report::class);

        $validated = $request->validated();

        /** @var User $performedBy */
        $performedBy = $request->user();

        return $this->reportService->export(
            $validated,
            $validated['selected_columns'],
            $validated['format'],
            $validated['title'],
            $validated['subtitle'] ?? null,
            $performedBy,
        );
    }
}
