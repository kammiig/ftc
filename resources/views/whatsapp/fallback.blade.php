@extends('layouts.app')

@section('title', $title)
@section('subtitle', ($result['download_url'] ?? null) ? 'Download the PDF, then attach it manually in WhatsApp' : 'Open WhatsApp with the prepared message')

@section('content')
<div class="card" style="max-width: 860px">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3 mb-3">
            <span class="metric-icon"><i data-lucide="message-circle"></i></span>
            <div>
                <h2 class="h5 mb-1">WhatsApp Web message ready</h2>
                <p class="text-muted mb-0">
                    @if($result['download_url'] ?? null)
                        Please attach the downloaded PDF manually in WhatsApp.
                    @else
                        Open WhatsApp and send the prepared message.
                    @endif
                </p>
            </div>
        </div>

        <div class="alert alert-info">
            {{ $result['message'] ?? 'WhatsApp message is ready. Please attach the downloaded PDF manually.' }}
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            @if($result['download_url'] ?? null)
                <a class="btn btn-outline-primary" href="{{ $result['download_url'] }}"><i data-lucide="file-down"></i> Download PDF</a>
            @endif
            @if($result['whatsapp_url'] ?? null)
                <a class="btn btn-success" href="{{ $result['whatsapp_url'] }}" target="_blank" rel="noopener"><i data-lucide="send"></i> Open WhatsApp</a>
            @endif
            <a class="btn btn-outline-secondary" href="{{ url()->previous() }}"><i data-lucide="arrow-left"></i> Back</a>
        </div>

        <div class="mb-2 text-muted small">Prepared message</div>
        <textarea class="form-control" rows="10" readonly>{{ $result['prepared_message'] ?? '' }}</textarea>
    </div>
</div>
@endsection
