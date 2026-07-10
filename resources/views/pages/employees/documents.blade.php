@extends('layouts.app')

@section('title', 'Documents - ' . $employee->full_name)

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">
                            <i class="fas fa-folder-open me-2"></i>Documents de {{ $employee->full_name }}
                        </h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Accueil</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('employees.index') }}">Employés</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('employees.show', $employee->employee_id) }}">
                                        {{ $employee->full_name }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Documents</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upload Form -->
            <div class="col-md-4 mb-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-upload me-2"></i>Uploader des documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            <div id="document-uploads">
                                <div class="document-item border rounded p-3 mb-3">
                                    <div class="mb-3">
                                        <label class="form-label">Document</label>
                                        <input type="file" class="form-control" name="documents[]" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Catégorie</label>
                                        <select class="form-control" name="categories[]" required>
                                            <option value="cin">CIN</option>
                                            <option value="cnss">CNSS</option>
                                            <option value="contract">Contrat</option>
                                            <option value="diploma">Diplôme</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="descriptions[]" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_confidentiel[]"
                                                value="1">
                                            <label class="form-check-label">Document confidentiel</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-document">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" id="addDocument">
                                    <i class="fas fa-plus me-2"></i>Ajouter un document
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Uploader les documents
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Liste des documents
                        </h5>
                        <div>
                            <span class="badge bg-primary" id="total-documents">0</span> documents
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="documents-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom du document</th>
                                        <th>Catégorie</th>
                                        <th>Taille</th>
                                        <th>Date d'upload</th>
                                        <th>Confidentiel</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-file me-2"></i>
                        <span id="preview-title"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="preview-container" class="text-center p-3">
                        <!-- PDF Preview -->
                        <div id="pdf-preview" class="d-none" style="min-height: 500px;">
                            <canvas id="pdf-canvas" style="width: 100%; height: auto;"></canvas>
                            <div class="mt-3" id="pdf-controls">
                                <button class="btn btn-sm btn-primary" id="prev-page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="page-info" class="mx-3">Page <span id="page-num">1</span> / <span
                                        id="page-count">1</span></span>
                                <button class="btn btn-sm btn-primary" id="next-page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Image Preview -->
                        <div id="image-preview" class="d-none">
                            <img id="preview-image" class="img-fluid" style="max-height: 600px;">
                        </div>

                        <!-- Other File Preview -->
                        <div id="other-preview" class="d-none py-5">
                            <i class="fas fa-file fa-5x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aperçu non disponible pour ce type de fichier</p>
                            <button class="btn btn-primary" id="download-btn">
                                <i class="fas fa-download me-2"></i>Télécharger
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Fermer
                    </button>
                    <a href="#" id="modal-download-btn" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>Télécharger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <style>
        .category-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        .document-preview {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .document-preview:hover {
            transform: scale(1.05);
        }

        #pdf-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        #pdf-controls {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize PDF.js
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            // Variables for PDF navigation
            var pdfDoc = null;
            var pageNum = 1;
            var pageRendering = false;
            var pageNumPending = null;
            var canvas = document.getElementById('pdf-canvas');
            var ctx = canvas.getContext('2d');

            // Initialize DataTable
            var table = $('#documents-table').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('employees.documents.index', $employee->employee_id) }}",
                    dataSrc: function(json) {
                        // Update total documents count
                        $('#total-documents').text(json.recordsTotal || 0);
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'document_name',
                        name: 'document_name',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return '<i class="fas ' + getFileIcon(row.mime_type) +
                                    ' me-2 text-primary"></i>' +
                                    '<span class="document-name">' + data + '</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'category',
                        name: 'category',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var badges = {
                                    'cin': 'bg-info',
                                    'cnss': 'bg-success',
                                    'contract': 'bg-primary',
                                    'diploma': 'bg-warning',
                                    'other': 'bg-secondary'
                                };
                                var badgeClass = badges[data] || 'bg-secondary';
                                var labels = {
                                    'cin': 'CIN',
                                    'cnss': 'CNSS',
                                    'contract': 'Contrat',
                                    'diploma': 'Diplôme',
                                    'other': 'Autre'
                                };
                                return '<span class="badge ' + badgeClass + ' category-badge">' +
                                    (labels[data] || data) + '</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'file_size',
                        name: 'file_size',
                        className: 'text-center'
                    },
                    {
                        data: 'uploaded_at',
                        name: 'uploaded_at',
                        className: 'text-center'
                    },
                    {
                        data: 'confidentiel',
                        name: 'confidentiel',
                        className: 'text-center',
                        orderable: false,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return data ?
                                    '<span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Confidentiel</span>' :
                                    '<span class="badge bg-success"><i class="fas fa-lock-open me-1"></i>Public</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        width: '10%'
                    }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [4, 'desc']
                ],
                drawCallback: function() {
                    // Update total count after each draw
                    $('#total-documents').text(this.api().data().length);
                }
            });

            // Helper function to get file icon based on mime type
            function getFileIcon(mimeType) {
                if (!mimeType) return 'fa-file';

                if (mimeType.includes('pdf')) {
                    return 'fa-file-pdf text-danger';
                } else if (mimeType.includes('image')) {
                    return 'fa-file-image text-success';
                } else if (mimeType.includes('word') || mimeType.includes('document')) {
                    return 'fa-file-word text-primary';
                } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
                    return 'fa-file-excel text-success';
                } else if (mimeType.includes('text')) {
                    return 'fa-file-alt text-secondary';
                }
                return 'fa-file';
            }

            // Add more document fields
            $('#addDocument').click(function() {
                var html = `
                    <div class="document-item border rounded p-3 mb-3">
                        <div class="mb-3">
                            <label class="form-label">Document</label>
                            <input type="file" class="form-control" name="documents[]" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select class="form-control" name="categories[]" required>
                                <option value="cin">CIN</option>
                                <option value="cnss">CNSS</option>
                                <option value="contract">Contrat</option>
                                <option value="diploma">Diplôme</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="descriptions[]" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_confidentiel[]" value="1">
                                <label class="form-check-label">Document confidentiel</label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-document">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </div>
                `;
                $('#document-uploads').append(html);
            });

            // Remove document field
            $(document).on('click', '.remove-document', function() {
                $(this).closest('.document-item').remove();
            });

            // Upload form submit
            $('#uploadForm').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i>Upload...');

                $.ajax({
                    url: "{{ route('employees.documents.upload', $employee->employee_id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#uploadForm')[0].reset();
                            $('#document-uploads').html(`
                                <div class="document-item border rounded p-3 mb-3">
                                    <div class="mb-3">
                                        <label class="form-label">Document</label>
                                        <input type="file" class="form-control" name="documents[]" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Catégorie</label>
                                        <select class="form-control" name="categories[]" required>
                                            <option value="cin">CIN</option>
                                            <option value="cnss">CNSS</option>
                                            <option value="contract">Contrat</option>
                                            <option value="diploma">Diplôme</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="descriptions[]" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_confidentiel[]" value="1">
                                            <label class="form-check-label">Document confidentiel</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-document">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            `);
                            table.ajax.reload();
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message ||
                            'Une erreur est survenue';
                        if (xhr.responseJSON?.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join(
                                '\n');
                        }
                        showToast('error', errorMessage);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Delete document
            $(document).on('click', '.delete-doc', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var docName = $(this).data('name');

                if (confirm('Êtes-vous sûr de vouloir supprimer le document "' + docName + '" ?')) {
                    $.ajax({
                        url: "{{ route('employees.documents.destroy', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('success', response.message);
                                table.ajax.reload();
                            } else {
                                showToast('error', response.message);
                            }
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message ||
                                'Erreur lors de la suppression');
                        }
                    });
                }
            });

            // Preview document
            $(document).on('click', '.preview-doc', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var docName = $(this).data('name');

                // Reset preview
                $('#preview-title').text(docName);
                $('#modal-download-btn').attr('href', "{{ route('employees.documents.download', '') }}/" +
                    id);
                $('#pdf-preview, #image-preview, #other-preview').addClass('d-none');

                // Show loading
                $('#preview-container').append(
                    '<div class="text-center p-5" id="preview-loading"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-3">Chargement...</p></div>'
                    );

                $.ajax({
                    url: "{{ route('employees.documents.preview', '') }}/" + id,
                    type: 'GET',
                    success: function(response) {
                        $('#preview-loading').remove();

                        if (response.success) {
                            if (response.type === 'application/pdf') {
                                // Show PDF preview
                                $('#pdf-preview').removeClass('d-none');
                                renderPDF(response.content);
                            } else if (response.type.startsWith('image/')) {
                                // Show image preview
                                $('#image-preview').removeClass('d-none');
                                $('#preview-image').attr('src', 'data:' + response.type +
                                    ';base64,' + response.content);
                            } else {
                                // Show other file type
                                $('#other-preview').removeClass('d-none');
                            }

                            $('#previewModal').modal('show');
                        }
                    },
                    error: function() {
                        $('#preview-loading').remove();
                        showToast('error', 'Erreur lors du chargement du document');
                    }
                });
            });

            // Render PDF function
            function renderPDF(base64Data) {
                var loadingTask = pdfjsLib.getDocument({
                    data: atob(base64Data)
                });

                loadingTask.promise.then(function(pdf) {
                    pdfDoc = pdf;
                    document.getElementById('page-count').textContent = pdfDoc.numPages;

                    // Initial page rendering
                    renderPage(pageNum);

                    // Show page navigation
                    if (pdfDoc.numPages > 1) {
                        $('#pdf-controls').show();
                    } else {
                        $('#pdf-controls').hide();
                    }
                });
            }

            // Render specific page
            function renderPage(num) {
                pageRendering = true;

                pdfDoc.getPage(num).then(function(page) {
                    var viewport = page.getViewport({
                        scale: 1.5
                    });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    var renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };

                    var renderTask = page.render(renderContext);

                    renderTask.promise.then(function() {
                        pageRendering = false;
                        document.getElementById('page-num').textContent = num;

                        if (pageNumPending !== null) {
                            renderPage(pageNumPending);
                            pageNumPending = null;
                        }
                    });
                });
            }

            // Previous page
            $('#prev-page').click(function() {
                if (pageNum <= 1) return;
                pageNum--;
                renderPage(pageNum);
            });

            // Next page
            $('#next-page').click(function() {
                if (pageNum >= pdfDoc.numPages) return;
                pageNum++;
                renderPage(pageNum);
            });

            // Queue rendering of next page
            function queueRenderPage(num) {
                if (pageRendering) {
                    pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            // Download from modal
            $('#modal-download-btn').click(function(e) {
                e.preventDefault();
                var downloadUrl = $(this).attr('href');
                window.location.href = downloadUrl;
            });

            // Clean up PDF on modal close
            $('#previewModal').on('hidden.bs.modal', function() {
                pdfDoc = null;
                pageNum = 1;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            function showToast(type, message) {
                var toastId = 'toast-' + Date.now();
                var toast = $('<div id="' + toastId + '" class="toast align-items-center text-white bg-' +
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
                    $('#' + toastId).remove();
                }, 5000);
            }
        });
    </script>
@endpush
