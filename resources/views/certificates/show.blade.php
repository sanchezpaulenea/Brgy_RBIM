<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }}</title>
    <style>
        @page { margin: 36px 46px 40px; }
        body { font-family: "DejaVu Serif", serif; font-size: 12.5px; color: #111; line-height: 1.35; }
        .center { text-align: center; }
        .header-line { margin: 0; line-height: 1.25; text-align: center; }
        .republic { font-weight: bold; font-size: 13px; }
        .barangay { font-weight: bold; font-size: 14px; }
        .contact { text-decoration: underline; font-size: 11.5px; }
        .title { font-family: "DejaVu Serif", serif; font-weight: bold; font-size: 18px; text-align: center; letter-spacing: 0.4px; margin: 22px 0 16px; }
        .salutation { font-weight: bold; margin: 0 0 10px; }
        .body { text-align: justify; text-indent: 42px; margin: 0 0 12px; line-height: 1.55; }
        .bold { font-weight: bold; }
        .letterhead { position: relative; height: 92px; text-align: center; }
        .logo-abs { position: absolute; top: 0; width: 78px; height: 78px; }
        .logo-left { left: 0; }
        .logo-mid { left: 86px; }
        .logo-right { right: 72px; }
        .logo-empty { width: 78px; height: 78px; border: 1px solid #bbb; }
        .logo-img { width: 78px; height: 78px; }
        .letterhead-copy { padding-top: 4px; }
        .pb-block { width: 250px; text-align: center; }
        .pb-name { font-weight: bold; text-decoration: underline; text-transform: uppercase; font-size: 12px; margin: 0 auto; min-width: 170px; display: inline-block; }
        .pb-title { margin: 1px 0 0; font-size: 12px; }
        .sig-space { height: 46px; }
        .sig-img { height: 46px; }
        .authority { margin-top: 8px; }
        .wet-line { border-bottom: 1px solid #111; width: 190px; height: 36px; margin-top: 8px; }
        .officer { margin: 2px 0 0; }
        .stamp-outer { border: 1.5px solid #9c3b32; border-radius: 8px; padding: 2px; width: 250px; margin-top: 10px; color: #9c3b32; }
        .stamp-inner { border: 1px solid #9c3b32; border-radius: 6px; padding: 6px 8px 8px; font-size: 8px; line-height: 1.2; }
        .stamp-title { font-weight: bold; text-align: center; letter-spacing: 0.2px; margin: 0 0 6px; }
        .stamp-label { font-size: 7.5px; }
        .stamp-blank { border-bottom: 1px solid #9c3b32; height: 12px; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="letterhead">
        @if (! empty($document['logos']['bagong_pilipinas']))
            <img class="logo-abs logo-left" src="{{ $document['logos']['bagong_pilipinas'] }}" alt="">
        @else
            <div class="logo-abs logo-left logo-empty">&nbsp;</div>
        @endif
        @if (! empty($document['logos']['city_of_baguio']))
            <img class="logo-abs logo-mid" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
        @else
            <div class="logo-abs logo-mid logo-empty">&nbsp;</div>
        @endif
        @if (! empty($document['logos']['happy_hallow']))
            <img class="logo-abs logo-right" src="{{ $document['logos']['happy_hallow'] }}" alt="">
        @else
            <div class="logo-abs logo-right logo-empty">&nbsp;</div>
        @endif
        <div class="letterhead-copy">
            <p class="header-line republic">Republic of the Philippines</p>
            <p class="header-line">{{ $document['header']['city_name'] }}</p>
            <p class="header-line barangay">{{ $document['header']['barangay_name'] }}</p>
            <p class="header-line contact">Official email address: {{ $document['header']['barangay_email'] }}</p>
            <p class="header-line contact">Tel. No. {{ $document['header']['barangay_contact_no'] }}</p>
        </div>
    </div>

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

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 28px;">
        <tr>
            <td>&nbsp;</td>
            <td class="pb-block" valign="top">
                @if (! empty($document['signature_data_uri']))
                    <img class="sig-img" src="{{ $document['signature_data_uri'] }}" alt="">
                @else
                    <div class="sig-space">&nbsp;</div>
                @endif
                <p class="pb-name">{{ $document['fields']['punong_barangay_name'] !== '' ? $document['fields']['punong_barangay_name'] : ' ' }}</p>
                <p class="pb-title">Punong Barangay</p>
            </td>
        </tr>
        <tr>
            <td class="authority" valign="middle">By The Authority of the Punong Barangay;</td>
            <td class="pb-block" valign="top">
                <div class="stamp-outer">
                    <div class="stamp-inner">
                        <p class="stamp-title">DOCUMENTARY STAMP TAX PAID</p>
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="58%" valign="top" style="padding-right: 8px;">
                                    <div class="stamp-label">GOR SERIAL NUMBER:</div>
                                    <div class="stamp-blank">&nbsp;</div>
                                </td>
                                <td width="42%" valign="top" style="border-left: 1px solid #9c3b32; padding-left: 8px;">
                                    <div class="stamp-label">DATE:</div>
                                    <div class="stamp-blank">&nbsp;</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div>
        <div class="wet-line">&nbsp;</div>
        <p class="officer">{{ $document['fields']['issuer_name'] }}</p>
        <p class="officer">Officer for the day</p>
        <p class="officer">{{ $document['fields']['issuer_position'] }}</p>
    </div>
</body>
</html>
