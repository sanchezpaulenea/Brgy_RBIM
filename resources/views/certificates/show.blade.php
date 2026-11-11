<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }}</title>
    <style>
        @php
            $arialFonts = $arialFonts ?? [];
            $sheets = $document['pages'] ?? [$document];
            $exactDocument = false;
            foreach ($sheets as $sheet) {
                if (! empty($sheet['exact'])) {
                    $exactDocument = true;
                    break;
                }
            }
        @endphp
        @if (! empty($arialFonts['regular']))
        @@font-face { font-family: "Arial"; font-weight: normal; font-style: normal; src: url("{{ $arialFonts['regular'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['bold']))
        @@font-face { font-family: "Arial"; font-weight: bold; font-style: normal; src: url("{{ $arialFonts['bold'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['italic']))
        @@font-face { font-family: "Arial"; font-weight: normal; font-style: italic; src: url("{{ $arialFonts['italic'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['bold_italic']))
        @@font-face { font-family: "Arial"; font-weight: bold; font-style: italic; src: url("{{ $arialFonts['bold_italic'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['georgia']))
        @@font-face { font-family: "Georgia"; font-weight: normal; font-style: normal; src: url("{{ $arialFonts['georgia'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['georgia_bold']))
        @@font-face { font-family: "Georgia"; font-weight: bold; font-style: normal; src: url("{{ $arialFonts['georgia_bold'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['times_italic']))
        @@font-face { font-family: "Times New Roman"; font-weight: normal; font-style: italic; src: url("{{ $arialFonts['times_italic'] }}") format("truetype"); }
        @endif
        @if (! empty($arialFonts['times_bold_italic']))
        @@font-face { font-family: "Times New Roman"; font-weight: bold; font-style: italic; src: url("{{ $arialFonts['times_bold_italic'] }}") format("truetype"); }
        @endif
        @page { margin: {{ $exactDocument ? '0' : '40px 48px 36px' }}; }
        body { font-family: Arial, sans-serif; font-size: 15px; color: #111; line-height: 1.5; }
        .sheet-table { width: 100%; border-collapse: collapse; }
        .sheet-main { vertical-align: top; }
        .sheet-close { vertical-align: top; }
        .sheet-gap { height: 340px; font-size: 1px; line-height: 1px; }
        .dense .sheet-gap { height: 160px; }
        .center { text-align: center; }
        .header-line { margin: 0; line-height: 1.3; text-align: center; }
        .republic { font-weight: bold; font-size: 14px; }
        .barangay { font-weight: bold; font-size: 15px; }
        .contact { text-decoration: underline; font-size: 12px; }
        .title { font-family: Arial, sans-serif; font-weight: bold; font-size: 22px; text-align: center; letter-spacing: 0.8px; margin: 22px 0 16px; }
        .title-plain { font-weight: normal; font-size: 18px; }
        .title-long { font-size: 16px; margin: 14px 0 10px; }
        .subtitle, .office, .meta { text-align: center; margin: 0; line-height: 1.3; }
        .office { font-weight: bold; font-size: 13px; margin-top: 8px; }
        .subtitle { font-weight: bold; font-size: 14px; margin-bottom: 8px; }
        .meta { font-size: 12px; }
        .salutation { font-weight: bold; font-size: 16px; margin: 0 0 14px; text-align: left; }
        .body { text-align: justify; text-indent: 40px; margin: 0 0 16px; line-height: 1.55; font-weight: normal; }
        .dense { font-size: 13px; }
        .dense .title { margin: 12px 0 8px; font-size: 18px; }
        .dense .salutation { font-size: 14px; }
        .dense .body { text-indent: 24px; margin: 0 0 8px; line-height: 1.4; }
        .children { width: 100%; border-collapse: collapse; margin: 8px 0 10px; font-size: 11px; }
        .children th, .children td { border: 1px solid #111; padding: 3px 5px; }
        .children th { text-align: center; }
        .footer-note { text-align: center; font-weight: bold; margin: 8px 0 0; font-size: 11px; }
        .sign-label { margin: 12px 0 0; font-weight: bold; }
        .bold { font-weight: bold; }
        .regular { font-weight: normal; }
        .italic { font-style: italic; }
        .underline { text-decoration: underline; }
        .flush { text-indent: 0; margin-bottom: 4px; }
        .meta-right { text-align: right; font-style: italic; }
        .meta-key { font-style: italic; text-decoration: underline; }
        .sheet-break { page-break-before: always; }
        .footer-left { text-align: left; font-weight: bold; margin: 8px 0 0; font-size: 11px; }
        .letterhead { width: 100%; border-collapse: collapse; margin: 0 0 8px; }
        .logo-cell { width: 30%; vertical-align: middle; text-align: left; }
        .logo-cell-right { width: 30%; text-align: right; vertical-align: middle; }
        .logo-img { width: 78px; height: 78px; }
        .logo-gap { width: 8px; }
        .logo-empty { width: 78px; height: 78px; border: 1px solid #bbb; }
        .letterhead-copy { width: 40%; text-align: center; vertical-align: middle; }
        .pb-block { width: 260px; text-align: center; }
        .pb-inline { width: 100%; text-align: right; }
        .pb-name { font-weight: bold; text-decoration: underline; text-transform: uppercase; font-size: 14px; margin: 0 auto; min-width: 150px; display: inline-block; }
        .pb-title { margin: 2px 0 0; font-size: 14px; }
        .sig-space { height: 42px; }
        .sig-img { height: 42px; }
        .authority { margin-top: 8px; font-size: 15px; }
        .wet-line { border-bottom: 1px solid #111; width: 220px; height: 28px; margin-top: 8px; }
        .officer { margin: 2px 0 0; font-size: 15px; }
        .dense .authority, .dense .officer { font-size: 13px; }
        .stamp-outer { border: 1.5px solid #9c3b32; border-radius: 8px; padding: 2px; width: 230px; margin-top: 4px; color: #9c3b32; }
        .stamp-inner { border: 1px solid #9c3b32; border-radius: 6px; padding: 6px 8px 8px; font-size: 8px; line-height: 1.2; }
        .stamp-title { font-weight: bold; text-align: center; letter-spacing: 0.2px; margin: 0 0 6px; }
        .stamp-label { font-size: 7.5px; }
        .stamp-blank { border-bottom: 1px solid #9c3b32; height: 12px; margin-top: 2px; }
        .exact { position: relative; }
        .watermark { position: fixed; top: 390pt; left: 8pt; font-family: Arial, sans-serif; font-size: 45pt; font-weight: normal; color: #e4e4e4; transform: rotate(-45deg); white-space: nowrap; line-height: 1; z-index: -1; }
        .exact-mast { padding: 20pt 26pt 0 28pt; }
        .layout-good-moral .exact-mast { padding-top: 72pt; }
        .layout-low-income .exact-mast { padding-top: 48pt; }
        .exact-copy { padding: 0 69pt 22pt 72pt; font-family: Arial, sans-serif; font-size: 12pt; color: #111; }
        .layout-low-income .exact-copy { padding: 0 56pt 16pt 72pt; }
        .mast-table { width: 100%; border-collapse: collapse; }
        .mast-left { width: 158pt; vertical-align: middle; }
        .mast-right { width: 90pt; text-align: right; vertical-align: middle; }
        .mast-center { text-align: center; vertical-align: middle; }
        .logo-bp { width: 68pt; height: 64pt; }
        .logo-city { width: 72pt; height: 72pt; margin-left: 2pt; }
        .logo-brgy { width: 80pt; height: 80pt; }
        .layout-good-moral .mast-left { width: 120pt; text-align: center; }
        .layout-good-moral .logo-city { width: 82pt; height: 82pt; margin-left: 0; }
        .layout-good-moral .logo-brgy { width: 86pt; height: 86pt; }
        .mast-line { font-family: Georgia, "Times New Roman", serif; font-size: 13pt; line-height: 14.7pt; margin: 0; text-align: center; font-weight: normal; }
        .mast-line.strong { font-weight: bold; }
        .mast-line.ruled { text-decoration: underline; }
        .exact .office { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: center; margin: 16pt 0 0; line-height: 1.2; }
        .exact .doc-title { font-family: Arial, sans-serif; font-weight: bold; font-size: 16pt; text-align: center; margin: 14pt 0 12pt; line-height: 1.2; }
        .exact .doc-subtitle { font-family: Georgia, "Times New Roman", serif; font-weight: bold; font-size: 12pt; text-align: center; margin: 1pt 0 6pt; line-height: 14pt; }
        .layout-jobseeker .doc-title { font-family: Georgia, "Times New Roman", serif; font-size: 12pt; margin: 1pt 0 0; }
        .layout-oath .doc-title { font-family: Arial, sans-serif; font-size: 12pt; margin: 12pt 0 10pt; }
        .layout-good-moral .office, .layout-low-income .office { margin-top: 18pt; }
        .layout-good-moral .doc-title { margin: 16pt 0 14pt; }
        .layout-low-income .doc-title { margin: 20pt 0 16pt; font-size: 18pt; }
        .revised-top { font-family: "Times New Roman", Times, serif; font-style: italic; font-weight: normal; font-size: 12pt; text-align: right; margin: 8pt 0 0; line-height: 14pt; }
        .cert-no { font-family: "Times New Roman", Times, serif; font-style: italic; font-size: 12pt; text-align: right; text-decoration: underline; margin: 0; line-height: 14pt; }
        .exact .salutation { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: left; margin: 10pt 0 8pt; line-height: 14pt; }
        .exact .body { font-family: Arial, sans-serif; font-size: 12pt; font-weight: normal; text-align: justify; text-indent: 36pt; line-height: 18pt; margin: 0 0 8pt; }
        .layout-jobseeker .body { line-height: 20.7pt; margin: 0 0 10pt; }
        .layout-good-moral .body { line-height: 18pt; margin: 0 0 10pt; }
        .layout-low-income .body { line-height: 16pt; margin: 0 0 11pt; }
        .good-sign { margin-top: 48pt; }
        .layout-oath .body { text-indent: 0; font-weight: bold; line-height: 16pt; margin: 0; }
        .layout-oath .body.item { margin-left: 36pt; text-indent: -18pt; }
        .layout-oath .body.lead { margin-bottom: 10pt; }
        .exact .bold { font-weight: bold; }
        .exact .italic { font-style: italic; }
        .exact .underline { text-decoration: underline; }
        .approved { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: center; margin: 8pt 0 0; }
        .sig-center { text-align: center; }
        .sig-img-exact { height: 34pt; }
        .sig-gap { height: 34pt; }
        .pb-line { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: center; text-decoration: underline; margin: 0; line-height: 14pt; }
        .pb-role { font-family: Arial, sans-serif; font-size: 12pt; text-align: center; margin: 0; line-height: 14pt; font-weight: normal; }
        .witness-label { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: left; margin: 14pt 0 10pt; }
        .witness-name { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; margin: 0; line-height: 15pt; text-align: left; }
        .witness-role, .witness-date { font-family: Arial, sans-serif; font-size: 12pt; font-weight: normal; margin: 0; line-height: 15pt; text-align: left; }
        .form-footer { width: 100%; border-collapse: collapse; margin-top: 42pt; }
        .form-revised { font-family: "Times New Roman", Times, serif; font-style: italic; font-weight: bold; font-size: 12pt; text-align: right; }
        .form-note { font-family: Arial, sans-serif; font-weight: bold; font-style: italic; font-size: 10pt; text-align: left; margin: 0; line-height: 12pt; }
        .oath-sign { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: right; margin: 2pt 0 8pt; }
        .oath-seeker { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: left; margin: 0; }
        .oath-seeker-role { font-size: 11pt; font-weight: bold; }
        .oath-witness, .oath-date { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; text-align: left; margin: 8pt 0 0; }
        .oath-notes { margin-top: 12pt; }
        .layout-oath .form-note { font-size: 10pt; }
        .income-authority { font-family: Arial, sans-serif; font-size: 12pt; margin: 0; line-height: 16pt; }
        .income-name { font-family: Arial, sans-serif; font-weight: bold; font-size: 12pt; margin: 0; line-height: 16pt; }
        .income-role { font-family: Arial, sans-serif; font-size: 12pt; margin: 0; line-height: 16pt; }
        .layout-low-income .stamp-outer { width: 168pt; }
        .sign-right { text-align: center; }
    </style>
</head>
<body>
@foreach ($sheets as $sheetIndex => $document)
    @if (! empty($document['exact']))
        @include('certificates.exact')
    @else
    <div class="sheet {{ $sheetIndex > 0 ? 'sheet-break' : '' }} {{ ! empty($document['dense']) ? 'dense' : '' }}" style="font-weight: normal;">
    <table class="sheet-table" width="100%" cellspacing="0" cellpadding="0">
    <tr>
    <td class="sheet-main" valign="top">
    <table class="letterhead" cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell" width="30%" align="left" valign="middle">
                <table cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="middle">
                            @if (! empty($document['logos']['bagong_pilipinas']))
                                <img class="logo-img" src="{{ $document['logos']['bagong_pilipinas'] }}" alt="">
                            @else
                                <div class="logo-empty">&nbsp;</div>
                            @endif
                        </td>
                        <td class="logo-gap">&nbsp;</td>
                        <td valign="middle">
                            @if (! empty($document['logos']['city_of_baguio']))
                                <img class="logo-img" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
                            @else
                                <div class="logo-empty">&nbsp;</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td class="letterhead-copy" width="40%" align="center" valign="middle">
                <p class="header-line republic">Republic of the Philippines</p>
                <p class="header-line">{{ $document['header']['city_name'] }}</p>
                <p class="header-line barangay">{{ $document['header']['barangay_name'] }}</p>
                <p class="header-line contact">Official email address: {{ $document['header']['barangay_email'] }}</p>
                <p class="header-line contact">Tel. No. {{ $document['header']['barangay_contact_no'] }}</p>
            </td>
            <td class="logo-cell-right" width="30%" align="right" valign="middle">
                @if (! empty($document['logos']['happy_hallow']))
                    <img class="logo-img" src="{{ $document['logos']['happy_hallow'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
            </td>
        </tr>
    </table>

    @if (! empty($document['office']))
        <p class="office">{{ $document['office'] }}</p>
    @endif
    @if (! empty($document['revised_note']))
        <p class="meta {{ ! empty($document['meta_right']) ? 'meta-right' : '' }}">{{ $document['revised_note'] }}</p>
    @endif
    @if (! empty($document['show_certificate_number']))
        <p class="meta meta-right meta-key">Barangay Certificate Number: {{ $document['certificate_number'] ?? '' }}</p>
    @endif

    <p class="title {{ strlen((string) $document['title']) > 40 ? 'title-long' : '' }} {{ ($document['title_bold'] ?? true) ? '' : 'title-plain' }}">{{ $document['title'] }}</p>
    @if (! empty($document['subtitle']))
        <p class="subtitle">{{ $document['subtitle'] }}</p>
    @endif
    @if (! empty($document['salutation']))
        <p class="salutation">{{ $document['salutation'] }}</p>
    @endif

    @foreach ($document['paragraphs'] as $index => $paragraph)
        @if (($document['children_after'] ?? null) === $index)
            @include('certificates.partials.children', ['children' => $document['children'] ?? []])
        @endif
        @php
            $parts = $paragraph['parts'] ?? $paragraph;
            $flush = is_array($paragraph) && ($paragraph['flush'] ?? false);
        @endphp
        <p class="body {{ $flush ? 'flush' : '' }}">
            @foreach ($parts as $part)
                @php
                    $partClass = trim(
                        (($part['bold'] ?? false) ? 'bold ' : '')
                        .(($part['italic'] ?? false) ? 'italic ' : '')
                        .(($part['underline'] ?? false) ? 'underline' : '')
                    );
                @endphp
                @if ($partClass !== '')
                    <span class="{{ $partClass }}">{{ $part['text'] }}</span>
                @else
                    <span class="regular">{{ $part['text'] }}</span>
                @endif
            @endforeach
        </p>
    @endforeach
    @if (($document['children_after'] ?? null) === count($document['paragraphs']))
        @include('certificates.partials.children', ['children' => $document['children'] ?? []])
    @endif

    </td>
    </tr>
    <tr>
    <td class="sheet-close" valign="top">
    <div class="sheet-gap">&nbsp;</div>
    @php
        $style = $document['signature_style'] ?? 'authority';
    @endphp

    @if ($style === 'punong')
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 22px;">
            <tr>
                <td>&nbsp;</td>
                <td class="pb-block" valign="top">
                    @include('certificates.partials.punong', ['document' => $document])
                </td>
            </tr>
        </table>
    @elseif ($style === 'approved')
        <p class="sign-label" style="text-align: center;">Approved by:</p>
        <div class="pb-block" style="margin-left: auto; margin-right: auto;">
            @include('certificates.partials.punong', ['document' => $document])
        </div>
        <p class="sign-label">Witnessed by:</p>
        <p class="officer bold">{{ $document['fields']['issuer_name'] }}</p>
        <p class="officer">{{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}</p>
        <p class="officer">Date: <span class="bold">{{ $document['fields']['witness_date'] }}</span></p>
    @elseif ($style === 'oath')
        <div class="pb-inline" style="margin-top: 18px; margin-left: auto;">
            @include('certificates.partials.punong', ['document' => $document, 'inline' => true])
        </div>
        <p class="officer bold">{{ $document['fields']['resident_name'] }}, FIRST TIME JOB SEEKER</p>
        <p class="officer">Witnessed by:<span class="bold">{{ $document['fields']['issuer_name'] }}</span>, {{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}/Officer of the day</p>
        <p class="officer">DATE: {{ $document['fields']['witness_date'] }}</p>
    @elseif ($style === 'income')
        <p class="authority">{{ $document['authority_line'] }}</p>
        <p class="officer bold">{{ $document['fields']['issuer_name'] }}</p>
        <p class="officer">Officer for the day</p>
        <p class="officer bold">{{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}</p>
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
            <tr>
                <td valign="top">&nbsp;</td>
                <td class="pb-block" valign="bottom">
                    @include('certificates.partials.punong', ['document' => $document])
                </td>
            </tr>
            @if (! empty($document['show_stamp']))
                <tr>
                    <td>&nbsp;</td>
                    <td valign="top" align="right" style="padding-top: 6px;">
                        @include('certificates.partials.stamp')
                    </td>
                </tr>
            @endif
        </table>
    @elseif ($style === 'affidavit')
        <div style="text-align: right;">
            @if (! empty($document['signature_data_uri']))
                <img class="sig-img" src="{{ $document['signature_data_uri'] }}" alt="">
            @endif
            <p class="officer">Barangay Captain: <span class="bold">{{ $document['fields']['punong_barangay_name'] }}</span></p>
        </div>
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
            <tr>
                <td width="50%" valign="top">
                    <p class="officer bold">Witness</p>
                    <p class="officer">Name: {{ $document['affidavit']['president_name'] ?? '' }}</p>
                    <p class="officer">Solo Parent President</p>
                    <p class="officer">Complete Address: {{ $document['affidavit']['president_address'] ?? '' }}</p>
                    <div class="wet-line">&nbsp;</div>
                    <p class="officer">Signature</p>
                </td>
                <td width="50%" valign="top">
                    <p class="officer bold">NEIGHBOR:</p>
                    <p class="officer">Name: {{ $document['affidavit']['neighbor_name'] ?? '' }}</p>
                    <p class="officer">Complete Address: {{ $document['affidavit']['neighbor_address'] ?? '' }}</p>
                    <div class="wet-line">&nbsp;</div>
                    <p class="officer">Signature</p>
                </td>
            </tr>
        </table>
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
            <tr>
                <td width="55%" valign="top">
                    <p class="officer">Name: {{ $document['affidavit']['kagawad_name'] ?? '' }}</p>
                    <p class="officer">Kagawad on Social Services</p>
                    <div class="wet-line">&nbsp;</div>
                    <p class="officer">Signature</p>
                </td>
                <td valign="bottom" align="right">
                    @if (! empty($document['show_stamp']))
                        @include('certificates.partials.stamp')
                    @endif
                </td>
            </tr>
        </table>
    @else
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 22px;">
            <tr>
                <td>&nbsp;</td>
                <td class="pb-block" valign="top">
                    @include('certificates.partials.punong', ['document' => $document])
                </td>
            </tr>
            <tr>
                <td class="authority" valign="middle">{{ $document['authority_line'] ?? 'By The Authority of the Punong Barangay;' }}</td>
                <td class="pb-block" valign="top">
                    @if (! empty($document['show_stamp']))
                        @include('certificates.partials.stamp')
                    @endif
                </td>
            </tr>
        </table>

        <div>
            @if ($document['officer_line'] ?? true)
                <div class="wet-line">&nbsp;</div>
            @endif
            <p class="officer {{ ! empty($document['bold_issuer']) ? 'bold' : '' }}">{{ $document['fields']['issuer_name'] }}</p>
            <p class="officer">Officer for the day</p>
            <p class="officer">{{ $document['fields']['issuer_position'] }}</p>
        </div>
    @endif

    @if (! empty($document['footer_split']))
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px;">
            <tr>
                <td valign="bottom">
                    @foreach ($document['footer_notes'] ?? [] as $note)
                        <p class="footer-left">{{ $note }}</p>
                    @endforeach
                </td>
                <td valign="bottom" style="text-align: right; font-style: italic; font-size: 10px;">
                    {{ $document['revised_note'] ?? '' }}
                </td>
            </tr>
        </table>
    @else
        @foreach ($document['footer_notes'] ?? [] as $note)
            <p class="footer-left">{{ $note }}</p>
        @endforeach
    @endif
    </td>
    </tr>
    </table>
    </div>
    @endif
@endforeach
</body>
</html>
