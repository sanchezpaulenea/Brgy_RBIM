<div class="exact layout-{{ $document['layout'] }} {{ $sheetIndex > 0 ? 'sheet-break' : '' }}">
    @if (! empty($document['watermark']))
        <div class="watermark">{{ $document['watermark'] }}</div>
    @endif

    <div class="exact-mast">
        <table class="mast-table" cellspacing="0" cellpadding="0">
            <tr>
                <td class="mast-left" valign="middle">
                    @if (($document['letterhead']['variant'] ?? 'standard') === 'punong')
                        @if (! empty($document['logos']['city_of_baguio']))
                            <img class="logo-city" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
                        @endif
                    @else
                        @if (! empty($document['logos']['bagong_pilipinas']))
                            <img class="logo-bp" src="{{ $document['logos']['bagong_pilipinas'] }}" alt="">
                        @endif
                        @if (! empty($document['logos']['city_of_baguio']))
                            <img class="logo-city" src="{{ $document['logos']['city_of_baguio'] }}" alt="">
                        @endif
                    @endif
                </td>
                <td class="mast-center" valign="middle">
                    @foreach ($document['letterhead']['lines'] ?? [] as $line)
                        <p class="mast-line {{ ! empty($line['bold']) ? 'strong' : '' }} {{ ! empty($line['underline']) ? 'ruled' : '' }}">{{ $line['text'] }}</p>
                    @endforeach
                </td>
                <td class="mast-right" valign="middle">
                    @if (! empty($document['logos']['happy_hallow']))
                        <img class="logo-brgy" src="{{ $document['logos']['happy_hallow'] }}" alt="">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="exact-copy">
        @if (! empty($document['office']))
            <p class="office">{{ $document['office'] }}</p>
        @endif
        @if (! empty($document['revised_note']) && ($document['layout'] ?? '') === 'jobseeker')
            <p class="revised-top">{{ $document['revised_note'] }}</p>
        @endif
        @if (! empty($document['show_certificate_number']))
            <p class="cert-no">Barangay Certificate Number: {{ $document['certificate_number'] ?? '' }}</p>
        @endif

        <p class="doc-title">{{ $document['title'] }}</p>
        @if (! empty($document['subtitle']))
            <p class="doc-subtitle">{{ $document['subtitle'] }}</p>
        @endif
        @if (! empty($document['salutation']))
            <p class="salutation">{{ $document['salutation'] }}</p>
        @endif

        @php $itemStarted = false; @endphp
        @foreach ($document['paragraphs'] as $paragraph)
            @php
                $parts = $paragraph['parts'] ?? $paragraph;
                $isItem = ! empty($paragraph['item']);
                $isLead = ! $isItem && ! $itemStarted;
                if ($isItem) {
                    $itemStarted = true;
                }
            @endphp
            <p class="body {{ $isItem ? 'item' : '' }} {{ $isLead ? 'lead' : '' }}">@foreach ($parts as $part)<span class="{{ trim((($part['bold'] ?? false) ? 'bold ' : '').(($part['italic'] ?? false) ? 'italic ' : '').(($part['underline'] ?? false) ? 'underline' : '')) }}">{{ $part['text'] }}</span>@endforeach</p>
        @endforeach

        @php $style = $document['signature_style'] ?? 'authority'; @endphp

        @if ($style === 'punong')
            <table class="good-sign" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td>&nbsp;</td>
                    <td style="width: 230pt;" valign="top" align="center">
                        @include('certificates.partials.punong-exact')
                    </td>
                </tr>
            </table>
        @elseif ($style === 'approved')
            <p class="approved">Approved by:</p>
            <div class="sig-center">
                @include('certificates.partials.punong-exact')
            </div>
            <p class="witness-label">Witnessed by:</p>
            <p class="witness-name">{{ $document['fields']['issuer_name'] }}</p>
            <p class="witness-role">{{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}</p>
            <p class="witness-date">Date: <span class="bold">{{ $document['fields']['witness_date_tight'] ?? $document['fields']['witness_date'] }}</span></p>
            <table class="form-footer" cellspacing="0" cellpadding="0">
                <tr>
                    <td>&nbsp;</td>
                    <td class="form-revised">{{ $document['revised_note'] ?? '' }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 14pt;">
                        @foreach ($document['footer_notes'] ?? [] as $note)
                            <p class="form-note">{{ $note }}</p>
                        @endforeach
                    </td>
                </tr>
            </table>
        @elseif ($style === 'oath')
            <div style="margin-top: 8pt;">
                @include('certificates.partials.punong-exact', ['inline' => true])
            </div>
            <p class="oath-sign">{{ $document['fields']['punong_barangay_name'] }}, Punong Barangay</p>
            <p class="oath-seeker">{{ mb_strtoupper((string) ($document['fields']['resident_name'] ?? ''), 'UTF-8') }}, <span class="oath-seeker-role">FIRST TIME JOB SEEKER</span></p>
            <p class="oath-witness">Witnessed by:{{ mb_strtoupper((string) ($document['fields']['issuer_name'] ?? ''), 'UTF-8') }}, {{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}/Officer of the day</p>
            <p class="oath-date">DATE: {{ $document['fields']['witness_date'] }}</p>
            <div class="oath-notes">
                @foreach ($document['footer_notes'] ?? [] as $note)
                    <p class="form-note">{{ $note }}</p>
                @endforeach
            </div>
        @elseif ($style === 'income')
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 14pt;">
                <tr>
                    <td>&nbsp;</td>
                    <td style="width: 180pt;" valign="top" align="center">
                        @include('certificates.partials.punong-exact')
                    </td>
                </tr>
                <tr>
                    <td valign="top" style="padding-top: 8pt;">
                        <p class="income-authority">{{ $document['authority_line'] }}</p>
                        <p class="income-name">{{ ! empty($document['issuer_upper']) ? mb_strtoupper((string) ($document['fields']['issuer_name'] ?? ''), 'UTF-8') : $document['fields']['issuer_name'] }}</p>
                        <p class="income-role">{{ $document['officer_label'] ?? 'Officer for the day' }}</p>
                        <p class="income-role">{{ $document['fields']['issuer_position'] !== '' ? $document['fields']['issuer_position'] : 'Barangay Kagawad' }}</p>
                    </td>
                    <td style="width: 180pt;" valign="top" align="center">
                        @if (! empty($document['show_stamp']))
                            @include('certificates.partials.stamp')
                        @endif
                    </td>
                </tr>
            </table>
        @endif
    </div>
</div>
