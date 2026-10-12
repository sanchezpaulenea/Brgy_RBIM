<?php

return [

    /*
    | Paper size for every certificate. Dompdf paper names: a4, letter, legal.
    | Orientation is portrait unless this is changed to landscape.
    */
    'paper' => 'a4',

    'orientation' => 'portrait',

    /*
    | Same local disk the profile photo upload uses. The file name is fixed so a
    | new upload replaces the previous signature instead of accumulating files.
    */
    'signature_disk' => 'local',

    'signature_path' => 'signatures/punong_barangay.png',

    /*
    | Logo files live in resources/certificates/logos. A missing file is drawn
    | as an empty box; generation still succeeds.
    */
    'logos' => [
        'bagong_pilipinas' => 'bagong_pilipinas.png',
        'city_of_baguio' => 'city_of_baguio.png',
        'happy_hallow' => 'happy_hallow.png',
    ],

];
