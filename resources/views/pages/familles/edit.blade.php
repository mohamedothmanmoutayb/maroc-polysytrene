@extends('layouts.app')

@section('title', 'Modifier la Famille')

@section('content')
    <div class="container-fluid">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Modifier la Famille</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('familles.index') }}">
                                        Familles
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Modifier {{ $famille->famille_name }}
                                    </span>
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
                    <div class="card-header card-header-custom">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-edit me-2"></i>Modifier la Famille
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="familleForm" action="{{ route('familles.update', $famille->famille_id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="apply_prices_to_products" value="1">

                            <div class="row">
                                <!-- Basic Information Column -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-layer-group me-2"></i>Informations Famille
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group mb-3">
                                                <label for="famille_code" class="form-label">Code Famille *</label>
                                                <input type="text" class="form-control" id="famille_code"
                                                    name="famille_code" value="{{ $famille->famille_code }}" required>
                                                <small class="form-text text-muted">Code unique pour identifier la
                                                    famille</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="famille_name" class="form-label">Nom de la Famille *</label>
                                                <input type="text" class="form-control" id="famille_name"
                                                    name="famille_name" value="{{ $famille->famille_name }}" required>
                                                <small class="form-text text-muted">Nom d'affichage de la famille</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3">{{ $famille->description }}</textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_active"
                                                        name="is_active" value="1"
                                                        {{ $famille->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        Famille active
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing Column -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-tags me-2"></i>Tarification (Prix par m³)
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Ces prix sont exprimés en <strong>DH par m³</strong>. Pour chaque produit
                                                associé,
                                                le prix final sera calculé comme : <strong>Prix unitaire × Volume du produit
                                                    (m³)</strong>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_client" class="form-label">Prix Client (DH/m³)
                                                        *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control price-input"
                                                            id="prix_client" name="prix_client" min="0"
                                                            step="0.01" value="{{ $famille->prix_client }}" required>
                                                        <span class="input-group-text">DH/m³</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_grossiste" class="form-label">Prix Grossiste (DH/m³)
                                                        *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control price-input"
                                                            id="prix_grossiste" name="prix_grossiste" min="0"
                                                            step="0.01" value="{{ $famille->prix_grossiste }}"
                                                            required>
                                                        <span class="input-group-text">DH/m³</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_commercial" class="form-label">Prix Commercial
                                                        (DH/m³) *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control price-input"
                                                            id="prix_commercial" name="prix_commercial" min="0"
                                                            step="0.01" value="{{ $famille->prix_commercial }}"
                                                            required>
                                                        <span class="input-group-text">DH/m³</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="prix_special" class="form-label">Prix Spécial (DH/m³)
                                                        *</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control price-input"
                                                            id="prix_special" name="prix_special" min="0"
                                                            step="0.01" value="{{ $famille->prix_special }}" required>
                                                        <span class="input-group-text">DH/m³</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="prix_revient" class="form-label">Prix de Revient (DH)</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="prix_revient"
                                                        name="prix_revient" min="0" step="0.01"
                                                        value="{{ $famille->prix_revient }}">
                                                    <span class="input-group-text">DH</span>
                                                </div>
                                                <small class="form-text text-muted">Prix de revient total par unité de
                                                    produit</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden inputs to keep existing products --}}
                            @foreach($famille->products as $product)
                                <input type="hidden" name="associated_products[]" value="{{ $product->product_id }}">
                                <input type="hidden" name="quantity_per_unit[{{ $loop->index }}]" value="{{ $product->pivot->quantity_per_unit }}">
                            @endforeach

                            {{-- Produits Associés — full-width DataTable --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-cubes me-2"></i>Produits Associés
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-light" id="showAddPanel">
                                                <i class="fas fa-plus me-1"></i> Ajouter un produit
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            {{-- Add-product panel (hidden by default) --}}
                                            <div id="addProductPanel" class="border rounded p-3 mb-3 bg-light" style="display:none">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-9">
                                                        <label class="form-label small mb-1">Produit</label>
                                                        <select id="newProductSelect" class="form-control w-100"></select>
                                                    </div>
                                                    <div class="col-md-3 d-flex gap-2">
                                                        <button type="button" class="btn btn-success btn-sm flex-fill" id="confirmAddProduct">
                                                            <i class="fas fa-check me-1"></i>Ajouter
                                                        </button>
                                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelAddProduct">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table id="edit-products-table" class="table table-bordered table-hover w-100">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="4%">#</th>
                                                            <th>Produit</th>
                                                            <th>Code</th>
                                                            <th class="text-end">Prix Client</th>
                                                            <th class="text-end">Prix Grossiste</th>
                                                            <th class="text-end">Prix Commercial</th>
                                                            <th class="text-end">Prix Spécial</th>
                                                            <th class="text-end">Stock</th>
                                                            <th width="8%" class="text-center">Retirer</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Info: prices applied automatically -->
                                <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-sync-alt me-2"></i>Application des prix
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle me-2"></i>
                                                Les prix seront <strong>automatiquement appliqués</strong> à tous les produits associés lors de l'enregistrement.<br>
                                                Formule : <strong>Prix unitaire (DH/m³) × Volume du produit (m³)</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Action Buttons -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-save me-2"></i>Enregistrement
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                                    <i class="fas fa-save me-2"></i> Enregistrer les Modifications
                                                </button>
                                                <a href="{{ route('familles.show', $famille->famille_id) }}"
                                                    class="btn btn-info">
                                                    <i class="fas fa-eye me-2"></i> Voir Détails
                                                </a>
                                                <a href="{{ route('familles.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times me-2"></i> Annuler
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

@push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: 0; }
        .price-input:focus { border-color: #ffc107; box-shadow: 0 0 0 0.2rem rgba(255,193,7,.25); }
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; }
        .select2-selection__arrow { height: 36px !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
    const FAMILLE_ID   = {{ $famille->famille_id }};
    const PRODUCTS_URL = "{{ route('familles.products-data', $famille->famille_id) }}";
    const MANAGE_URL   = "{{ route('familles.manage-products', $famille->famille_id) }}";
    const SEARCH_URL   = "{{ route('products.search') }}";
    const CSRF         = "{{ csrf_token() }}";

    $(document).ready(function () {

        // ── Products DataTable ────────────────────────────────────────────────
        const dt = $('#edit-products-table').DataTable({ paging: false, lengthChange: false, 
            processing: true,
            serverSide: true,
            ajax: { url: PRODUCTS_URL + '?editable=1', type: 'GET' },
            columns: [
                { data: 'DT_RowIndex',     name: 'DT_RowIndex',      orderable: false, searchable: false, className: 'text-center' },
                { data: 'product_name',    name: 'p.product_name' },
                { data: 'product_code',    name: 'p.product_code' },
                { data: 'prix_client',     name: 'pf.prix_client',     className: 'text-end' },
                { data: 'prix_grossiste',  name: 'pf.prix_grossiste',  className: 'text-end' },
                { data: 'prix_commercial', name: 'pf.prix_commercial', className: 'text-end' },
                { data: 'prix_special',    name: 'pf.prix_special',    className: 'text-end' },
                { data: 'current_quantity',name: 'pfs.current_quantity',className: 'text-end', orderable: false },
                { data: 'action',          name: 'action',             orderable: false, searchable: false, className: 'text-center' },
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
        });

        // ── Remove product (AJAX detach) ──────────────────────────────────────
        $(document).on('click', '.btn-detach-product', function () {
            const productId = $(this).data('product-id');
            const name      = $(this).data('product-name');
            if (!confirm(`Retirer "${name}" de cette famille ?`)) return;

            $.ajax({
                url: MANAGE_URL,
                type: 'POST',
                data: { _token: CSRF, action: 'detach', product_id: productId },
                success: function (res) {
                    showToast(res.success ? 'success' : 'error', res.message);
                    if (res.success) {
                        dt.ajax.reload(null, false);
                        // Also remove the hidden input for this product
                        $('input[name="associated_products[]"][value="' + productId + '"]').remove();
                        $('input[name="quantity_per_unit[]"]').each(function(index) {
                            if ($(this).attr('name') === 'quantity_per_unit[' + index + ']' && $(this).closest('div').find('input[value="' + productId + '"]').length === 0) {
                                // This is a bit hacky, better to refresh the page or rebuild hidden inputs
                                location.reload();
                            }
                        });
                    }
                },
                error: function (xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur lors du retrait');
                }
            });
        });

        // ── Add product panel ─────────────────────────────────────────────────
        $('#showAddPanel').click(function () {
            $('#addProductPanel').slideToggle();
            $('#newProductSelect').val(null).trigger('change');
        });
        $('#cancelAddProduct').click(function () { $('#addProductPanel').slideUp(); });

        $('#newProductSelect').select2({
            ajax: {
                url: SEARCH_URL,
                dataType: 'json',
                delay: 250,
                data: function (p) { return { q: p.term || '' }; },
                processResults: function (d) { return { results: d.results }; },
                cache: true,
            },
            language: 'fr',
            placeholder: 'Rechercher un produit...',
            allowClear: true,
            minimumInputLength: 0,
            dropdownParent: $('#addProductPanel'),
        });

        $('#confirmAddProduct').click(function () {
            const productId = $('#newProductSelect').val();
            if (!productId) { showToast('error', 'Sélectionnez un produit'); return; }

            $.ajax({
                url: MANAGE_URL,
                type: 'POST',
                data: { _token: CSRF, action: 'attach', product_id: productId, quantity_per_unit: 1 },
                success: function (res) {
                    showToast(res.success ? 'success' : 'error', res.message);
                    if (res.success) {
                        dt.ajax.reload(null, false);
                        $('#addProductPanel').slideUp();
                        $('#newProductSelect').val(null).trigger('change');
                        // Reload the page to update hidden inputs
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function (xhr) {
                    showToast('error', xhr.responseJSON?.message || 'Erreur lors de l\'ajout');
                }
            });
        });

        // ── Main form submit (prices + famille info only) ─────────────────────
        $('#familleForm').submit(function (e) {
            e.preventDefault();
            const submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(() => { window.location.href = "{{ route('familles.show', $famille->famille_id) }}"; }, 1500);
                    } else {
                        showToast('error', res.message);
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Enregistrer les Modifications');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                        : (xhr.responseJSON?.message || 'Une erreur est survenue');
                    showToast('error', msg);
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Enregistrer les Modifications');
                }
            });
        });

        // ── Toast ─────────────────────────────────────────────────────────────
        function showToast(type, message) {
            const toast = $(`<div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-atomic="true">
                <div class="d-flex"><div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
            $('#toast-container').append(toast);
            new bootstrap.Toast(toast[0]).show();
            setTimeout(() => toast.remove(), 5000);
        }
    });
    </script>
@endpush
