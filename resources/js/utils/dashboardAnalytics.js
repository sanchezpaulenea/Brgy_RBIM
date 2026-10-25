import { CHART_REPORTS } from '@/constants/dashboardCharts';

export function blankFilters(category) {
    const next = {};

    (category?.filters ?? []).forEach((filter) => {
        if (filter.type === 'checks') {
            next[filter.key] = { mode: 'multiple', ids: [], value: '', min: '', max: '' };

            return;
        }

        next[filter.key] = {
            mode: filter.type === 'lookup' ? 'all' : (filter.type === 'range' ? 'bounds' : (filter.choices?.[0]?.value ?? '')),
            ids: [],
            value: '',
            min: '',
            max: '',
        };
    });

    return next;
}

export function defaultColumnKeys(category) {
    return (category?.columns ?? [])
        .filter((column) => column.default)
        .map((column) => column.key);
}

export function reportMapping(chartKey, panel = null) {
    const mapping = CHART_REPORTS[panel?.key] ?? CHART_REPORTS[chartKey] ?? null;

    if (!mapping || !panel) {
        return mapping;
    }

    return {
        ...mapping,
        filter: panel.filter ?? mapping.filter,
        choices: panel.choices ?? mapping.choices,
        topics: panel.topic != null ? [panel.topic] : mapping.topics,
    };
}

export function buildFilterPayload(category, filterState, page = 1, selectedColumns = null) {
    const filters = {};

    (category?.filters ?? []).forEach((filter) => {
        const state = filterState[filter.key] ?? { mode: 'all', ids: [], value: '', min: '', max: '' };
        const choice = (filter.choices ?? []).find((item) => item.value === state.mode && item.numeric);
        const visible = filterIsVisible(filter, filterState);
        const sendsIds = filter.type === 'checks'
            || (filter.type === 'lookup' && state.mode !== 'all' && state.mode !== filter.sentinel_mode);

        filters[filter.key] = {
            mode: visible ? state.mode : (filter.type === 'lookup' ? 'all' : (filter.choices?.[0]?.value ?? 'all')),
            ids: visible && sendsIds ? state.ids.map(Number) : [],
            value: visible && choice ? Number(state.value) : null,
            min: visible && filter.type === 'range' && state.min !== '' ? state.min : null,
            max: visible && filter.type === 'range' && state.max !== '' ? state.max : null,
        };
    });

    return {
        category: category.key,
        filters,
        selected_columns: selectedColumns ?? defaultColumnKeys(category),
        page,
    };
}

export function applySliceToFilters(category, filterState, chartKey, slice, panel = null) {
    const next = structuredClone(filterState);
    const mapping = reportMapping(chartKey, panel);

    if (!mapping || !slice) {
        return next;
    }

    if (Array.isArray(mapping.topics) && next.topics) {
        const currentIds = next.topics.ids ?? [];
        const merged = [...new Set([...currentIds, ...mapping.topics])];
        next.topics = { mode: 'multiple', ids: merged, value: '', min: '', max: '' };
    }

    const key = mapping.filter;

    if (!key) {
        return next;
    }

    const current = next[key] ?? { mode: 'all', ids: [], value: '', min: '', max: '' };

    if (slice.kind === 'missing') {
        return next;
    }

    if (mapping.choices && slice.key) {
        next[key] = {
            ...current,
            mode: mapping.choices[slice.key] ?? slice.key,
            ids: [],
        };

        return next;
    }

    const filter = (category?.filters ?? []).find((item) => item.key === key);

    if (filter?.type === 'range' && (slice.min != null || slice.max != null)) {
        next[key] = {
            ...current,
            mode: 'bounds',
            min: slice.min == null ? '' : String(slice.min),
            max: slice.max == null ? '' : String(slice.max),
        };

        return next;
    }

    if (filter?.type === 'lookup') {
        if (slice.kind === 'others' && Array.isArray(slice.ids) && slice.ids.length) {
            next[key] = { ...current, mode: 'multiple', ids: slice.ids.map(Number) };
        } else if (slice.id != null) {
            next[key] = { ...current, mode: 'one', ids: [Number(slice.id)] };
        }

        return next;
    }

    if (filter?.type === 'options') {
        if (slice.choice) {
            next[key] = { ...current, mode: slice.choice, ids: [] };

            return next;
        }

        const matched = (filter.choices ?? []).find((choice) => (
            String(choice.value) === String(slice.id)
            || String(choice.label).toLowerCase() === String(slice.label ?? '').toLowerCase()
        ));

        if (matched) {
            next[key] = { ...current, mode: matched.value, ids: [] };

            return next;
        }

        if (slice.value != null) {
            const exact = (filter.choices ?? []).find((choice) => choice.op === 'eq_input');

            if (exact) {
                next[key] = { ...current, mode: exact.value, value: String(slice.value) };
            }
        }
    }

    return next;
}

export function filterIsVisible(filter, filterState) {
    const rule = filter.visible_when;

    if (!rule) {
        return true;
    }

    const other = filterState[rule.filter];

    if (!other) {
        return false;
    }

    if (Array.isArray(rule.except_modes) && rule.except_modes.includes(other.mode)) {
        return false;
    }

    if (rule.unless_sentinel) {
        if (other.mode === 'all') {
            return true;
        }

        if (rule.sentinel_mode && other.mode === rule.sentinel_mode) {
            return false;
        }

        if (!other.ids.length) {
            return false;
        }

        const sentinelId = Number(rule.sentinel_id);

        return other.ids.some((id) => Number(id) !== sentinelId);
    }

    if (Array.isArray(rule.include_ids)) {
        const needed = new Set(rule.include_ids.map((id) => Number(id)));

        return other.ids.some((id) => needed.has(Number(id)));
    }

    if (rule.only_sentinel) {
        if (rule.sentinel_mode && other.mode === rule.sentinel_mode) {
            return true;
        }

        if ((other.mode !== 'one' && other.mode !== 'multiple') || !other.ids.length) {
            return false;
        }

        const sentinelId = Number(rule.sentinel_id);

        return other.ids.every((id) => Number(id) === sentinelId);
    }

    if (Array.isArray(rule.lookup_ids)) {
        if (other.mode === 'all' || !other.ids.length) {
            return false;
        }

        const allowed = new Set(rule.lookup_ids.map((id) => Number(id)));

        return other.ids.every((id) => allowed.has(Number(id)));
    }

    return true;
}

export function visibleSlices(chart, category, filterState, chartKey, panel = null) {
    if (!chart) {
        return [];
    }

    const mapping = reportMapping(chartKey, panel);
    const state = mapping?.filter ? filterState[mapping.filter] : null;
    const source = chart.values ?? chart.slices ?? [];

    if (!state || !mapping?.filter) {
        return chart.slices ?? [];
    }

    if (state.mode === 'one' && state.ids.length === 1) {
        return source.filter((slice) => Number(slice.id) === Number(state.ids[0]));
    }

    if (state.mode === 'multiple' && state.ids.length) {
        const allowed = new Set(state.ids.map(Number));

        return source.filter((slice) => allowed.has(Number(slice.id)));
    }

    if ((category?.filters ?? []).find((filter) => filter.key === mapping.filter)?.type === 'range') {
        const min = state.min === '' || state.min == null ? null : Number(state.min);
        const max = state.max === '' || state.max == null ? null : Number(state.max);

        if (min == null && max == null) {
            return chart.slices ?? [];
        }

        return source.filter((slice) => {
            const sliceMin = slice.min == null ? null : Number(slice.min);
            const sliceMax = slice.max == null ? null : Number(slice.max);

            if (min != null && sliceMax != null && sliceMax < min) {
                return false;
            }

            if (max != null && sliceMin != null && sliceMin > max) {
                return false;
            }

            return true;
        });
    }

    const filter = (category?.filters ?? []).find((item) => item.key === mapping.filter);

    if (filter?.type === 'options') {
        const first = filter.choices?.[0]?.value;

        if (!state.mode || state.mode === first) {
            return chart.slices ?? [];
        }

        return source.filter((slice) => (
            slice.choice === state.mode
            || String(slice.id) === String(state.mode)
            || String(slice.label ?? '').toLowerCase() === String(
                (filter.choices ?? []).find((choice) => choice.value === state.mode)?.label ?? '',
            ).toLowerCase()
        ));
    }

    return chart.slices ?? [];
}

export function reportHeading(category, filterState) {
    const filters = (category?.filters ?? []).filter((filter) => filterIsVisible(filter, filterState));
    const active = filters.filter((filter) => {
        const state = filterState[filter.key];

        if (!state) {
            return false;
        }

        if (filter.type === 'lookup') {
            return state.mode !== 'all';
        }

        if (filter.type === 'range') {
            return state.min !== '' || state.max !== '';
        }

        if (filter.type === 'checks') {
            return state.ids.length > 0;
        }

        const first = filter.choices?.[0]?.value;

        return state.mode && state.mode !== first;
    });

    if (active.length === 1 && active[0].type === 'lookup') {
        const filter = active[0];
        const state = filterState[filter.key];
        const mode = (filter.modes ?? []).find((item) => item.value === state.mode);

        return {
            title: `${category.label} · ${mode?.label ?? filter.label}`,
            subtitle: '',
        };
    }

    if (!active.length) {
        return {
            title: `${category.label} · All`,
            subtitle: '',
        };
    }

    return {
        title: category.label,
        subtitle: active.map((filter) => filter.label).join('; '),
    };
}
