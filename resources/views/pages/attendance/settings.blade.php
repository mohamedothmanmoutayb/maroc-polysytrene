@extends('layouts.app')

@section('title', 'Paramètres des Présences')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Paramètres des Présences</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('attendance.index') }}">
                                        Présences
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Paramètres
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-cog me-2"></i>Configuration des Présences
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Heure d'arrivée *</label>
                                        <input type="time" class="form-control" name="check_in_time"
                                            value="{{ \Carbon\Carbon::parse($settings->check_in_time)->format('H:i') }}"
                                            required>
                                        <small class="text-muted">Heure normale d'arrivée</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Heure de départ *</label>
                                        <input type="time" class="form-control" name="check_out_time"
                                            value="{{ \Carbon\Carbon::parse($settings->check_out_time)->format('H:i') }}"
                                            required>
                                        <small class="text-muted">Heure normale de départ</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Seuil de retard *</label>
                                        <input type="time" class="form-control" name="late_threshold"
                                            value="{{ \Carbon\Carbon::parse($settings->late_threshold)->format('H:i') }}"
                                            required>
                                        <small class="text-muted">Au-delà de cette heure, c'est un retard</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Heures de travail par jour *</label>
                                        <input type="number" class="form-control" name="work_hours_per_day"
                                            value="{{ $settings->work_hours_per_day }}" step="0.5" min="0"
                                            max="24" required>
                                        <small class="text-muted">Nombre d'heures standard par jour</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">Marquer automatiquement les absents</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="auto_mark_absent"
                                                value="1" id="auto_mark_absent"
                                                {{ $settings->auto_mark_absent ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auto_mark_absent">
                                                Marquer automatiquement les employés sans présence comme absents
                                            </label>
                                        </div>
                                        <small class="text-muted">S'applique à la fin de chaque journée</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Jours ouvrables</h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($days as $day)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="working_days[]"
                                                        value="{{ $day }}" id="working_{{ $day }}"
                                                        {{ in_array($day, $settings->working_days) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="working_{{ $day }}">
                                                        {{ $dayLabels[$day] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Jours de week-end</h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($days as $day)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="weekend_days[]"
                                                        value="{{ $day }}" id="weekend_{{ $day }}"
                                                        {{ in_array($day, $settings->weekend_days) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="weekend_{{ $day }}">
                                                        {{ $dayLabels[$day] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Informations supplémentaires...">{{ $settings->notes }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information:</strong> Les modifications seront appliquées à partir de maintenant.
                                Les données historiques ne seront pas affectées.
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#settingsForm').submit(function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

                $.ajax({
                    url: '{{ route('attendance.settings.update') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = xhr.responseJSON?.message ||
                            'Erreur lors de l\'enregistrement';
                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        function showToast(type, message) {
            var toast = $('<div class="toast align-items-center text-white bg-' +
                (type === 'success' ? 'success' : 'danger') +
                ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                '<div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div>' +
                '</div>');

            $('#toast-container').append(toast);
            var bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();

            setTimeout(function() {
                toast.remove();
            }, 5000);
        }
    </script>
@endpush
