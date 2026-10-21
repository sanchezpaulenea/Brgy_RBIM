/*
 * Ranked slices take these colours in order so the largest value in every
 * chart has the same colour. Adjacent colours differ in both hue and
 * lightness so neighbouring slices stay distinct for colour-blind readers.
 */
export const SLICE_PALETTE = [
    '#2d4c39',
    '#e0a526',
    '#3b7dbf',
    '#c4553a',
    '#7a5ba6',
    '#3fa392',
];

export const OTHERS_COLOR = '#94a3b8';

export const MISSING_COLOR = '#d5dbe3';

export function sliceColors(slices) {
    let ranked = 0;

    return slices.map((slice) => {
        if (slice.kind === 'others') {
            return OTHERS_COLOR;
        }

        if (slice.kind === 'missing') {
            return MISSING_COLOR;
        }

        const color = SLICE_PALETTE[ranked % SLICE_PALETTE.length];
        ranked += 1;

        return color;
    });
}

export const SUMMARY_CARDS = [
    { key: 'households', label: 'Total Households', icon: 'home' },
    { key: 'residents', label: 'Total Residents', icon: 'users' },
    { key: 'clans', label: 'Total Clans', icon: 'clan' },
    { key: 'streets', label: 'Total Streets', icon: 'map' },
];

export const CHART_SECTIONS = [
    {
        key: 'household-profile',
        title: 'Household Profile',
        description: 'How registered households are distributed across the barangay.',
        charts: [
            {
                key: 'clan',
                title: 'Clan',
                subtitle: 'Records per clan',
                views: [
                    { key: 'clan_households', label: 'Households' },
                    { key: 'clan_residents', label: 'Residents' },
                ],
            },
            { key: 'street', title: 'Street', subtitle: 'Households per street' },
            { key: 'household_status', title: 'Household Status', subtitle: 'Active, inactive, and archived households' },
            { key: 'house_story', title: 'House Story', subtitle: 'Households by number of stories' },
            { key: 'basement_level', title: 'Basement Level', subtitle: 'Households by number of basement levels' },
        ],
    },
    {
        key: 'housing-utilities',
        title: 'Housing and Utilities',
        description: 'Answers from the latest household questionnaire of each household.',
        charts: [
            { key: 'ownership', title: 'Ownership of Housing Unit and Lot', subtitle: 'Housing unit and lot tenure, combined' },
            { key: 'fuel_lighting', title: 'Fuel for Lighting', subtitle: 'Main fuel used for lighting' },
            { key: 'fuel_cooking', title: 'Fuel for Cooking', subtitle: 'Main fuel used for cooking' },
            { key: 'water_source', title: 'Main Source of Drinking Water', subtitle: 'Where households get drinking water' },
            { key: 'kitchen_garbage', title: 'Kitchen Garbage Disposal', subtitle: 'How kitchen garbage is disposed of' },
            { key: 'toilet_facility', title: 'Toilet Facility', subtitle: 'Type of toilet facility used' },
            { key: 'building_type', title: 'Type of Building/House', subtitle: 'Kind of building households live in' },
            { key: 'construction_material', title: 'Construction Material of the Outer Wall', subtitle: 'Main material of the outer walls' },
        ],
    },
    {
        key: 'barangay-records',
        title: 'Barangay-wide Records',
        description: 'Concerns reported by households for the whole barangay.',
        charts: [
            { key: 'common_disease', title: 'Common Diseases That Cause Death', subtitle: 'Diseases reported across households' },
            { key: 'primary_need', title: 'Primary Needs of This Barangay', subtitle: 'Needs reported across households' },
        ],
    },
    {
        key: 'resident-demographics',
        title: 'Resident Demographics',
        description: 'Profile of every registered resident.',
        charts: [
            { key: 'relationship_to_hh', title: 'Relationship to Household Head', subtitle: 'Residents by relationship to the head' },
            { key: 'nationality', title: 'Nationality', subtitle: 'Residents by nationality' },
            { key: 'marital_status', title: 'Marital Status', subtitle: 'Residents by marital status' },
        ],
    },
];
