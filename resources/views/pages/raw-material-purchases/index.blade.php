@extends('layouts.app')

@section('title', 'Gestion des Achats')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Gestion des Achats</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">Achats</span>
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
                            <i class="fas fa-shopping-cart me-2"></i>Achats par Fournisseur
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" id="filterBtn">
                                <i class="fas fa-filter me-1"></i> Filtres
                            </button>
                            @can('create_raw_material_purchases')
                            <a href="{{ route('raw-material-purchases.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Nouvel Achat
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3" id="filtersSection" style="display:none;">
                            <div class="col-md-3">
                                <label class="form-label">Fournisseur</label>
                                <select class="form-control select2" id="filterSupplier">
                                    <option value="">Tous les fournisseurs</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->supplier_id }}">
                                            {{ $supplier->company_name ?? $supplier->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Statut Paiement</label>
                                <select class="form-control" id="filterStatus">
                                    <option value="">Tous</option>
                                    <option value="pending">Non Payé</option>
                                    <option value="partial">Avance</option>
                                    <option value="paid">Payé</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date début</label>
                                <input type="date" class="form-control" id="dateFrom">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date fin</label>
                                <input type="date" class="form-control" id="dateTo">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyFilters">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Grouped table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="purchasesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="4%">#</th>
                                        <th>Fournisseur</th>
                                        <th width="9%" class="text-center">Nb. Achats</th>
                                        <th width="13%" class="text-end">Montant Total</th>
                                        <th width="13%" class="text-end">Total Payé</th>
                                        <th width="13%" class="text-end">Reste</th>
                                        <th width="13%" class="text-end">Solde</th>
                                        <th width="18%" class="text-center">Statut</th>
                                        <th width="10%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Supplier Purchases Modal ───────────────────────────────────────── --}}
    <div class="modal fade" id="supplierPurchasesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <h5 class="modal-title text-white" id="supplierPurchasesTitle">
                        <i class="fas fa-list me-2"></i>Achats du fournisseur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="spLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Chargement...</p>
                    </div>
                    <div id="spContent" style="display:none;">
                        <div class="row mb-3" id="spSummary"></div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Date</th>
                                        <th>N° Achat</th>
                                        <th class="text-end">Montant</th>
                                        <th class="text-end">Payé</th>
                                        <th class="text-end">Reste</th>
                                        <th class="text-center">Statut</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="spBody"></tbody>
                                <tfoot id="spFoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Supplier Payment Modal (FIFO distribution) ────────────────────── --}}
    <div class="modal fade" id="supplierPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i>Paiement Fournisseur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Le paiement sera distribué automatiquement du plus ancien achat au plus récent.
                    </div>
                    <p class="fw-bold" id="spPaySupplierName"></p>
                    <form id="supplierPaymentForm">
                        <input type="hidden" id="spPaySupplierId">
                        <div class="mb-3">
                            <label class="form-label">Montant à payer *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="spPayAmount" min="0.01" step="0.01" required>
                                <span class="input-group-text">DH</span>
                            </div>
                            <small class="text-muted">Reste total : <strong id="spPayRestTotal"></strong> DH</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mode de paiement *</label>
                            <select class="form-control" id="spPayMethod" required>
                                <option value="">-- Choisir --</option>
                                <option value="cash">Espèces</option>
                                <option value="bank_transfer">Virement Bancaire</option>
                                <option value="check">Chèque</option>
                                <option value="credit_card">Carte de crédit</option>
                            </select>
                        </div>
                        <div id="spCheckSection" style="display:none;" class="mb-3">
                            <label class="form-label">Chèque *</label>
                            <select class="form-control" id="spPayCheckId">
                                <option value="">-- Chargement... --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de paiement *</label>
                            <input type="date" class="form-control" id="spPayDate" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (justificatif)</label>
                            <input type="file" class="form-control" id="spPayFile" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Max 5 MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="spPayNotes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" id="spPaySubmitBtn">
                        <i class="fas fa-paper-plane me-1"></i> Distribuer le paiement
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Allocation Result Modal ────────────────────────────────────────── --}}
    <div class="modal fade" id="allocationResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Paiement distribué</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="allocationMessage" class="fw-bold text-success"></p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>N° Achat</th><th class="text-end">Montant alloué</th></tr>
                            </thead>
                            <tbody id="allocationBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .card-header-custom { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); border-bottom:0; }
        .badge { font-size:.75rem; padding:.35rem .65rem; }
        .table td { vertical-align:middle; }
        .select2-container--default .select2-selection--single { height:38px; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
    let table;
    let currentSupplierId = null;

    $(document).ready(function () {

        // ── Select2 ──────────────────────────────────────────────────────────
        $('.select2').select2({ language: 'fr', placeholder: 'Sélectionner...', allowClear: true });

        // ── Toggle filters ────────────────────────────────────────────────────
        $('#filterBtn').click(function () { $('#filtersSection').slideToggle(); });
        $('#applyFilters').click(function () { table.ajax.reload(); });

        // ── DataTable (grouped by supplier) ───────────────────────────────────
        table = $('#purchasesTable').DataTable({ paging: false, lengthChange: false, 
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('raw-material-purchases.index') }}",
                data: function (d) {
                    d.supplier_id    = $('#filterSupplier').val();
                    d.payment_status = $('#filterStatus').val();
                    d.date_from      = $('#dateFrom').val();
                    d.date_to        = $('#dateTo').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex',        name: 'DT_RowIndex',   orderable: false, searchable: false, className: 'text-center' },
                { data: 'supplier_name',       name: 'supplier_name' },
                { data: 'purchases_count',     name: 'purchases_count', className: 'text-center' },
                { data: 'total_amount_display', name: 'total_amount',  className: 'text-end' },
                { data: 'total_paid_display',  name: 'total_paid',    className: 'text-end' },
                { data: 'total_rest_display',  name: 'total_rest',    orderable: false, className: 'text-end' },
                { data: 'balance_display',     name: 'balance',       orderable: false, className: 'text-end' },
                { data: 'status_summary',      name: 'status_summary', orderable: false, className: 'text-center' },
                { data: 'action',              name: 'action',        orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            pageLength: 25
        });

        // ── View supplier purchases (modal) ────────────────────────────────────
        $(document).on('click', '.view-supplier-btn', function () {
            currentSupplierId = $(this).data('id');
            var name          = $(this).data('name');

            $('#supplierPurchasesTitle').html('<i class="fas fa-list me-2"></i>Achats : ' + name);
            $('#spLoading').show();
            $('#spContent').hide();
            $('#spBody,#spFoot,#spSummary').empty();
            $('#supplierPurchasesModal').modal('show');

            $.get("{{ url('raw-material-purchases/supplier') }}/" + currentSupplierId + "/purchases", {
                payment_status: $('#filterStatus').val(),
                date_from:      $('#dateFrom').val(),
                date_to:        $('#dateTo').val()
            }, function (res) {
                if (res.success) renderPurchaseList(res.data);
                else showToast('error', 'Erreur lors du chargement');
            }).fail(function () { showToast('error', 'Erreur réseau'); });
        });

        function renderPurchaseList(purchases) {
            var tbody = $('#spBody'), tfoot = $('#spFoot'), summary = $('#spSummary');
            tbody.empty(); tfoot.empty(); summary.empty();

            if (!purchases.length) {
                tbody.append('<tr><td colspan="8" class="text-center text-muted py-3">Aucun achat trouvé</td></tr>');
                $('#spLoading').hide(); $('#spContent').show();
                return;
            }

            var totAmt = 0, totPaid = 0, totRest = 0;
            purchases.forEach(function (p, i) {
                totAmt  += p.final_amount;
                totPaid += p.total_paid;
                totRest += p.rest_amount;

                tbody.append(`
                    <tr>
                        <td class="text-center">${i + 1}</td>
                        <td>${p.purchase_date}</td>
                        <td><strong>${p.purchase_number}</strong></td>
                        <td class="text-end">${p.final_amount_display} DH</td>
                        <td class="text-end text-success fw-bold">${p.total_paid_display} DH</td>
                        <td class="text-end ${p.rest_class} fw-bold">${p.rest_amount_display} DH</td>
                        <td class="text-center">${p.payment_status_label}</td>
                        <td class="text-center">
                            <a href="${p.show_url}" class="btn btn-sm btn-info" title="Voir détails">
                                <i class="fas fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                `);
            });

            tfoot.append(`
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Totaux</td>
                    <td class="text-end">${totAmt.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</td>
                    <td class="text-end text-success">${totPaid.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</td>
                    <td class="text-end ${totRest>0?'text-warning':'text-success'}">${totRest.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</td>
                    <td colspan="2"></td>
                </tr>
            `);

            summary.html(`
                <div class="col-md-4"><div class="card border-primary mb-0"><div class="card-body py-2 px-3">
                    <small class="text-muted">Total Achats (${purchases.length})</small>
                    <div class="fw-bold text-primary">${totAmt.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</div>
                </div></div></div>
                <div class="col-md-4"><div class="card border-success mb-0"><div class="card-body py-2 px-3">
                    <small class="text-muted">Total Payé</small>
                    <div class="fw-bold text-success">${totPaid.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</div>
                </div></div></div>
                <div class="col-md-4"><div class="card border-${totRest>0?'danger':'success'} mb-0"><div class="card-body py-2 px-3">
                    <small class="text-muted">Reste à Payer</small>
                    <div class="fw-bold text-${totRest>0?'danger':'success'}">${totRest.toLocaleString('de-DE',{minimumFractionDigits:2})} DH</div>
                </div></div></div>
            `);

            $('#spLoading').hide(); $('#spContent').show();
        }

        // ── Open payment modal ─────────────────────────────────────────────────
        $(document).on('click', '.pay-supplier-btn', function () {
            var id   = $(this).data('id');
            var name = $(this).data('name');
            var rest = parseFloat($(this).data('rest'));

            currentSupplierId = id;
            $('#spPaySupplierId').val(id);
            $('#spPaySupplierName').text('Fournisseur : ' + name);
            $('#spPayRestTotal').text(rest.toLocaleString('de-DE', {minimumFractionDigits: 2}));
            $('#spPayAmount').val(rest.toFixed(2));
            $('#spPayMethod').val('');
            $('#spCheckSection').hide();
            $('#spPayFile').val('');
            $('#spPayNotes').val('');
            $('#supplierPaymentModal').modal('show');
        });

        // Show/hide check dropdown when method = check
        $('#spPayMethod').on('change', function () {
            if ($(this).val() === 'check') {
                loadChecksForSelect();
                $('#spCheckSection').show();
            } else {
                $('#spCheckSection').hide();
            }
        });

        function loadChecksForSelect() {
            $('#spPayCheckId').html('<option value="">Chargement...</option>');
            $.get("{{ route('raw-material-purchases.available-checks') }}", function (res) {
                if (res.success && res.data.length) {
                    var opts = '<option value="">-- Choisir un chèque --</option>';
                    res.data.forEach(function (c) {
                        opts += `<option value="${c.check_id}">N°${c.check_number} – ${c.bank_name} – Disponible: ${parseFloat(c.available_amount).toFixed(2)} DH</option>`;
                    });
                    $('#spPayCheckId').html(opts);
                } else {
                    $('#spPayCheckId').html('<option value="">Aucun chèque disponible</option>');
                }
            });
        }

        // ── Submit distributed payment ──────────────────────────────────────────
        $('#spPaySubmitBtn').click(function () {
            var amount  = parseFloat($('#spPayAmount').val());
            var method  = $('#spPayMethod').val();
            var date    = $('#spPayDate').val();
            var suppId  = $('#spPaySupplierId').val();

            if (!amount || amount <= 0) { showToast('error', 'Montant invalide'); return; }
            if (!method)               { showToast('error', 'Choisissez un mode de paiement'); return; }
            if (!date)                 { showToast('error', 'Date requise'); return; }
            if (method === 'check' && !$('#spPayCheckId').val()) {
                showToast('error', 'Sélectionnez un chèque'); return;
            }

            var fd = new FormData();
            fd.append('_token',         '{{ csrf_token() }}');
            fd.append('amount',         amount);
            fd.append('payment_method', method);
            fd.append('payment_date',   date);
            fd.append('notes',          $('#spPayNotes').val());
            if (method === 'check') fd.append('check_id', $('#spPayCheckId').val());
            var fileInput = $('#spPayFile')[0].files[0];
            if (fileInput) fd.append('payment_file', fileInput);

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Traitement...');

            $.ajax({
                url:         "{{ url('raw-material-purchases/supplier') }}/" + suppId + "/distribute-payment",
                type:        'POST',
                data:        fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Distribuer le paiement');
                    if (res.success) {
                        $('#supplierPaymentModal').modal('hide');
                        table.ajax.reload();

                        // Show allocation result
                        $('#allocationMessage').text(res.message);
                        var tbody = $('#allocationBody').empty();
                        res.allocations.forEach(function (a) {
                            tbody.append(`<tr><td>${a.purchase_number}</td><td class="text-end fw-bold">${a.amount} DH</td></tr>`);
                        });
                        $('#allocationResultModal').modal('show');
                    } else {
                        showToast('error', res.message);
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Distribuer le paiement');
                    showToast('error', xhr.responseJSON?.message || 'Erreur lors du paiement');
                }
            });
        });

        // Reset supplier payment modal on close
        $('#supplierPaymentModal').on('hidden.bs.modal', function () {
            $('#spPayMethod').val('');
            $('#spCheckSection').hide();
            $('#spPayFile').val('');
            $('#spPayNotes').val('');
        });
        $('#supplierPurchasesModal').on('hidden.bs.modal', function () { currentSupplierId = null; });
    });

    function showToast(type, message) {
        var toast = $('<div class="toast align-items-center text-white bg-' +
            (type === 'success' ? 'success' : 'danger') +
            ' border-0" role="alert">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div>');
        $('#toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        setTimeout(function () { toast.remove(); }, 6000);
    }
    </script>
@endpush
