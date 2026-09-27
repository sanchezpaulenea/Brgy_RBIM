<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ReportRepositoryInterface;
use App\Services\ReportSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @return list<array{id: int, label: string}>
     */
    public function getCategoryOptions(string $categoryKey, ?string $search = null): array
    {
        $category = ReportSchema::category($categoryKey);
        $modelClass = $category['filter_model'];
        $prototype = new $modelClass;
        $labelColumn = $category['filter_label_column'];
        $term = trim((string) $search);

        $options = $modelClass::query()
            ->when($term !== '', function (Builder $query) use ($labelColumn, $term): void {
                $query->where($labelColumn, 'like', $this->likeContains($term));
            })
            ->orderBy($labelColumn)
            ->limit(ReportSchema::OPTION_LIMIT)
            ->get();

        return $options
            ->map(fn (Model $option): array => $this->optionPayload($option, $prototype, $labelColumn))
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id: int, label: string}>
     */
    public function filterLabels(string $categoryKey, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $category = ReportSchema::category($categoryKey);
        $modelClass = $category['filter_model'];
        $prototype = new $modelClass;
        $keyName = $prototype->getKeyName();
        $labelColumn = $category['filter_label_column'];

        $labels = $modelClass::query()
            ->whereIn($keyName, $ids)
            ->get()
            ->keyBy(fn (Model $option): int => (int) $option->getAttribute($keyName));

        $ordered = [];

        foreach ($ids as $id) {
            $option = $labels->get((int) $id);

            if ($option instanceof Model) {
                $ordered[] = $this->optionPayload($option, $prototype, $labelColumn);
            }
        }

        return $ordered;
    }

    /**
     * @param  array{mode?: string, ids?: list<int>}  $filter
     * @param  list<string>  $selectedColumns
     */
    public function buildReportQuery(string $categoryKey, array $filter, array $selectedColumns): Builder
    {
        $category = ReportSchema::category($categoryKey);
        $modelClass = ReportSchema::levelModel($category['level']);
        $prototype = new $modelClass;
        $table = $prototype->getTable();
        $mode = $filter['mode'] ?? 'all';
        $ids = array_values(array_map('intval', $filter['ids'] ?? []));

        $query = $modelClass::query();

        if ($mode !== 'all' && $ids !== []) {
            $query->whereIn($table.'.'.$category['filter_column'], $ids);
        }

        $this->applySelectedRelations($query, $category['level'], $selectedColumns);

        return $query->orderByDesc($table.'.'.$prototype->getKeyName());
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<string>  $selectedColumns
     */
    private function applySelectedRelations(Builder $query, string $level, array $selectedColumns): void
    {
        $loads = ReportSchema::loads($level, $selectedColumns);
        $with = [];

        foreach ($loads['with'] as $path) {
            if ($path === 'residents') {
                $with['residents'] = fn ($residents) => $residents
                    ->orderBy('last_name')
                    ->orderBy('first_name');

                continue;
            }

            if ($path === 'pets') {
                $with['pets'] = fn ($pets) => $pets->orderBy('pet_census_id');

                continue;
            }

            $with[] = $path;
        }

        if ($with !== []) {
            $query->with($with);
        }

        if ($loads['withCount'] !== []) {
            $query->withCount($loads['withCount']);
        }
    }

    /**
     * @return array{id: int, label: string}
     */
    private function optionPayload(Model $option, Model $prototype, string $labelColumn): array
    {
        return [
            'id' => (int) $option->getAttribute($prototype->getKeyName()),
            'label' => (string) $option->getAttribute($labelColumn),
        ];
    }

    private function likeContains(string $term): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        return '%'.$escaped.'%';
    }
}
