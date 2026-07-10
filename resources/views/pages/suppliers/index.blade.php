@extends('layouts.app')

@section('title', 'Gestion des Fournisseurs')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Fournisseurs</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Fournisseurs
                                    </span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="statistics row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Total Fournisseurs</span>
                                <h3 class="mb-0" id="totalSuppliers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-truck fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Fournisseurs Actifs</span>
                                <h3 class="mb-0" id="activeSuppliers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Physique</span>
                                <h3 class="mb-0" id="physiqueSuppliers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Morale</span>
                                <h3 class="mb-0" id="moraleSuppliers">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="color:white">
                            <i class="fas fa-truck me-2"></i>Liste des Fournisseurs
                        </h5>
                        <div>
                            @can('create_suppliers')
                            <a href="{{ route('suppliers.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouveau Fournisseur
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="suppliers-table" class="table table-bordered table-hover w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nom/Entreprise</th>
                                        <th>Type</th>
                                        <th>Contact</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Info Entreprise</th>
                                        <th>Statut</th>
                                        <th class="text-center">Solde</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribute Payment Modal -->
    <div class="modal fade" id="distributePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Paiement Global Fournisseur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="distributePaymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="dist_check_id" name="check_id">
                    <input type="hidden" id="dist_traite_id" name="traite_id">
                    <input type="hidden" id="dist_total_rest_raw" value="0">
                    <input type="hidden" id="dist_current_balance_raw" value="0">
                    <div class="modal-body">
                        <p class="text-muted small">Le montant sera distribué automatiquement sur les achats impayés (ordre
                            chronologique).</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-danger bg-opacity-10 border border-danger rounded">
                                    <span class="small text-danger fw-semibold">Total impayé</span>
                                    <strong class="text-danger" id="dist_total_rest_display">—</strong>
                                </div>
                            </div>
                            <div class="col-6" id="dist_balance_info_row" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-success bg-opacity-10 border border-success rounded">
                                    <span class="small text-success fw-semibold">Solde crédit</span>
                                    <strong class="text-success" id="dist_balance_info_display">—</strong>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info py-2 mb-3" id="dist_no_unpaid_info" style="display:none;">
                            <i class="fas fa-info-circle me-1"></i>Ce fournisseur n'a aucun achat impayé. Le montant sera
                            entièrement ajouté à son solde.<br>
                            Solde actuel : <strong id="dist_current_balance_display">—</strong>
                            <span id="dist_new_balance_wrap"> → Nouveau solde : <strong id="dist_new_balance_display">—</strong></span>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant (DH) *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="dist_amount" name="amount"
                                        pattern="^[0-9]+([.,][0-9]+)?$" inputmode="decimal"
                                        placeholder="ex: 950,40" required>
                                    <span id="dist_solde_badge" class="input-group-text text-success fw-bold"
                                        style="display:none;" title="Le surplus sera ajouté au solde fournisseur">
                                        +solde
                                    </span>
                                </div>
                                <small id="dist_excess_info" class="text-success mt-1" style="display:none;"></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" id="dist_date" name="payment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Méthode *</label>
                                <select class="form-control" id="dist_method" name="payment_method" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="cash">Espèces</option>
                                    <option value="bank_transfer">Virement Bancaire</option>
                                    <option value="check">Chèque</option>
                                    <option value="traite">Traite</option>
                                    <option value="credit_card">Carte de crédit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Document</label>
                                <input type="file" class="form-control" id="dist_file" name="payment_file"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        {{-- Check selection (shown when check selected) --}}
                        <div id="dist_check_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-money-check me-1"></i>Sélection du Chèque</h6>
                            <div id="dist_check_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Chèque sélectionné: <strong
                                    id="dist_check_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="dist_check_clear_btn">Changer</button>
                            </div>
                            <div id="dist_check_picker">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" id="dist_checks_table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>N° Chèque</th>
                                                <th>Banque</th>
                                                <th>Montant</th>
                                                <th>Disponible</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="dist_checks_body">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Traite section (shown when traite selected) --}}
                        <div id="dist_traite_section" class="border rounded p-3 bg-light mb-3" style="display:none;">
                            <h6 class="mb-2"><i class="fas fa-file-invoice me-1"></i>Traite</h6>
                            <div id="dist_traite_selected_info" class="alert alert-success py-2 mb-2"
                                style="display:none;">
                                <i class="fas fa-check-circle me-1"></i>Traite sélectionnée: <strong
                                    id="dist_traite_selected_label"></strong>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                    id="dist_traite_clear_btn">Changer</button>
                            </div>
                            <div id="dist_traite_picker">
                                <ul class="nav nav-tabs mb-2" id="dist_traite_tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#dist_traite_existing_tab"
                                            data-bs-toggle="tab">Existante</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#dist_traite_new_tab" data-bs-toggle="tab">Nouvelle</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="dist_traite_existing_tab">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>N° Traite</th>
                                                        <th>Banque</th>
                                                        <th>Montant</th>
                                                        <th>Échéance</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dist_traites_body">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Chargement...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="dist_traite_new_tab">
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">N° Traite</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="dist_traite_number" name="traite_number"
                                                    placeholder="Auto si vide">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Date d'Échéance *</label>
                                                <input type="date" class="form-control form-control-sm"
                                                    id="dist_traite_due" name="traite_due_date">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label form-label-sm">Banque</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    id="dist_traite_bank" name="traite_bank"
                                                    placeholder="Nom de la banque">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="dist_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i
                                class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Balance Modal -->
    <div class="modal fade" id="addBalanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-wallet me-2"></i>Ajouter un Solde — <span id="balanceSupplierName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addBalanceForm">
                    <input type="hidden" id="balanceSupplierId">
                    <div class="modal-body">
                        <p class="text-muted small">Enregistrez un paiement direct au fournisseur (non attribué à un achat spécifique). Ce montant sera crédité en solde et peut être utilisé pour payer des achats futurs.<br><strong>Montant négatif</strong> : pour récupérer du solde (retrait de crédit).</p>
                        <div class="alert alert-secondary py-2" id="balanceCurrentInfo"></div>
                        <div class="mb-3">
                            <label class="form-label">Montant (DH) * <small class="text-muted">(négatif pour récupérer)</small></label>
                            <input type="text" class="form-control" id="bal_amount" pattern="^-?[0-9]+([.,][0-9]+)?$" inputmode="decimal" placeholder="ex: 950,40" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date *</label>
                            <input type="date" class="form-control" id="bal_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="bal_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-info" id="balanceSubmitBtn"><i class="fas fa-save me-1"></i>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le fournisseur : <strong id="deleteSupplierName"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script>
        var currentSupplierId = null;
        var currentSupplierName = '';

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            var d = new Date(dateString);
            if (isNaN(d.getTime())) return 'N/A';
            return String(d.getDate()).padStart(2, '0') + '/' +
                String(d.getMonth() + 1).padStart(2, '0') + '/' +
                d.getFullYear();
        }

        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#suppliers-table').DataTable({ paging: false, lengthChange: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('suppliers.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'display_name',
                        name: 'display_name'
                    },
                    {
                        data: 'supplier_type_badge',
                        name: 'supplier_type',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'contact_person',
                        name: 'contact_person'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'company_info',
                        name: 'company_info',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'balance_display',
                        name: 'balance',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
                },
                order: [
                    [1, 'asc']
                ],
                responsive: true,
                dom: '<"row"<"col-md-6"l><"col-md-6"f>>rtip',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-light',
                        text: '<i class="fas fa-print me-1"></i> Imprimer'
                    }
                ]
            });

            // Initialize tooltips
            table.on('draw', function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            // Load statistics
            loadStatistics();

            function loadStatistics() {
                $.ajax({
                    url: "{{ route('suppliers.statistics') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#totalSuppliers').text(response.data.total);
                            $('#activeSuppliers').text(response.data.active);
                            $('#physiqueSuppliers').text(response.data.physique);
                            $('#moraleSuppliers').text(response.data.morale);
                        }
                    },
                    error: function() {
                        $('#totalSuppliers').text('0');
                        $('#activeSuppliers').text('0');
                        $('#physiqueSuppliers').text('0');
                        $('#moraleSuppliers').text('0');
                    }
                });
            }

            // Handle delete button click
            $(document).on('click', '.dropdown-item.delete', function() {
                var supplierId = $(this).data('id');
                var supplierName = $(this).data('name');

                $('#deleteSupplierName').text(supplierName);
                $('#deleteForm').attr('action', "{{ url('suppliers') }}/" + supplierId);
                $('#deleteModal').modal('show');
            });

            // Delete Form Submit
            $('#deleteForm').submit(function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.draw();
                            loadStatistics();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        var response = xhr.responseJSON;
                        showToast('error', response?.message ||
                            'Erreur lors de la suppression');
                    }
                });
            });

            // ── Distribute Payment (global button) ───────────────────────────
            $(document).on('click', '.pay-supplier-all-btn', function() {
                currentSupplierId = $(this).data('id');
                currentSupplierName = $(this).data('name');
                var totalRest = parseFloat($(this).data('rest')) || 0;
                var balance = parseFloat($(this).data('balance')) || 0;
                var credit = balance < -0.01 ? Math.abs(balance) : 0;

                $('#distributePaymentForm')[0].reset();
                $('#dist_check_id').val('');
                $('#dist_traite_id').val('');
                $('#dist_date').val('{{ date("Y-m-d") }}');
                $('#dist_check_section').hide();
                $('#dist_traite_section').hide();
                $('#dist_check_selected_info').hide();
                $('#dist_check_picker').show();
                $('#dist_traite_selected_info').hide();
                $('#dist_traite_picker').show();

                // Populate info section
                $('#dist_total_rest_raw').val(totalRest);
                $('#dist_total_rest_display').text(
                    totalRest.toLocaleString('de-DE', {minimumFractionDigits: 2}) + ' DH');
                $('#dist_amount').val(totalRest > 0.005 ? totalRest.toFixed(2).replace('.', ',') : '');
                $('#dist_current_balance_raw').val(balance);
                $('#dist_no_unpaid_info').hide();

                if (credit > 0.005) {
                    $('#dist_balance_info_display').text(
                        credit.toLocaleString('de-DE', {minimumFractionDigits: 2}) + ' DH');
                    $('#dist_balance_info_row').show();
                } else {
                    $('#dist_balance_info_row').hide();
                }
                $('#dist_solde_badge').hide();
                $('#dist_excess_info').hide().text('');

                $('#distributePaymentModal').modal('show');
            });

            function formatSupplierBalance(balance) {
                var abs = Math.abs(balance).toLocaleString('de-DE', {minimumFractionDigits: 2});
                if (balance > 0.01) return abs + ' DH (dû)';
                if (balance < -0.01) return abs + ' DH (crédit)';
                return '0,00 DH';
            }

            // ── Surplus → solde indicator ─────────────────────────────────────
            $('#dist_amount').on('input', function() {
                var raw = $(this).val().trim().replace(',', '.');
                var amount = parseFloat(raw);
                if (isNaN(amount)) amount = 0;
                var rest = parseFloat($('#dist_total_rest_raw').val()) || 0;
                var excess = amount - rest;
                // Only a real overpayment (unpaid purchases exist and amount exceeds them)
                // counts as "excess to balance" — when there's nothing to pay (rest = 0), the
                // dedicated info box below already explains the whole amount goes to balance.
                if (rest > 0.01 && excess > 0.005) {
                    $('#dist_solde_badge').show();
                    $('#dist_excess_info').text(
                        excess.toLocaleString('de-DE', {minimumFractionDigits: 2}) +
                        ' DH → solde fournisseur'
                    ).show();
                } else {
                    $('#dist_solde_badge').hide();
                    $('#dist_excess_info').hide();
                }

                // No unpaid purchase to pay: the whole amount is applied to the balance —
                // show the supplier's current/projected balance.
                if (rest <= 0.01 && amount > 0.005) {
                    var currentBalance = parseFloat($('#dist_current_balance_raw').val()) || 0;
                    var newBalance = currentBalance - amount;
                    $('#dist_current_balance_display').text(formatSupplierBalance(currentBalance));
                    $('#dist_new_balance_display').text(formatSupplierBalance(newBalance));
                    $('#dist_no_unpaid_info').show();
                } else {
                    $('#dist_no_unpaid_info').hide();
                }
            });

            $('#dist_method').change(function() {
                var method = $(this).val();
                $('#dist_check_section').toggle(method === 'check');
                $('#dist_traite_section').toggle(method === 'traite');

                if (method === 'check') {
                    loadDistributeChecks();
                }
                if (method === 'traite') {
                    loadTraitesList('#dist_traites_body', '#dist_traite_existing_tab');
                }
            });

            function loadDistributeChecks() {
                $('#dist_checks_body').html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-checks') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $('#dist_checks_body');
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucun chèque disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(c) {
                            tbody.append('<tr>' +
                                '<td>' + (c.check_number || 'N/A') + '</td>' +
                                '<td>' + (c.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(c.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + parseFloat(c.available_amount).toFixed(2) +
                                ' DH</td>' +
                                '<td><button type="button" class="btn btn-sm btn-primary dist-select-check" ' +
                                'data-id="' + c.check_id + '" data-label="' + c
                                .check_number + ' (' + parseFloat(c.available_amount)
                                .toFixed(2) + ' DH)">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $('#dist_checks_body').html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            $(document).on('click', '.dist-select-check', function() {
                $('#dist_check_id').val($(this).data('id'));
                $('#dist_check_selected_label').text($(this).data('label'));
                $('#dist_check_selected_info').show();
                $('#dist_check_picker').hide();
            });

            $('#dist_check_clear_btn').click(function() {
                $('#dist_check_id').val('');
                $('#dist_check_selected_info').hide();
                $('#dist_check_picker').show();
            });

            $(document).on('click', '.dist-select-traite', function() {
                $('#dist_traite_id').val($(this).data('id'));
                $('#dist_traite_selected_label').text($(this).data('label'));
                $('#dist_traite_selected_info').show();
                $('#dist_traite_picker').hide();
            });

            $('#dist_traite_clear_btn').click(function() {
                $('#dist_traite_id').val('');
                $('#dist_traite_selected_info').hide();
                $('#dist_traite_picker').show();
            });

            function loadTraitesList(bodySelector, tabSelector) {
                $(bodySelector).html('<tr><td colspan="5" class="text-center">Chargement...</td></tr>');
                $.ajax({
                    url: "{{ route('raw-material-purchases.available-traites') }}",
                    type: 'GET',
                    success: function(res) {
                        var tbody = $(bodySelector);
                        tbody.empty();
                        if (!res.success || !res.data.length) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">Aucune traite disponible</td></tr>'
                                );
                            return;
                        }
                        res.data.forEach(function(t) {
                            var due = t.due_date ? formatDate(t.due_date) : 'N/A';
                            tbody.append('<tr>' +
                                '<td>' + (t.traite_number || 'N/A') + '</td>' +
                                '<td>' + (t.bank_name || 'N/A') + '</td>' +
                                '<td>' + parseFloat(t.amount).toFixed(2) + ' DH</td>' +
                                '<td>' + due + '</td>' +
                                '<td><button type="button" class="btn btn-sm btn-warning select-traite-btn dist-select-traite" ' +
                                'data-traite-id="' + t.traite_id +
                                '" data-traite-number="' + t.traite_number + '" ' +
                                'data-id="' + t.traite_id + '" data-label="' + t
                                .traite_number + ' (' + parseFloat(t.amount).toFixed(2) +
                                ' DH)">Utiliser</button></td>' +
                                '</tr>');
                        });
                    },
                    error: function() {
                        $(bodySelector).html(
                            '<tr><td colspan="5" class="text-center text-danger">Erreur</td></tr>');
                    }
                });
            }

            $('#distributePaymentForm').submit(function(e) {
                e.preventDefault();
                if (!currentSupplierId) return;

                var method = $('#dist_method').val();
                if (method === 'check' && !$('#dist_check_id').val()) {
                    showToast('error', 'Veuillez sélectionner un chèque');
                    return;
                }

                var rawAmount = $('#dist_amount').val().trim().replace(',', '.');
                var amount = parseFloat(rawAmount);
                if (isNaN(amount) || amount < 0.01) {
                    showToast('error', 'Montant invalide.');
                    return;
                }

                var $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                var fd = new FormData(this);
                fd.set('amount', amount);
                fd.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ url('raw-material-purchases/supplier') }}/" + currentSupplierId +
                        "/distribute-payment",
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#distributePaymentModal').modal('hide');
                            showToast('success', res.message);
                            table.draw();
                            loadStatistics();
                        } else {
                            showToast('error', res.message);
                        }
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    },
                    error: function(xhr) {
                        showToast('error', xhr.responseJSON?.message ||
                            'Erreur lors du paiement');
                        $btn.prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i>Enregistrer');
                    }
                });
            });

            // Handle add balance button click
            $(document).on('click', '.add-balance-btn', function() {
                var supplierId = $(this).data('id');
                var supplierName = $(this).data('name');
                var balance = parseFloat($(this).data('balance')) || 0;

                $('#balanceSupplierId').val(supplierId);
                $('#balanceSupplierName').text(supplierName);
                $('#bal_amount').val('');
                $('#bal_notes').val('');
                $('#bal_date').val('{{ date("Y-m-d") }}');

                var balanceInfo = '';
                if (balance > 0) {
                    balanceInfo = '<i class="fas fa-exclamation-circle text-danger me-1"></i>Solde actuel : <strong class="text-danger">' + Math.abs(balance).toFixed(2).replace('.', ',') + ' DH</strong> (débit)';
                } else if (balance < 0) {
                    balanceInfo = '<i class="fas fa-check-circle text-success me-1"></i>Solde actuel : <strong class="text-success">' + Math.abs(balance).toFixed(2).replace('.', ',') + ' DH</strong> (crédit)';
                } else {
                    balanceInfo = '<i class="fas fa-circle text-secondary me-1"></i>Solde actuel : <strong>0,00 DH</strong> (soldé)';
                }
                $('#balanceCurrentInfo').html(balanceInfo);

                $('#addBalanceModal').modal('show');
            });

            // Add balance form submit
            $('#addBalanceForm').submit(function(e) {
                e.preventDefault();
                var $btn = $('#balanceSubmitBtn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

                var rawAmount = $('#bal_amount').val().trim().replace(',', '.');
                var amount = parseFloat(rawAmount);
                if (isNaN(amount) || Math.abs(amount) < 0.01) {
                    showToast('error', 'Montant invalide.');
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Enregistrer');
                    return;
                }

                $.ajax({
                    url: "{{ url('suppliers/situation/supplier') }}/" + $('#balanceSupplierId').val() + "/add-balance",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: amount,
                        payment_date: $('#bal_date').val(),
                        notes: $('#bal_notes').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addBalanceModal').modal('hide');
                            table.draw();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message || 'Erreur lors de l\'enregistrement');
                        }
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Enregistrer');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement';
                        showToast('error', msg);
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Enregistrer');
                    }
                });
            });

            // setInterval(loadStatistics, 30000);

            function showToast(type, message) {
                var toast = $('<div class="toast align-items-center text-white bg-' +
                    (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')) +
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
