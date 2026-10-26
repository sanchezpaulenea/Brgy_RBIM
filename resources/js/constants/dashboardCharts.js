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
            {
                key: 'house_story_basement',
                title: 'House Story & Basement Level',
                subtitle: 'Households by stories and basement levels',
                panels: [
                    { key: 'house_story', title: 'House Story' },
                    { key: 'basement_level', title: 'Basement Level' },
                ],
            },
        ],
    },
    {
        key: 'housing-utilities',
        title: 'Housing and Utilities',
        description: 'Answers from the latest household questionnaire of each household.',
        charts: [
            {
                key: 'ownership',
                title: 'Ownership of Housing Unit & Lot',
                subtitle: 'Housing unit and lot tenure',
                panels: [
                    { key: 'ownership_unit', title: 'Housing Unit Ownership' },
                    { key: 'ownership_lot', title: 'Housing Lot Ownership' },
                ],
            },
            {
                key: 'fuel',
                title: 'Fuel for Lighting & Cooking',
                subtitle: 'Main fuel used for lighting and cooking',
                panels: [
                    { key: 'fuel_lighting', title: 'Lighting Fuel' },
                    { key: 'fuel_cooking', title: 'Cooking Fuel' },
                ],
            },
            { key: 'water_source', title: 'Main Source of Drinking Water', subtitle: 'Where households get drinking water' },
            { key: 'kitchen_garbage', title: 'Kitchen Garbage Disposal', subtitle: 'How kitchen garbage is disposed of' },
            { key: 'segregate_garbage', title: 'Segregate Garbage', subtitle: 'Households that do or do not segregate', visualization: 'boolean' },
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
            { key: 'female_died', title: 'Female Household Member Died in the Past 12 Months', subtitle: 'Households that reported a female death', visualization: 'boolean' },
            { key: 'child_died', title: 'Child Below 5 Household Member Died in the Past 12 Months', subtitle: 'Households that reported a child death', visualization: 'boolean' },
            { key: 'common_disease', title: 'Common Diseases That Cause Death', subtitle: 'Diseases reported across households' },
            { key: 'primary_need', title: 'Primary Needs of This Barangay', subtitle: 'Needs reported across households' },
            {
                key: 'pets',
                title: 'Households with Pets',
                subtitle: 'Recorded pets by specie, sex, rabies vaccination, and spay/neuter',
                panels: [
                    { key: 'pets', title: 'Specie' },
                    { key: 'pets_sex', title: 'Sex' },
                    { key: 'pets_rabies', title: 'Rabies Vaccination' },
                    { key: 'pets_spay', title: 'Spay / Neuter', visualization: 'boolean' },
                ],
            },
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
            {
                key: 'age',
                title: 'Age',
                subtitle: 'Age plus marital status, disability, resident type, and skill type',
                visualization: 'range',
                rangeFilter: 'age',
                panels: [
                    { key: 'age', title: 'Age' },
                    { key: 'age_marital', title: 'Marital Status', dataKey: 'marital_status' },
                    { key: 'age_disability', title: 'Disability', visualization: 'boolean' },
                    { key: 'age_resident_type', title: 'Type of Resident', dataKey: 'migration' },
                    { key: 'age_skill_type', title: 'Skill Type', dataKey: 'skills' },
                ],
            },
        ],
    },
    {
        key: 'education-economic',
        title: 'Education and Economic',
        description: 'Schooling and livelihood of registered residents.',
        charts: [
            {
                key: 'education',
                title: 'Education',
                subtitle: 'Highest education level and current enrollment',
                panels: [
                    { key: 'education', title: 'Highest Level of Education' },
                    { key: 'education_enrollment', title: 'Enrollment Status' },
                ],
            },
            {
                key: 'economic',
                title: 'Economic',
                subtitle: 'Monthly income, source of income, and work status',
                visualization: 'range',
                rangeFilter: 'monthly_income',
                panels: [
                    { key: 'economic', title: 'Monthly Income' },
                    { key: 'economic_source', title: 'Source of Income' },
                    { key: 'economic_work_status', title: 'Status of Work / Business' },
                ],
            },
        ],
    },
    {
        key: 'health-profile',
        title: 'Health',
        description: 'Health, infant, and women records.',
        charts: [
            {
                key: 'health',
                title: 'Health',
                subtitle: 'Insurance, facility visits, and disability',
                panels: [
                    { key: 'health', title: 'Health Insurance' },
                    { key: 'health_facility', title: 'Facility Visited Past 12 Months' },
                    { key: 'health_reason', title: 'Facility Visit Reason' },
                    { key: 'health_disability', title: 'Disability', visualization: 'boolean' },
                ],
            },
            {
                key: 'infant_health',
                title: 'Infant Health',
                subtitle: 'Place of delivery, birth attendant, and immunization',
                panels: [
                    { key: 'infant_health', title: 'Place of Delivery' },
                    { key: 'infant_attendant', title: 'Birth Attendant' },
                    { key: 'infant_immunization', title: 'Immunization' },
                ],
            },
            {
                key: 'women_health',
                title: 'Women’s Health',
                subtitle: 'Pregnancies, family planning, source, and intention',
                visualization: 'range',
                rangeFilter: 'pregnancies',
                panels: [
                    { key: 'women_health', title: 'Number of Pregnancies' },
                    { key: 'women_fp', title: 'Family Planning Method' },
                    { key: 'women_fp_source', title: 'Source of FP' },
                    { key: 'women_fp_intention', title: 'Intention to Use FP' },
                ],
            },
        ],
    },
    {
        key: 'civic-skills',
        title: 'Sociocivic, Migration, and Skills',
        description: 'Civic status, movement, certificates, and skills.',
        charts: [
            {
                key: 'sociocivic',
                title: 'Sociocivic',
                subtitle: 'Solo parents, senior citizens, and registered barangay voters',
                topics: [1, 2, 3],
                panels: [
                    { key: 'solo_parent_status', title: 'Solo Parent Status', topic: 1 },
                    { key: 'registered_senior', title: 'Registered Senior Citizen', visualization: 'boolean', topic: 2 },
                    { key: 'registered_voter', title: 'Registered Barangay Voter', topic: 3 },
                ],
            },
            { key: 'migration', title: 'Migration', subtitle: 'Residents by type of resident' },
            { key: 'ctc', title: 'Community Tax Certificate', subtitle: 'Residents 18+ with a valid CTC issued here or elsewhere' },
            {
                key: 'skills',
                title: 'Skills',
                subtitle: 'Skills development training and skill type of residents 15+',
                panels: [
                    { key: 'skills_training', title: 'Skills Development Training' },
                    { key: 'skills', title: 'Skill Type' },
                ],
            },
        ],
    },
];

export const CHART_REPORTS = {
    clan: { category: 'clan', filter: 'clan' },
    street: { category: 'street', filter: 'street' },
    household_status: { category: 'household_status', filter: 'household_status' },
    house_story_basement: { category: 'house_story_basement', filter: 'story' },
    house_story: { category: 'house_story_basement', filter: 'story' },
    basement_level: { category: 'house_story_basement', filter: 'basement' },
    ownership: { category: 'ownership', filter: 'unit' },
    ownership_unit: { category: 'ownership', filter: 'unit' },
    ownership_lot: { category: 'ownership', filter: 'lot' },
    fuel: { category: 'fuel', filter: 'lighting' },
    fuel_lighting: { category: 'fuel', filter: 'lighting' },
    fuel_cooking: { category: 'fuel', filter: 'cooking' },
    water_source: { category: 'water_source', filter: 'water_source' },
    kitchen_garbage: { category: 'kitchen_garbage', filter: 'kitchen_garbage' },
    segregate_garbage: { category: 'segregate_garbage', filter: 'segregate', choices: { yes: 'yes', no: 'no' } },
    toilet_facility: { category: 'toilet_facility', filter: 'toilet_facility' },
    building_type: { category: 'building_type', filter: 'building_type' },
    construction_material: { category: 'construction_material', filter: 'construction_material' },
    female_died: { category: 'female_died', filter: 'female_died', choices: { yes: 'has', no: 'none' } },
    child_died: { category: 'child_died', filter: 'child_died', choices: { yes: 'has', no: 'none' } },
    common_disease: { category: 'common_disease', filter: 'disease' },
    primary_need: { category: 'primary_need', filter: 'need' },
    pets: { category: 'pets', filter: 'specie' },
    pets_sex: { category: 'pets', filter: 'sex' },
    pets_rabies: { category: 'pets', filter: 'rabies' },
    pets_spay: { category: 'pets', filter: 'spay_neuter', choices: { yes: 'yes', no: 'no' } },
    relationship_to_hh: { category: 'relationship_to_hh', filter: 'relationship_to_hh' },
    nationality: { category: 'nationality', filter: 'nationality' },
    marital_status: { category: 'marital_status', filter: 'marital_status' },
    education: { category: 'education', filter: 'highest_level' },
    education_enrollment: { category: 'education', filter: 'enrollment' },
    economic: { category: 'economic', filter: 'monthly_income' },
    economic_source: { category: 'economic', filter: 'source' },
    economic_work_status: { category: 'economic', filter: 'work_status' },
    health: { category: 'health', filter: 'health_insurance' },
    health_facility: { category: 'health', filter: 'facility_visited' },
    health_reason: { category: 'health', filter: 'facility_visit_reason' },
    health_disability: { category: 'health', filter: 'disability', choices: { yes: 'with', no: 'without' } },
    infant_health: { category: 'infant_health', filter: 'place_of_delivery' },
    infant_attendant: { category: 'infant_health', filter: 'birth_attendant' },
    infant_immunization: { category: 'infant_health', filter: 'immunization' },
    women_health: { category: 'women_health', filter: 'pregnancies' },
    women_fp: { category: 'women_health', filter: 'family_planning_method' },
    women_fp_source: { category: 'women_health', filter: 'source_of_fp_method' },
    women_fp_intention: { category: 'women_health', filter: 'fp_intention' },
    sociocivic: { category: 'sociocivic', topics: [1, 2, 3] },
    solo_parent_status: { category: 'sociocivic', filter: 'solo_parent_status', topics: [1] },
    registered_senior: { category: 'sociocivic', filter: 'registered_senior', choices: { yes: 'yes', no: 'no' }, topics: [2] },
    registered_voter: { category: 'sociocivic', filter: 'registered_barangay_voter' },
    migration: { category: 'migration', filter: 'resident_type' },
    ctc: { category: 'ctc', filter: 'has_valid_ctc' },
    skills_training: { category: 'skills', filter: 'training' },
    skills: { category: 'skills', filter: 'skill_type' },
    age: { category: 'resident_cross_filter', filter: 'age' },
    age_marital: { category: 'resident_cross_filter', filter: 'marital_status' },
    age_disability: { category: 'resident_cross_filter', filter: 'disability', choices: { yes: 'with', no: 'without' } },
    age_resident_type: { category: 'resident_cross_filter', filter: 'resident_type' },
    age_skill_type: { category: 'resident_cross_filter', filter: 'skill_type' },
};
