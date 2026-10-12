<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: "DejaVu Serif", serif; font-size: 12px; color: #111; }
        .center { text-align: center; }
        .header-line { margin: 0; line-height: 1.3; text-align: center; }
        .barangay { font-weight: bold; font-size: 14px; letter-spacing: 0.3px; }
        .title { font-family: "DejaVu Sans", sans-serif; font-weight: bold; font-size: 16px; text-align: center; margin: 18px 0 14px; }
        .salutation { font-weight: bold; margin: 0 0 8px; }
        .body { text-align: justify; text-indent: 36px; margin: 0 0 10px; line-height: 1.45; }
        .bold { font-weight: bold; }
        .logo-cell { width: 78px; height: 78px; vertical-align: middle; text-align: center; }
        .logo-empty { width: 72px; height: 72px; border: 1px solid #999; }
        .logo-img { width: 72px; height: 72px; }
        .pb-name { font-weight: bold; text-decoration: underline; text-transform: uppercase; margin-top: 2px; }
        .sig-space { height: 52px; }
        .sig-img { height: 52px; }
        .wet-line { border-bottom: 1px solid #111; width: 210px; height: 28px; margin-top: 18px; }
        .stamp { border: 1px solid #111; width: 230px; margin: 12px auto 0; padding: 8px 10px; font-size: 10px; text-align: left; }
        .stamp-title { font-weight: bold; text-align: center; margin-bottom: 8px; }
        .stamp-line { margin-top: 6px; }
    </style>
</head>
<body>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell">
                @if (! empty($document['logos']['bagong_pilipinas']))
                    <img class="logo-img" src="{{ $document['logos']['bagong_pilipinas'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
            </td>
            <td class="logo-cell">
                @if (! empty($document['logos']['city_of_baguio']))
                    <img class="logo-img" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
            </td>
            <td class="center">
                <p class="header-line">Republic of the Philippines</p>
                <p class="header-line">{{ $document['header']['city_name'] }}</p>
                <p class="header-line barangay">{{ $document['header']['barangay_name'] }}</p>
                <p class="header-line">Official email address: {{ $document['header']['barangay_email'] }}</p>
                <p class="header-line">Tel. No. {{ $document['header']['barangay_contact_no'] }}</p>
            </td>
            <td class="logo-cell">
                @if (! empty($document['logos']['happy_hallow']))
                    <img class="logo-img" src="{{ $document['logos']['happy_hallow'] }}" alt="">
                @else
                    <div class="logo-empty">&nbsp;</div>
                @endif
            </td>
        </tr>
    </table>

    <p class="title">{{ $document['title'] }}</p>
    <p class="salutation">To Whom It May Concern:</p>

    @foreach ($document['paragraphs'] as $paragraph)
        <p class="body">
            @foreach ($paragraph as $part)
                @if ($part['bold'])
                    <span class="bold">{{ $part['text'] }}</span>
                @else
                    {{ $part['text'] }}
                @endif
            @endforeach
        </p>
    @endforeach

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 16px;">
        <tr>
            <td>&nbsp;</td>
            <td width="250" class="center">
                @if (! empty($document['signature_data_uri']))
                    <img class="sig-img" src="{{ $document['signature_data_uri'] }}" alt="">
                @else
                    <div class="sig-space">&nbsp;</div>
                @endif
                <div class="pb-name">{{ $document['fields']['punong_barangay_name'] }}</div>
                <div>Punong Barangay</div>
                <div class="stamp">
                    <div class="stamp-title">DOCUMENTARY STAMP TAX PAID</div>
                    <div class="stamp-line">GOR SERIAL NUMBER: _______________</div>
                    <div class="stamp-line">DATE: _______________</div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 26px;">
        <div>By The Authority of the Punong Barangay;</div>
        <div class="wet-line">&nbsp;</div>
        <div>{{ $document['fields']['issuer_name'] }}</div>
        <div>Officer for the day</div>
        <div>{{ $document['fields']['issuer_position'] }}</div>
    </div>
</body>
</html>
