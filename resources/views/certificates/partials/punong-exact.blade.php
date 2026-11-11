@if (! empty($inline ?? null))
    <div style="text-align: right;">
        @if (! empty($document['signature_data_uri']))
            <img class="sig-img-exact" src="{{ $document['signature_data_uri'] }}" alt="">
        @else
            <div class="sig-gap">&nbsp;</div>
        @endif
    </div>
@else
    <div class="sig-center">
        @if (! empty($document['signature_data_uri']))
            <img class="sig-img-exact" src="{{ $document['signature_data_uri'] }}" alt="">
        @else
            <div class="sig-gap">&nbsp;</div>
        @endif
        <p class="pb-line">{{ ($document['fields']['punong_barangay_name'] ?? '') !== '' ? $document['fields']['punong_barangay_name'] : ' ' }}</p>
        <p class="pb-role">Punong Barangay</p>
    </div>
@endif
