@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger no-print">
        <strong>Please check the form.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('whatsapp_status'))
    <div class="alert alert-{{ session('whatsapp_status.type', 'success') }} alert-dismissible fade show no-print" role="alert">
        {{ session('whatsapp_status.message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('whatsapp_fallback'))
    <div class="alert alert-warning no-print" role="alert">
        <strong>WhatsApp fallback:</strong> {{ session('whatsapp_fallback.message') }}
        <div class="mt-2 d-flex flex-wrap gap-2">
            @if(session('whatsapp_fallback.download_url'))
                <a class="btn btn-sm btn-outline-dark" href="{{ session('whatsapp_fallback.download_url') }}">Download PDF</a>
            @endif
            @if(session('whatsapp_fallback.whatsapp_url'))
                <a class="btn btn-sm btn-success" target="_blank" rel="noopener" href="{{ session('whatsapp_fallback.whatsapp_url') }}">Open WhatsApp Message</a>
            @endif
        </div>
    </div>
@endif
