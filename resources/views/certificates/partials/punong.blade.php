@if (! empty($document['signature_data_uri']))
    <img class="sig-img" src="{{ $document['signature_data_uri'] }}" alt="">
@else
    <div class="sig-space">&nbsp;</div>
@endif
@if (! empty($inline ?? null))
    <p class="officer">{{ $document['fields']['punong_barangay_name'] }}, Punong Barangay</p>
@else
    <p class="pb-name">{{ $document['fields']['punong_barangay_name'] !== '' ? $document['fields']['punong_barangay_name'] : ' ' }}</p>
    <p class="pb-title">Punong Barangay</p>
@endif
