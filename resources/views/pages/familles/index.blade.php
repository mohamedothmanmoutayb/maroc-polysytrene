@extends('layouts.app')

@section('title', 'Gestion des Familles')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Familles</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('products.index') }}">
                                        Produits
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Familles
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
                    <div class="card-header card-header-custom" style="display: flex;justify-content: space-between;">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-layer-group me-2"></i>Liste des Familles
                        </h5>
                        <div class="card-tools">
                            <button class="btn btn-light btn-sm" id="filterBtn">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_families')
                            <a href="{{ route('familles.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvelle Famille
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3" id="filtersSection" style="display: none;">
                            <div class="col-md-3">
                                <label for="filterProduct" class="form-label">Produit Associé</label>
                                <select class="form-control select2" id="filterProduct" name="product_id">
                                    <option value="">Tous les produits</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->product_id }}">
                                            {{ $product->product_name }} ({{ $product->product_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterStatus" class="form-label">Statut</label>
                                <select class="form-control" id="filterStatus" name="is_active">
                                    <option value="">Tous</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary me-2" id="applyFilters">
                                    <i class="fas fa-check me-1"></i> Appliquer
                                </button>
                                <button class="btn btn-secondary" id="resetFilters">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="famillesTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom de la Famille</th>
                                        <th>Produits Associés</th>
                                        <th>Stock Total</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Créée le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Products Modal -->
    <div class="modal fade" id="manageProductsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="manageProductsForm">
                    @csrf
                    <input type="hidden" id="modalFamilleId" name="famille_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Gérer les Produits Associés</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Famille</label>
                                <input type="text" class="form-control" id="modalManageFamilleName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" id="modalManageFamilleCode" readonly>
                            </div>
                        </div>

                        <!-- Add Product Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-plus me-2"></i>Ajouter un Produit
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="addProductId" class="form-label">Produit *</label>
                                        <select class="form-control select2" id="addProductId" name="product_id">
                                            <option value="">Sélectionner un produit...</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->product_id }}">
                                                    {{ $product->product_name }} ({{ $product->product_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="addQuantityPerUnit" class="form-label">Quantité par Unité</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="addQuantityPerUnit"
                                                name="quantity_per_unit" min="0.01" step="0.01" value="1">
                                            <span class="input-group-text">/unité</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-success w-100" id="btnAddProduct">
                                            <i class="fas fa-plus me-1"></i> Ajouter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Associated Products List -->
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>Produits Associés
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="associatedProductsTable">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Code</th>
                                                <th>Quantité/Unité</th>
                                                <th>Stock</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="associatedProductsBody">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="noProductsMessage" class="text-center p-4">
                                    <p class="text-muted">Aucun produit associé à cette famille</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="adjustStockForm">
                    @csrf
                    <input type="hidden" id="adjustFamilleId" name="famille_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajuster le Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Famille</label>
                                <input type="text" class="form-control" id="modalFamilleName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" id="modalFamilleCode" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">Produit *</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">Sélectionner un produit...</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Stock Actuel</label>
                                <input type="text" class="form-control" id="modalCurrentStock" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d'Ajustement</label>
                                <input type="date" class="form-control" id="adjustment_date" name="adjustment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adjustment_type" class="form-label">Type d'Ajustement *</label>
                            <select class="form-control" id="adjustment_type" name="adjustment_type" required>
                                <option value="add">Ajouter au stock (+)</option>
                                <option value="remove">Retirer du stock (-)</option>
                                <option value="set">Définir le stock (=)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantité *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="quantity" name="quantity" required
                                    min="0" step="0.01" value="0">
                                <span class="input-group-text">unités</span>
                            </div>
                            <div class="form-text">
                                <span id="newStockPreview">Nouveau stock: <strong id="newStockValue">0</strong>
                                    unités</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes *</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Raison de l'ajustement..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="saveAdjustment">
                            <i class="ti ti-check me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activate/Deactivate Confirmation Modal -->
    <div class="modal fade" id="statusChangeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusChangeTitle">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="statusChangeMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChange">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de Suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la famille <strong id="deleteFamilleName"></strong> ?</p>
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        Cette action est irréversible. Tous les enregistrements associés à cette famille seront affectés.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .price-info {
            font-size: 0.85rem;
        }

        .price-info div {
            padding: 2px 0;
        }

        .price-info small {
            color: #6c757d;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        let famillesTable;
        let currentFamilleId = null;
        let currentAction    = null;
        const CSRF = $('meta[name="csrf-token"]').attr('content');

        /* ── Toast ── */
        function showToast(type, message) {
            const bg   = type === 'success' ? 'bg-success' : 'bg-danger';
            const html = `
                <div class="toast align-items-center text-white ${bg} border-0" role="alert" data-bs-autohide="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
            const el = $(html).appendTo('#toast-container')[0];
            new bootstrap.Toast(el).show();
            setTimeout(() => $(el).remove(), 4500);
        }

        $(document).ready(function () {

            /* ── Select2 ── */
            $('.select2').select2({ language: 'fr', placeholder: 'Sélectionner...', allowClear: true });

            /* ── Filters toggle ── */
            $('#filterBtn').on('click', function () { $('#filtersSection').slideToggle(); });

            /* ── DataTable ── */
            famillesTable = $('#famillesTable').DataTable({ paging: false, lengthChange: false, 
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('familles.index') }}",
                    data: function (d) {
                        d.product_id = $('#filterProduct').val();
                        d.is_active  = $('#filterStatus').val();
                    },
                    error: function (xhr) {
                        console.error('DataTables Error:', xhr.responseText);
                        showToast('error', 'Erreur lors du chargement des données');
                    }
                },
                columns: [
                    { data: 'famille_code',  name: 'famille_code' },
                    { data: 'famille_name',  name: 'famille_name' },
                    { data: 'product_info',  name: 'product_info',  orderable: false, searchable: false },
                    { data: 'stock_info',    name: 'stock_info',    orderable: false, searchable: false },
                    { data: 'price_info',    name: 'price_info',    orderable: false, searchable: false },
                    { data: 'status_badge',  name: 'status_badge',  orderable: false, searchable: false },
                    { data: 'created_at',    name: 'created_at' },
                    { data: 'action',        name: 'action',        orderable: false, searchable: false }
                ],
                order: [[6, 'desc']],
                pageLength: 10,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json' }
            });

            $('#applyFilters').on('click',  function () { famillesTable.ajax.reload(); });
            $('#resetFilters').on('click',  function () {
                $('#filterProduct').val('').trigger('change');
                $('#filterStatus').val('');
                famillesTable.ajax.reload();
            });

            /* ════════════════════════════════════════════════════════
               DELEGATED EVENTS — work on DataTables-injected buttons
               ════════════════════════════════════════════════════════ */

            /* ── Manage Products ── */
            $(document).on('click', '.btn-manage-products', function () {
                const id   = $(this).data('famille-id');
                const name = $(this).data('famille-name');
                const code = $(this).data('famille-code');

                $('#modalFamilleId').val(id);
                $('#modalManageFamilleName').val(name);
                $('#modalManageFamilleCode').val(code);
                $('#associatedProductsBody').empty();
                $('#noProductsMessage').show();

                loadAssociatedProducts(id);
                new bootstrap.Modal(document.getElementById('manageProductsModal')).show();
            });

            /* ── Add product inside manage modal ── */
            $('#btnAddProduct').on('click', function () {
                const familleId       = $('#modalFamilleId').val();
                const productId       = $('#addProductId').val();
                const quantityPerUnit = $('#addQuantityPerUnit').val();

                if (!productId) { alert('Veuillez sélectionner un produit'); return; }

                const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Ajout...');

                $.ajax({
                    url: `/familles/${familleId}/manage-products`,
                    type: 'POST',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    data: JSON.stringify({ action: 'attach', product_id: productId, quantity_per_unit: quantityPerUnit }),
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.message);
                            loadAssociatedProducts(familleId);
                            $('#addProductId').val(null).trigger('change');
                            $('#addQuantityPerUnit').val('1');
                        } else {
                            showToast('error', res.message);
                        }
                    },
                    error: function () { showToast('error', 'Une erreur est survenue'); },
                    complete: function () { btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Ajouter'); }
                });
            });

            /* ── Remove product (delegated — buttons inside modal tbody) ── */
            $(document).on('click', '.btn-remove-product', function () {
                const familleId   = $('#modalFamilleId').val();
                const productId   = $(this).data('product-id');
                const productName = $(this).data('product-name');

                if (!confirm(`Détacher le produit "${productName}" ?`)) return;

                $.ajax({
                    url: `/familles/${familleId}/manage-products`,
                    type: 'POST',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    data: JSON.stringify({ action: 'detach', product_id: productId }),
                    success: function (res) {
                        if (res.success) { showToast('success', res.message); loadAssociatedProducts(familleId); }
                        else showToast('error', res.message);
                    },
                    error: function () { showToast('error', 'Une erreur est survenue'); }
                });
            });

            /* ── Adjust Stock ── */
            $(document).on('click', '.btn-adjust-stock', function () {
                const id   = $(this).data('famille-id');
                const name = $(this).data('famille-name');
                const code = $(this).data('famille-code');

                $('#adjustFamilleId').val(id);
                $('#modalFamilleName').val(name);
                $('#modalFamilleCode').val(code);
                $('#adjustment_type').val('add');
                $('#quantity').val('0');
                $('#notes').val('');
                $('#modalCurrentStock').val('0.00');

                loadProductsForAdjustment(id);
                new bootstrap.Modal(document.getElementById('adjustStockModal')).show();
            });

            $('#product_id').on('change', function () {
                const stock = $(this).find(':selected').data('current-stock') || '0';
                $('#modalCurrentStock').val(parseFloat(stock).toFixed(2));
                updateStockPreview();
            });
            $('#adjustment_type, #quantity').on('change input', updateStockPreview);

            $('#adjustStockForm').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#saveAdjustment').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Enregistrement...');

                $.ajax({
                    url: "{{ route('familles.adjust-stock', '') }}/" + $('#adjustFamilleId').val(),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.message);
                            bootstrap.Modal.getInstance(document.getElementById('adjustStockModal')).hide();
                            famillesTable.ajax.reload();
                        } else {
                            showToast('error', res.message);
                            btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Enregistrer');
                        }
                    },
                    error: function () {
                        showToast('error', 'Une erreur est survenue');
                        btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Enregistrer');
                    }
                });
            });

            /* ── Activate / Deactivate ── */
            $(document).on('click', '.btn-activate, .btn-deactivate', function () {
                currentFamilleId = $(this).data('famille-id');
                const familleName = $(this).data('famille-name');

                if ($(this).hasClass('btn-activate')) {
                    currentAction = 'activate';
                    $('#statusChangeTitle').text('Activation de la Famille');
                    $('#statusChangeMessage').text(`Voulez-vous activer la famille "${familleName}" ?`);
                } else {
                    currentAction = 'deactivate';
                    $('#statusChangeTitle').text('Désactivation de la Famille');
                    $('#statusChangeMessage').text(`Voulez-vous désactiver la famille "${familleName}" ? Une famille désactivée ne sera plus disponible pour les nouvelles productions.`);
                }
                new bootstrap.Modal(document.getElementById('statusChangeModal')).show();
            });

            $('#confirmStatusChange').on('click', function () {
                const btn = $(this).prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Traitement...');

                $.ajax({
                    url: "{{ route('familles.update', '') }}/" + currentFamilleId,
                    type: 'PUT',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    data: JSON.stringify({ is_active: currentAction === 'activate' }),
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.message);
                            bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
                            famillesTable.ajax.reload();
                        } else {
                            showToast('error', res.message);
                            btn.prop('disabled', false).html('Confirmer');
                        }
                    },
                    error: function () {
                        showToast('error', 'Une erreur est survenue');
                        btn.prop('disabled', false).html('Confirmer');
                    }
                });
            });

            /* ── Delete ── */
            $(document).on('click', '.btn-delete', function () {
                currentFamilleId = $(this).data('famille-id');
                $('#deleteFamilleName').text($(this).data('famille-name'));
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });

            $('#confirmDelete').on('click', function () {
                const btn = $(this).prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Suppression...');

                $.ajax({
                    url: "{{ route('familles.destroy', '') }}/" + currentFamilleId,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.message);
                            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                            famillesTable.ajax.reload();
                        } else {
                            showToast('error', res.message);
                            btn.prop('disabled', false).html('Supprimer');
                        }
                    },
                    error: function () {
                        showToast('error', 'Une erreur est survenue');
                        btn.prop('disabled', false).html('Supprimer');
                    }
                });
            });
        });

        /* ════════════════════════════════════════════════
           HELPER FUNCTIONS
           ════════════════════════════════════════════════ */

        function loadAssociatedProducts(familleId) {
            $.get(`/api/familles/${familleId}/products`, function (data) {
                const tbody = $('#associatedProductsBody').empty();
                if (data.success && data.data.length > 0) {
                    $('#noProductsMessage').hide();
                    data.data.forEach(function (p) {
                        tbody.append(`
                            <tr>
                                <td>${p.product_name}</td>
                                <td>${p.product_code}</td>
                                <td>${p.quantity_per_unit}</td>
                                <td>${p.stock || '0.00'}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-product"
                                            data-product-id="${p.product_id}"
                                            data-product-name="${p.product_name}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>`);
                    });
                } else {
                    $('#noProductsMessage').show();
                }
            });
        }

        function loadProductsForAdjustment(familleId) {
            const sel = $('#product_id').html('<option value="">Sélectionner un produit...</option>');
            $.get(`/api/familles/by-product/${familleId}`, function (data) {
                if (data.success && data.data.length > 0) {
                    data.data.forEach(function (f) {
                        sel.append(`<option value="${f.product_id}" data-current-stock="${f.stock || 0}">
                            ${f.product_name} (Stock: ${f.stock || '0.00'})
                        </option>`);
                    });
                }
            });
        }

        function updateStockPreview() {
            const currentStock   = parseFloat($('#modalCurrentStock').val()) || 0;
            const adjustmentType = $('#adjustment_type').val();
            const quantity       = parseFloat($('#quantity').val()) || 0;

            let newStock = currentStock;
            if      (adjustmentType === 'add')    newStock = currentStock + quantity;
            else if (adjustmentType === 'remove') newStock = currentStock - quantity;
            else if (adjustmentType === 'set')    newStock = quantity;

            if (adjustmentType === 'remove' && quantity > currentStock) {
                $('#newStockPreview').html('<span class="text-danger">Erreur : Quantité supérieure au stock disponible</span>');
                $('#saveAdjustment').prop('disabled', true);
                return;
            }

            $('#saveAdjustment').prop('disabled', false);
            const cls = newStock > currentStock ? 'text-success' : newStock < currentStock ? 'text-danger' : 'text-muted';
            $('#newStockPreview').html(`Nouveau stock : <strong id="newStockValue" class="${cls}">${newStock.toFixed(2)}</strong>`);
        }
    </script>
@endpush
