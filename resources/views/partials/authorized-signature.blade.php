@php
    $signatureContext = $context ?? 'ledger';
    $isPdf = (bool) ($pdf ?? false);
    $shouldShow = $signatureContext === 'receipt' ? show_signature_on_receipt() : show_signature_on_ledger();
    $signatureName = authorized_signature_name();
    $signatureImage = authorized_signature_data_uri();
@endphp

@if($shouldShow && ($signatureName || $signatureImage))
    <div class="{{ $isPdf ? 'pdf-authorized-signature' : 'authorized-signature' }}">
        @if($signatureImage)
            <img src="{{ $signatureImage }}" alt="Digital signature">
        @endif
        <div>Authorized Signature</div>
        @if($signatureName)
            <strong>{{ $signatureName }}</strong>
        @endif
    </div>
@endif
