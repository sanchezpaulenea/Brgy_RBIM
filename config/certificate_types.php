<?php

/*
| Certificate body copy, keyed by type_of_certificate.type_of_certificate_id.
|
| Wording for types 3, 4, 6, 7, 8, and 9 follows the Barangay Happy Hallow forms.
| Certificate of Residency and Barangay Clearance keep the earlier residency form
| because those two PDFs were not in the barangay set.
|
| kind is the stable code the service uses for eligibility and extra fields.
| allows_signature is true for every type. The Punong Barangay name and signature
| file are read from system_setting when the document is rendered.
|
| A paragraph that already says "issued upon the request" is replaced by the
| representative sentence when that option is on.
*/

return [

    1 => [
        'kind' => 'residency',
        'title' => 'Certificate of Residency',
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify {name} {age_phrase}, with a residence and postal address at {address} is a bona fide resident of this barangay;',
            'It is certified further that as per available records on file in this Barangay, the above-named person has no derogatory records and or any criminal/civil case pending investigation against {pronoun} as of this date;',
            'This CERTIFICATION was issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

    2 => [
        'kind' => 'clearance',
        'title' => 'Barangay Clearance',
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify {name} {age_phrase}, with a residence and postal address at {address} is a bona fide resident of this barangay;',
            'It is certified further that as per available records on file in this Barangay, the above-named person has no derogatory records and or any criminal/civil case pending investigation against {pronoun} as of this date;',
            'This CERTIFICATION was issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

    3 => [
        'kind' => 'first_time_job_seeker',
        'title' => 'Barangay Certification',
        'subtitle' => '(FIRST TIME JOBSEEKERS ASSISTANCE ACT-RA 11261)',
        'office' => null,
        'salutation' => 'To Whom It May Concern:',
        'signature' => 'approved',
        'stamp' => false,
        'revised_note' => 'Revised as of 21 October 2021',
        'meta_right' => true,
        'show_certificate_number' => true,
        'footer_split' => true,
        'bold_issuer' => true,
        'packet' => true,
        'footer_notes' => [
            'THIS FORM NEED NOT BE NOTARIZED',
            'RA 11261 FORM 1',
        ],
        'allows_signature' => true,
        'print_purpose' => false,
        'paragraphs' => [
            'This is to certify that {name}, {years_old}, with residence and postal address at **{address}**, is a qualified availee of RA 11261 or the **First Time Jobseekers Act of 2019**.',
            'I further certify that the holder was informed of {possessive} rights, including the duties and responsibilities accorded by RA 11261 through the Oath of Undertaking {subject_pronoun} signed and executed in the presence of our Barangay Official.',
            'Signed on the **{issued_ordinal} day of {issued_month} {issued_year}**, in the Barangay Hall of {barangay_short},City of {city_name}.',
            'This certification is valid only for one (1) year from the issuance.',
        ],
    ],

    4 => [
        'kind' => 'oath',
        'title' => 'Oath of Undertaking',
        'salutation' => null,
        'signature' => 'oath',
        'stamp' => false,
        'title_bold' => false,
        'dense' => true,
        'packet' => true,
        'footer_notes' => [
            'THIS FORM NEED NOT BE NOTARIZED',
            'RA 11261 FORM 1',
        ],
        'allows_signature' => true,
        'print_purpose' => false,
        'paragraphs' => [
            'I, {name}, {years_of_age}, resident of {address} availing the benefits of RA 11261, otherwise known as the First Time Jobseekers Act of 2019, do hereby declare, agree and undertake to abide and be bound by the following:',
            '1. That this is my first time actively looking for a job, and therefore requesting that a Barangay Certification be issued in my favor to avail the benefits of the law;',
            '2. That I am aware that the benefit and privilege/s under the said law shall be valid only for one (1) year from the date the Barangay Certification was issued;',
            '3. That I can avail of the benefit only once;',
            '4. That I understand that my personal information shall be included in the Roster/List of First Time Jobseekers and will be used for any unlawful purpose;',
            '5. That I will inform and/or report to the Barangay personally, through text or other means, or through my family/relatives once I got employed;and',
            '6. That I am not a beneficiary of the JobStart Program under RA.NO.10869 and other laws that give similar exemptions for the documents or transactions exempted under RA. NO. 11261',
            '7. That if issued the requested Certification, I will not use the same in any fraud, neither falsify nor help and/or assist in the fabrication of the said certification.',
            '8. That this undertaking is made solely for the purpose of obtaining a Barangay Certification consistent with the objective RA.NO 11261 and not for any other purpose.',
            '9. That I consent to the use of my personal information pursuant to the DATA PRIVACY ACT and other applicable laws, rules, and regulations.',
            'Signed this {issued_ordinal} day of {issued_month} {issued_year} in the {city_of}.',
        ],
    ],

    6 => [
        'kind' => 'solo_parent',
        'title' => 'Affidavit of Barangay Official that a Solo Parent is a Resident of this Barangay',
        'office' => 'OFFICE OF THE PUNONG BARANGAY',
        'salutation' => null,
        'signature' => 'affidavit',
        'stamp' => true,
        'dense' => true,
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify that {name} , {years_old} , presently residing at **{address}** is a single parent as defined under Section 3A of the Solo Parent Welfare of RA 8972 as expanded by **RA 11861** __(SOLO PARENT-{circumstance})__;',
            '~{possessive} children',
            '{children_table}',
            'are under {possessive} custody.',
            'Upon verification, the solo parent applicant is not involved into a new relationship up to present which gives them consideration to be a certified solo parent. This affidavit is being issued upon the request of {name}{purpose_clause}.',
            'Issued on the {issued_on} at {barangay_short} Barangay Hall.',
        ],
    ],

    7 => [
        'kind' => 'low_income',
        'title' => 'Low Income Certification',
        'title_with_amount' => 'Certificate of Low Income',
        'office' => 'OFFICE OF THE SANGGUNIANG BARANGAY',
        'salutation' => 'To Whom It May Concern:',
        'salutation_with_amount' => 'To Whom it may concern,',
        'signature' => 'income',
        'stamp' => true,
        'bold_issuer' => true,
        'authority_line' => 'By the Authority of the Punong Barangay:',
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to CERTIFY that {courtesy}{name}, legal age, {civil_status}, residing at {address}, is a bona fide resident of this barangay since {resident_since} to present;',
            'It is certified further that the above mentioned name belongs to the low income group/ below minimum wage.',
            'This BARANGAY CERTIFICATION is being issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_on} at {barangay_short} Barangay, {city}.',
        ],
        'paragraphs_with_amount' => [
            'This is to certify that **{courtesy}{name}**, {years_old} , {nationality}, with a residence and postal address at **{address}** is a bona fide resident of this barangay;',
            'This is to further certify that the above -name person`s source of income or occupation is inadequate to support family basic needs and belongs to the family whose annual income is less than the regional threshold of {income_words} ({income_figure}) pesos.',
            'This **BARANGAY CERTIFICATION** is issued upon the request of **{courtesy}{requester}** for **{purpose}**.',
            'Issued this **{issued_on}** at {barangay_short} Barangay, {city}.',
        ],
    ],

    8 => [
        'kind' => 'death',
        'title' => 'C E R T I F I C A T I O N',
        'office' => 'OFFICE OF THE HAPPY HALLOW BARANGAY INDIGENOUS PEOPLES MANDATORY REPRESENTATIVE',
        'salutation' => 'To Whom It May Concern:',
        'signature' => 'authority',
        'stamp' => true,
        'officer_line' => false,
        'bold_issuer' => true,
        'authority_line' => 'By the Authority of the Punong Barangay:',
        'allows_signature' => true,
        'print_purpose' => false,
        'paragraphs' => [
            'This is to certify that {name}, {years_old}, {nationality} with residence and postal address at {address} was a bonafide resident of this barangay;',
            'This is to certify furthermore that as part of ethnic and cultural practice/s of the {ethnic_group}, the above-named person was buried at the family\'s ancestral land at the above address which their family owned and is located 50 meters far away from the water source;',
            'It was coordinated to the neighborhood with **NO OBJECTION** and **DOES NOT POSE AS NUISANCE TO THE PUBLIC**.',
            '{death_request}',
            'Issued on the {issued_on_comma} at {barangay_short} Barangay, {city}.',
        ],
    ],

    9 => [
        'kind' => 'good_moral',
        'title' => 'Good Moral Certificate',
        'office' => 'OFFICE OF THE PUNONG BARANGAY',
        'salutation' => 'TO WHOM IT MAY CONCERN:',
        'signature' => 'punong',
        'stamp' => false,
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify that {name}, {years_old} , {nationality}, {civil_status_lower} with postal and residential address of ~~{address}~~ is a bona fide resident of this barangay.',
            'It is certified further that the above named person is of good moral character and has no derogatory records and or any criminal/civil case pending investigation against {pronoun} in the barangay;',
            'This good moral certificate was issued upon the request of **{courtesy}{requester}**{purpose_clause}.',
            'Issued this {issued_on} at {barangay_short} Barangay, {city}.',
        ],
    ],

];
