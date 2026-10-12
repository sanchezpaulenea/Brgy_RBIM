<?php

/*
| Certificate body copy, keyed by type_of_certificate.type_of_certificate_id.
|
| allows_signature and print_purpose live here so they can be flipped in one place.
| allows_signature true: Certificate of Residency, Barangay Clearance, Certificate of Indigency.
| allows_signature false: First Time Job Seeker, Oath of Undertaking, Solo Parent Certificate.
| print_purpose true appends " for {purpose}" to the request sentence.
|
| Placeholders: {name} {age_phrase} {address} {pronoun} {requester} {purpose}
| {issued_date} {barangay} {city} {barangay_short} {purpose_clause}
*/

return [

    // Certificate of Residency (client-approved)
    1 => [
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

    // Barangay Clearance — DRAFT, pending client wording
    2 => [
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

    // First Time Job Seeker — DRAFT, pending client wording
    3 => [
        'title' => 'First Time Job Seeker',
        'allows_signature' => false,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify {name}, {age_phrase}, residing at {address}, is a qualified availee of RA 11261 or the First Time Jobseekers Assistance Act of 2019. I further certify that the holder/bearer was informed of his/her rights, including the duties and responsibilities accorded by RA 11261 through the Oath of Undertaking he/she has signed and executed in the presence of our barangay official.',
            'This CERTIFICATION was issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

    // Oath of Undertaking — DRAFT, pending client wording
    4 => [
        'title' => 'Oath of Undertaking',
        'allows_signature' => false,
        'print_purpose' => true,
        'paragraphs' => [
            'I, {name}, {age_phrase}, residing at {address}, availing the benefits of RA 11261, declare and undertake that this is the first time I will actually look for a job, that I understand the benefit may be availed only once, and that I will not use the issued document in any fraudulent manner.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

    // Certificate of Indigency — DRAFT, pending client wording
    5 => [
        'title' => 'Certificate of Indigency',
        'allows_signature' => true,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify {name} {age_phrase}, with a residence and postal address at {address} is a bona fide resident of this barangay and belongs to the indigent families of this community;',
            'This CERTIFICATION was issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

    // Solo Parent Certificate — DRAFT, pending client wording
    6 => [
        'title' => 'Solo Parent Certificate',
        'allows_signature' => false,
        'print_purpose' => true,
        'paragraphs' => [
            'This is to certify {name} {age_phrase}, with a residence and postal address at {address} is a bona fide resident of this barangay and is a solo parent;',
            'This CERTIFICATION was issued upon the request of {requester}{purpose_clause}.',
            'Issued this {issued_date}, at {barangay_short} Barangay, {city}.',
        ],
    ],

];
