@extends('layouts.app')

@section('title', 'Documents Client')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Documents du Client</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('clients.index') }}">
                                        Clients
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('clients.show', $client->client_id) }}">
                                        {{ $client->display_name }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Documents
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-folder me-2"></i>Documents : {{ $client->display_name }}
                        </h5>
                        <div>
                            <a href="{{ route('clients.show', $client->client_id) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Upload Document Button -->
                        <div class="mb-4">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-1"></i> Ajouter un Document
                            </button>
                        </div>

                        <!-- Documents List -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type de Document</th>
                                        <th>Nom du Document</th>
                                        <th>Taille</th>
                                        <th>Date d'Upload</th>
                                        <th>Uploadé par</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->documents as $document)
                                        <tr>
                                            <td>{{ $document->document_type_label }}</td>
                                            <td>{{ $document->document_name }}</td>
                                            <td>{{ number_format($document->file_size / 1024, 2, ',', '.') }} KB</td>
                                            <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $document->uploader->username ?? 'N/A' }}</td>
                                            <td>{{ $document->notes ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ Storage::url($document->file_path) }}" target="_blank"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ Storage::url($document->file_path) }}"
                                                        download="{{ $document->file_name }}"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-document"
                                                        data-id="{{ $document->document_id }}"
                                                        data-name="{{ $document->document_name }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-folder-open me-2"></i>
                                                Aucun document trouvé
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Uploader un Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Type de Document *</label>
                            <select class="form-control" id="document_type" name="document_type" required>
                                <option value="">Sélectionner</option>
                                @if ($client->person_type == 'physique')
                                    <option value="cin">CIN</option>
                                @else
                                    <option value="ice">ICE</option>
                                    <option value="rc">RC</option>
                                    <option value="patente">Patente</option>
                                @endif
                                <option value="contrat">Contrat</option>
                                <option value="facture">Facture</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="document_name" class="form-label">Nom du Document</label>
                            <input type="text" class="form-control" id="document_name" name="document_name">
                            <small class="form-text text-muted">Laisser vide pour utiliser le nom du fichier</small>
                        </div>
                        <div class="mb-3">
                            <label for="document" class="form-label">Fichier *</label>
                            <input type="file" class="form-control" id="document" name="document" required>
                            <small class="form-text text-muted">Taille max: 5MB. Formats acceptés: PDF, DOC, DOCX, JPG,
                                PNG</small>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Uploader</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Upload Form
            $('#uploadForm').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('clients.upload-document', $client->client_id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#uploadModal').modal('hide');
                            showToast('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '\n';
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                        }

                        showToast('error', errorMessage);
                    }
                });
            });

            // Delete Document
            $(document).on('click', '.delete-document', function() {
                var documentId = $(this).data('id');
                var documentName = $(this).data('name');

                if (confirm('Êtes-vous sûr de vouloir supprimer le document "' + documentName + '" ?')) {
                    $.ajax({
                        url: "{{ route('clients.delete-document', [$client->client_id, '']) }}/" +
                            documentId,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue';
                            showToast('error', errorMessage);
                        }
                    });
                }
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
        });
    </script>
@endpush
