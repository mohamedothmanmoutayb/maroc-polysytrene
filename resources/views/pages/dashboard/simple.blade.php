@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
            <div class="card shadow-sm border-0" style="max-width: 480px; width: 100%;">
                <div class="card-body text-center py-5">
                    <iconify-icon icon="solar:sun-2-bold-duotone" style="font-size: 3rem; color: #fbbf24;"></iconify-icon>
                    <h3 class="mt-3 mb-1">Bonjour, {{ $displayName }} 👋</h3>
                    <p class="text-muted mb-0">{{ $roleLabel }}</p>
                    <p class="text-muted">{{ now()->translatedFormat('l d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
