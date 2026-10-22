<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }}</title>
    <style>
        @page { margin: 26px 40px 24px; }
        body { font-family: "DejaVu Serif", serif; font-size: 12px; color: #111; line-height: 1.3; }
        .center { text-align: center; }
        .header-line { margin: 0; line-height: 1.15; text-align: center; }
        .republic { font-weight: bold; font-size: 11px; }
        .barangay { font-weight: bold; font-size: 12px; }
        .contact { text-decoration: underline; font-size: 10px; }
        .title { font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 15px; text-align: center; letter-spacing: 0.3px; margin: 12px 0 6px; }
        .title-long { font-size: 12px; margin: 4px 0; }
        .subtitle, .office, .meta { text-align: center; margin: 0; line-height: 1.2; }
        .office { font-weight: bold; font-size: 11px; margin-top: 4px; }
        .subtitle { font-weight: bold; font-size: 11px; margin-bottom: 4px; }
        .meta { font-size: 10px; }
        .salutation { font-weight: bold; margin: 4px 0 6px; }
        .body { text-align: justify; text-indent: 32px; margin: 0 0 7px; line-height: 1.35; }
        .dense { font-size: 10.5px; }
        .dense .body { text-indent: 18px; margin: 0 0 3px; line-height: 1.25; }
        .children { width: 100%; border-collapse: collapse; margin: 4px 0 6px; font-size: 10px; }
        .children th, .children td { border: 1px solid #111; padding: 2px 4px; }
        .children th { text-align: center; }
        .footer-note { text-align: center; font-weight: bold; margin: 2px 0 0; font-size: 10px; }
        .sign-label { margin: 8px 0 0; font-weight: bold; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .underline { text-decoration: underline; }
        .flush { text-indent: 0; margin-bottom: 2px; }
        .meta-right { text-align: right; font-style: italic; }
        .meta-key { font-style: italic; text-decoration: underline; }
        .sheet-break { page-break-before: always; }
        .footer-left { text-align: left; font-weight: bold; margin: 2px 0 0; font-size: 10px; }
        .letterhead { width: 100%; border-collapse: collapse; margin: 0 0 4px; }
        .logo-cell { width: 118px; vertical-align: top; }
        .logo-cell-right { width: 70px; text-align: right; vertical-align: top; }
        .logo-img { width: 52px; height: 52px; }
        .logo-gap { width: 6px; }
        .logo-empty { width: 52px; height: 52px; border: 1px solid #bbb; }
        .letterhead-copy { text-align: center; vertical-align: top; }
        .pb-block { width: 230px; text-align: center; }
        .pb-name { font-weight: bold; text-decoration: underline; text-transform: uppercase; font-size: 11px; margin: 0 auto; min-width: 150px; display: inline-block; }
        .pb-title { margin: 1px 0 0; font-size: 11px; }
        .sig-space { height: 34px; }
        .sig-img { height: 34px; }
        .authority { margin-top: 4px; }
        .wet-line { border-bottom: 1px solid #111; width: 180px; height: 26px; margin-top: 4px; }
        .officer { margin: 1px 0 0; }
        .stamp-outer { border: 1.5px solid #9c3b32; border-radius: 8px; padding: 2px; width: 230px; margin-top: 4px; color: #9c3b32; }
        .stamp-inner { border: 1px solid #9c3b32; border-radius: 6px; padding: 6px 8px 8px; font-size: 8px; line-height: 1.2; }
        .stamp-title { font-weight: bold; text-align: center; letter-spacing: 0.2px; margin: 0 0 6px; }
        .stamp-label { font-size: 7.5px; }
        .stamp-blank { border-bottom: 1px solid #9c3b32; height: 12px; margin-top: 2px; }
    </style>
</head>
<body>
@php $sheets = $document['pages'] ?? [$document]; @endphp
@foreach ($sheets as $sheetIndex => $document)
    <div class="{{ $sheetIndex > 0 ? 'sheet-break' : '' }} {{ ! empty($document['dense']) ? 'dense' : '' }}">
    <table class="letterhead" cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell">
                @if (! empty($document['logos']['bagong_pilipinas']))
                    <img class="logo-img" src="{{ $document['logos']['bagong_pilipinas'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
                <span class="logo-gap">&nbsp;</span>
                @if (! empty($document['logos']['city_of_baguio']))
                    <img class="logo-img" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
            </td>
            <td class="letterhead-copy">
                <p class="header-line republic">Republic of the Philippines</p>
                <p class="header-line">{{ $document['header']['city_name'] }}</p>
                <p class="header-line barangay">{{ $document['header']['barangay_name'] }}</p>
                <p class="header-line contact">Official email address: {{ $document['header']['barangay_email'] }}</p>
                <p class="header-line contact">Tel. No. {{ $document['header']['barangay_contact_no'] }}</p>
            </td>
            <td class="logo-cell-right">
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

    <p class="title {{ strlen((string) $document['title']) > 40 ? 'title-long' : '' }}">{{ $document['title'] }}</p>
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
                    {{ $part['text'] }}
                @endif
            @endforeach
        </p>
    @endforeach
    @if (($document['children_after'] ?? null) === count($document['paragraphs']))
        @include('certificates.partials.children', ['children' => $document['children'] ?? []])
    @endif

    @php
        $style = $document['signature_style'] ?? 'authority';
    @endphp

    @if ($style === 'punong')
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 14px;">
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
        <div class="pb-block" style="margin-left: auto;">
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
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 8px;">
            <tr>
                <td>&nbsp;</td>
                <td class="pb-block" valign="top">
                    @include('certificates.partials.punong', ['document' => $document])
                    @if (! empty($document['show_stamp']))
                        @include('certificates.partials.stamp')
                    @endif
                </td>
            </tr>
        </table>
    @elseif ($style === 'affidavit')
        <div style="text-align: right;">
            @if (! empty($document['signature_data_uri']))
                <img class="sig-img" src="{{ $document['signature_data_uri'] }}" alt="">
            @endif
            <p class="officer">Barangay Captain: <span class="bold">{{ $document['fields']['punong_barangay_name'] }}</span></p>
        </div>
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 8px;">
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
        <p class="officer" style="margin-top: 8px;">Name: {{ $document['affidavit']['kagawad_name'] ?? '' }}</p>
        <p class="officer">Kagawad on Social Services</p>
        <div class="wet-line">&nbsp;</div>
        <p class="officer">Signature</p>
        @if (! empty($document['show_stamp']))
            <div style="text-align: right;">
                @include('certificates.partials.stamp')
            </div>
        @endif
    @else
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 14px;">
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
            <div class="wet-line">&nbsp;</div>
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
    </div>
@endforeach
</body>
</html>
