@extends('layouts.app')

@section('title', 'Nouvel Achat')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Commande d'Achat</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none"
                                        href="{{ route('raw-material-purchases.index') }}">
                                        Achats
                                    </a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">
                                    <span class="badge fw-medium fs-2 bg-primary text-primary">
                                        Nouveau
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
                            <i class="fas fa-plus-circle me-2"></i>Créer une Nouvelle Commande d'Achat
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="purchaseForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="purchase_number" name="purchase_number" value="{{ $purchaseNumber }}">

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">N° Commande</label>
                                        <input type="text" class="form-control" id="display_purchase_number"
                                            value="{{ $purchaseNumber }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Fournisseur *</label>
                                        <select class="form-control select2" id="supplier_id" name="supplier_id" required>
                                            <option value="">Sélectionner un fournisseur</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->supplier_id }}">
                                                    {{ $supplier->company_name ?? $supplier->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Magasin *</label>
                                        <select class="form-control select2" id="magazine_id" name="magazine_id" required>
                                            <option value="">Sélectionner un magasin</option>
                                            @foreach ($magazines as $magazine)
                                                <option value="{{ $magazine->magazine_id }}">{{ $magazine->magazine_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Commande *</label>
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Livraison Prévue *</label>
                                        <input type="date" class="form-control" id="expected_delivery_date"
                                            name="expected_delivery_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6>Articles Commandés</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i> Ajouter un Article
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="itemsTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="15%">Type *</th>
                                                    <th width="52%">Matière / Description *</th>
                                                    <th width="18%">Total (DH) *</th>
                                                    <th width="10%">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsBody">
                                                <!-- Items will be added here dynamically -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Sous-total:</strong></td>
                                                    <td><strong id="subtotal">0.00 DH</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end">

                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="include_tva" name="include_tva" value="1"
                                                                checked>
                                                            <label class="form-check-label" for="include_tva">
                                                                <strong>TVA incluse</strong>
                                                            </label>
                                                        </div>
                                                        <span id="tva_badge" class="badge bg-success">TVA incluse</span>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end">Remise (%):</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control"
                                                                id="discount_percentage" name="discount_percentage"
                                                                value="0" min="0" max="100"
                                                                step="0.01">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <small class="text-muted">Montant: <span
                                                                id="discount_amount_display">0.00 DH</span></small>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total <span
                                                                id="total_label">TTC</span>:</strong></td>
                                                    <td><strong id="finalAmount">0.00 DH</strong></td>
                                                    <td>\n
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Documents Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-file-invoice me-2"></i>Documents de Paiement
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="paymentDocumentsContainer">
                                                <!-- Payment documents will be added here -->
                                            </div>

                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                id="addPaymentBtn" data-bs-toggle="modal"
                                                data-bs-target="#paymentMethodModal">
                                                <i class="fas fa-plus me-1"></i> Ajouter un Paiement
                                            </button>

                                            <div class="mt-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="alert alert-info">
                                                            <strong>Total Payé:</strong> <span id="totalPaid">0.00
                                                                DH</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="alert alert-warning">
                                                            <strong>Reste à Payer:</strong> <span id="remainingAmount">0.00
                                                                DH</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="alert" id="paymentStatusAlert">
                                                            <strong>Statut:</strong> <span id="paymentStatusDisplay">
                                                                <span class="badge bg-danger p-2">Non Payé</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes supplémentaires..."></textarea>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('raw-material-purchases.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Method Selection Modal -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Choisir le moyen de paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="cash">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Espèces
                        </button>
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="bank_transfer">
                            <i class="fas fa-university me-2 text-primary"></i> Virement Bancaire
                        </button>
                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="collapse"
                            data-bs-target="#checkOptions">
                            <i class="fas fa-money-check me-2 text-info"></i> Chèque
                            <i class="fas fa-chevron-down float-end"></i>
                        </button>
                        <div class="collapse" id="checkOptions">
                            <div class="list-group">
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="check" data-check-type="entreprise">
                                    <i class="fas fa-building me-2"></i> Chèque Entreprise
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="check" data-check-type="client">
                                    <i class="fas fa-user me-2"></i> Chèque Client
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="new_check_entreprise">
                                    <i class="fas fa-plus-circle me-2 text-success"></i> Nouveau Chèque Entreprise
                                </button>
                                <button type="button"
                                    class="list-group-item list-group-item-action payment-type-select ps-5"
                                    data-type="new_check_client">
                                    <i class="fas fa-plus-circle me-2 text-success"></i> Nouveau Chèque Client
                                </button>
                            </div>
                        </div>
                        <button type="button" class="list-group-item list-group-item-action payment-type-select"
                            data-type="credit_card">
                            <i class="fas fa-credit-card me-2 text-warning"></i> Carte de crédit
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Payment Modal -->
    <div class="modal fade" id="cashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paiement en Espèces</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cashForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="cash_amount" min="0.01" step="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de Paiement *</label>
                            <input type="date" class="form-control" id="cash_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (Reçu)</label>
                            <input type="file" class="form-control" id="cash_file" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Taille max: 5MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="cash_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bank Transfer Modal -->
    <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Virement Bancaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bankTransferForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="transfer_amount" min="0.01"
                                step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de Virement *</label>
                            <input type="date" class="form-control" id="transfer_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numéro de Transaction</label>
                            <input type="text" class="form-control" id="transfer_reference">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (Justificatif)</label>
                            <input type="file" class="form-control" id="transfer_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="transfer_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Check Selection Modal -->
    <div class="modal fade" id="checkSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkModalTitle">Sélectionner un Chèque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="selected_check_type">
                    <input type="hidden" id="selected_check_type_value">

                    <!-- Available Checks List -->
                    <div id="availableChecksList" style="display: none;">
                        <h6 id="availableChecksTitle">Chèques disponibles</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="checksTable">
                                <thead>
                                    <tr>
                                        <th>N° Chèque</th>
                                        <th>Banque</th>
                                        <th>Montant Total</th>
                                        <th>Montant Disponible</th>
                                        <th>Date d'Émission</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="checksBody">
                                    <!-- Checks will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Quick Add New Check Form -->
                    <div id="newCheckForm" style="display: none;">
                        <h6 id="newCheckTitle">Ajouter un nouveau chèque</h6>
                        <form id="quickCheckForm">
                            <input type="hidden" id="new_check_type" value="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de Chèque *</label>
                                    <input type="text" class="form-control" id="new_check_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Banque *</label>
                                    <input type="text" class="form-control" id="new_check_bank" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant *</label>
                                    <input type="number" class="form-control" id="new_check_amount" min="0.01"
                                        step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date d'Émission *</label>
                                    <input type="date" class="form-control" id="new_check_issue_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date d'Échéance *</label>
                                    <input type="date" class="form-control" id="new_check_due_date"
                                        value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Image du Chèque *</label>
                                    <input type="file" class="form-control" id="new_check_file"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">Taille max: 5MB</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Bénéficiaire</label>
                                    <input type="text" class="form-control" id="new_check_payee">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="showPaymentMethodModal()">Retour</button>
                    <button type="button" class="btn btn-success" id="saveNewCheckBtn"
                        style="display: none;">Enregistrer le chèque</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Card Modal -->
    <div class="modal fade" id="creditCardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Paiement par Carte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="creditCardForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Montant *</label>
                            <input type="number" class="form-control" id="card_amount" min="0.01" step="0.01"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de Transaction *</label>
                            <input type="date" class="form-control" id="card_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type de Carte</label>
                            <select class="form-control" id="card_type">
                                <option value="visa">Visa</option>
                                <option value="mastercard">Mastercard</option>
                                <option value="amex">American Express</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document (Reçu)</label>
                            <input type="file" class="form-control" id="card_file" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="card_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="showPaymentMethodModal()">Retour</button>
                        <button type="submit" class="btn btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .payment-document-row {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .payment-document-row.removing {
            opacity: 0;
            transform: translateX(-100%);
        }

        .check-badge {
            background-color: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .entreprise-check {
            border-left: 4px solid #007bff;
        }

        .client-check {
            border-left: 4px solid #28a745;
        }

        .payment-info {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .check-image-preview {
            max-height: 60px;
            max-width: 100px;
            cursor: pointer;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .check-image-preview:hover {
            opacity: 0.8;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        window.paymentFiles = {};
        window.checkImages = {};

        function showPaymentMethodModal() {
            $('.modal.show').each(function() {
                $(this).modal('hide');
            });

            setTimeout(function() {
                $('#paymentMethodModal').modal('show');
            }, 300);
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            // Add one default item row on page load
            addItemRow();

            // Add item button
            $('#addItemBtn').click(function() {
                addItemRow();
            });

            // Format number for display
            function formatNumber(number) {
                if (number === undefined || number === null || isNaN(number)) return '0.00';
                return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            // Parse formatted number back to float
            function parseNumber(formattedNumber) {
                if (!formattedNumber) return 0;
                var numberStr = formattedNumber.toString()
                    .replace(' DH', '')
                    .replace(/,/g, '')
                    .trim();
                var parsed = parseFloat(numberStr);
                return isNaN(parsed) ? 0 : parsed;
            }

            // Format date for display
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                var date = new Date(dateString);
                if (isNaN(date.getTime())) return 'N/A';
                return date.getDate().toString().padStart(2, '0') + '/' +
                    (date.getMonth() + 1).toString().padStart(2, '0') + '/' +
                    date.getFullYear();
            }

            // Calculate total paid from payment documents
            function calculateTotalPaid() {
                var total = 0;
                $('.payment-document-row').each(function() {
                    var amountText = $(this).find('.payment-amount-display').text();
                    total += parseNumber(amountText);
                });
                return total;
            }

            // Update payment status
            function updatePaymentStatus() {
                var orderTotal = parseNumber($('#finalAmount').text());
                var totalPaid = calculateTotalPaid();
                var remaining = orderTotal - totalPaid;

                $('#totalPaid').text(formatNumber(totalPaid) + ' DH');
                $('#remainingAmount').text(formatNumber(remaining) + ' DH');

                var statusHtml = '';
                var alertClass = '';

                if (orderTotal === 0) {
                    statusHtml = '<span class="badge bg-warning p-2">Non déterminé</span>';
                    alertClass = 'alert-warning';
                } else if (totalPaid <= 0) {
                    statusHtml = '<span class="badge bg-danger p-2">Non Payé</span>';
                    alertClass = 'alert-danger';
                } else if (totalPaid >= orderTotal - 0.01) {
                    statusHtml = '<span class="badge bg-success p-2">Payé</span>';
                    alertClass = 'alert-success';
                } else {
                    var percentage = ((totalPaid / orderTotal) * 100).toFixed(1);
                    statusHtml = '<span class="badge bg-info p-2">Avance (' + percentage + '%)</span>';
                    alertClass = 'alert-info';
                }

                $('#paymentStatusDisplay').html(statusHtml);

                var alert = $('#paymentStatusAlert');
                alert.removeClass('alert-danger alert-success alert-info alert-warning');
                alert.addClass(alertClass);
            }

            // Get payment method label
            function getPaymentMethodLabel(method, data) {
                switch (method) {
                    case 'cash':
                        return 'Espèces';
                    case 'bank_transfer':
                        return 'Virement Bancaire' + (data.reference ? ' (Ref: ' + data.reference + ')' : '');
                    case 'check':
                        var checkTypeText = data.check_type === 'client' ? 'Client' : 'Entreprise';
                        return 'Chèque ' + checkTypeText + ' N° ' + (data.check_number || '');
                    case 'credit_card':
                        return 'Carte de crédit' + (data.card_type ? ' (' + data.card_type + ')' : '');
                    default:
                        return method;
                }
            }

            // Get payment method icon
            function getPaymentMethodIcon(method) {
                switch (method) {
                    case 'cash':
                        return 'fa-money-bill-wave';
                    case 'bank_transfer':
                        return 'fa-university';
                    case 'check':
                        return 'fa-money-check';
                    case 'credit_card':
                        return 'fa-credit-card';
                    default:
                        return 'fa-file-invoice';
                }
            }

            // Function to add payment document row with image preview
            function addPaymentDocumentRow(paymentData) {
                var rowId = 'payment_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                var methodLabel = getPaymentMethodLabel(paymentData.payment_method, paymentData);
                var methodIcon = getPaymentMethodIcon(paymentData.payment_method);
                var checkClass = paymentData.check_type === 'client' ? 'client-check' : (paymentData
                    .payment_method === 'check' ? 'entreprise-check' : '');

                // Create image preview if file exists
                var imageHtml = '';

                // Check if we have a file in paymentFiles
                if (paymentData.has_file && window.paymentFiles[paymentData.temp_id]) {
                    var file = window.paymentFiles[paymentData.temp_id];

                    // Create a FileReader to generate preview
                    var reader = new FileReader();

                    // Create a placeholder that will be updated when file is read
                    imageHtml = `
                    <div class="mt-2" id="preview-${paymentData.temp_id}">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <small class="d-block text-muted">Chargement de l'image...</small>
                        </div>
                    </div>
                `;

                    // Read the file and update the preview
                    reader.onload = function(e) {
                        var previewHtml = '';
                        if (file.type.startsWith('image/')) {
                            previewHtml = `
                            <img src="${e.target.result}" alt="Document" class="img-thumbnail" style="max-height: 100px; cursor: pointer;"
                                 onclick="window.open('${e.target.result}', '_blank')">
                            <small class="d-block text-muted">${file.name}</small>
                        `;
                        } else {
                            previewHtml = `
                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            <small class="d-block text-muted">${file.name}</small>
                        `;
                        }
                        $(`#preview-${paymentData.temp_id}`).html(previewHtml);
                    };
                    reader.readAsDataURL(file);
                }
                // Check if we have a check image URL
                else if (paymentData.check_image_url) {
                    imageHtml = `
                    <div class="mt-2">
                        <img src="${paymentData.check_image_url}" alt="Image du chèque" class="check-image-preview"
                             onclick="window.open('${paymentData.check_image_url}', '_blank')">
                        <small class="d-block text-muted">Image du chèque</small>
                    </div>
                `;
                } else if (paymentData.filename) {
                    imageHtml = `
                    <div class="mt-2">
                        <i class="fas fa-file fa-2x text-muted"></i>
                        <small class="d-block text-muted">${paymentData.filename}</small>
                    </div>
                `;
                }

                var row = `
                <div class="payment-document-row border rounded p-3 mb-3 bg-light ${checkClass}"
                     id="${rowId}"
                     data-temp-id="${paymentData.temp_id || ''}"
                     data-check-id="${paymentData.check_id || ''}">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start">
                                <i class="fas ${methodIcon} me-2 fs-5 mt-1"></i>
                                <div>
                                    <strong>${methodLabel}</strong>
                                    <div class="payment-info">
                                        Montant: <span class="payment-amount-display">${formatNumber(paymentData.amount)} DH</span>
                                        <br>Date: ${formatDate(paymentData.payment_date)}
                                        ${paymentData.notes ? '<br>Notes: ' + paymentData.notes : ''}
                                        ${paymentData.filename ? '<br>Document: ' + paymentData.filename : ''}
                                        ${paymentData.check_number ? '<br>N° Chèque: ' + paymentData.check_number : ''}
                                    </div>
                                    ${imageHtml}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-sm btn-danger remove-payment-document">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

                $('#paymentDocumentsContainer').append(row);

                // Add remove functionality
                $('#' + rowId + ' .remove-payment-document').click(function() {
                    var tempId = $(this).closest('.payment-document-row').data('temp-id');
                    if (tempId && window.paymentFiles[tempId]) {
                        delete window.paymentFiles[tempId];
                    }
                    $('#' + rowId).addClass('removing');
                    setTimeout(function() {
                        $('#' + rowId).remove();
                        updatePaymentStatus();
                    }, 300);
                });

                updatePaymentStatus();
                return rowId;
            }

            // Payment type selection
            $('.payment-type-select').click(function() {
                var type = $(this).data('type');
                var checkType = $(this).data('check-type');

                $('#paymentMethodModal').modal('hide');

                switch (type) {
                    case 'cash':
                        $('#cashModal').modal('show');
                        break;
                    case 'bank_transfer':
                        $('#bankTransferModal').modal('show');
                        break;
                    case 'check':
                        $('#availableChecksList').show();
                        $('#newCheckForm').hide();
                        $('#saveNewCheckBtn').hide();

                        $('#selected_check_type').val(checkType);
                        $('#selected_check_type_value').val(checkType);

                        if (checkType === 'entreprise') {
                            $('#checkModalTitle').text('Sélectionner un chèque Entreprise');
                            $('#availableChecksTitle').text('Chèques entreprise disponibles');
                        } else {
                            $('#checkModalTitle').text('Sélectionner un chèque Client');
                            $('#availableChecksTitle').text('Chèques client disponibles');
                        }

                        loadAvailableChecks(checkType);

                        $('#checkSelectionModal').modal('show');
                        break;
                    case 'new_check_entreprise':
                        $('#availableChecksList').hide();
                        $('#newCheckForm').show();
                        $('#saveNewCheckBtn').show();

                        $('#selected_check_type').val('new');
                        $('#selected_check_type_value').val('entreprise');
                        $('#new_check_type').val('entreprise');
                        $('#checkModalTitle').text('Ajouter un nouveau chèque Entreprise');
                        $('#newCheckTitle').text('Ajouter un nouveau chèque Entreprise');

                        $('#quickCheckForm')[0].reset();
                        $('#new_check_issue_date').val(new Date().toISOString().split('T')[0]);

                        var dueDate = new Date();
                        dueDate.setDate(dueDate.getDate() + 30);
                        $('#new_check_due_date').val(dueDate.toISOString().split('T')[0]);

                        $('#checkSelectionModal').modal('show');
                        break;
                    case 'new_check_client':
                        $('#availableChecksList').hide();
                        $('#newCheckForm').show();
                        $('#saveNewCheckBtn').show();

                        $('#selected_check_type').val('new');
                        $('#selected_check_type_value').val('client');
                        $('#new_check_type').val('client');
                        $('#checkModalTitle').text('Ajouter un nouveau chèque Client');
                        $('#newCheckTitle').text('Ajouter un nouveau chèque Client');

                        $('#quickCheckForm')[0].reset();
                        $('#new_check_issue_date').val(new Date().toISOString().split('T')[0]);

                        var dueDate = new Date();
                        dueDate.setDate(dueDate.getDate() + 30);
                        $('#new_check_due_date').val(dueDate.toISOString().split('T')[0]);

                        $('#checkSelectionModal').modal('show');
                        break;
                    case 'credit_card':
                        $('#creditCardModal').modal('show');
                        break;
                }
            });

            // Cash form submit
            $('#cashForm').submit(function(e) {
                e.preventDefault();

                var amount = parseFloat($('#cash_amount').val());

                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Veuillez saisir un montant valide');
                    return;
                }

                var fileInput = $('#cash_file')[0].files[0];

                var tempId = 'cash_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                var paymentDoc = {
                    temp_id: tempId,
                    payment_method: 'cash',
                    amount: amount,
                    payment_date: $('#cash_date').val(),
                    notes: $('#cash_notes').val(),
                    filename: fileInput ? fileInput.name : null,
                    has_file: !!fileInput
                };

                if (fileInput) {
                    window.paymentFiles[tempId] = fileInput;
                }

                addPaymentDocumentRow(paymentDoc);
                $('#cashModal').modal('hide');
                $('#cashForm')[0].reset();
            });

            // Bank transfer form submit
            $('#bankTransferForm').submit(function(e) {
                e.preventDefault();

                var amount = parseFloat($('#transfer_amount').val());

                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Veuillez saisir un montant valide');
                    return;
                }

                var fileInput = $('#transfer_file')[0].files[0];

                var tempId = 'transfer_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                var paymentDoc = {
                    temp_id: tempId,
                    payment_method: 'bank_transfer',
                    amount: amount,
                    payment_date: $('#transfer_date').val(),
                    reference: $('#transfer_reference').val(),
                    notes: $('#transfer_notes').val(),
                    filename: fileInput ? fileInput.name : null,
                    has_file: !!fileInput
                };

                if (fileInput) {
                    window.paymentFiles[tempId] = fileInput;
                }

                addPaymentDocumentRow(paymentDoc);
                $('#bankTransferModal').modal('hide');
                $('#bankTransferForm')[0].reset();
            });

            // Credit card form submit
            $('#creditCardForm').submit(function(e) {
                e.preventDefault();

                var amount = parseFloat($('#card_amount').val());

                if (isNaN(amount) || amount <= 0) {
                    showToast('error', 'Veuillez saisir un montant valide');
                    return;
                }

                var fileInput = $('#card_file')[0].files[0];

                var tempId = 'card_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                var paymentDoc = {
                    temp_id: tempId,
                    payment_method: 'credit_card',
                    amount: amount,
                    payment_date: $('#card_date').val(),
                    card_type: $('#card_type').val(),
                    notes: $('#card_notes').val(),
                    filename: fileInput ? fileInput.name : null,
                    has_file: !!fileInput
                };

                if (fileInput) {
                    window.paymentFiles[tempId] = fileInput;
                }

                addPaymentDocumentRow(paymentDoc);
                $('#creditCardModal').modal('hide');
                $('#creditCardForm')[0].reset();
            });

            // Load available checks
            function loadAvailableChecks(type) {
                $('#checksBody').html('<tr><td colspan="6" class="text-center">Chargement...</td></tr>');

                $.ajax({
                    url: "{{ route('raw-material-purchases.available-checks') }}",
                    type: "GET",
                    data: {
                        type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            displayChecks(response.data, type);
                        } else {
                            $('#checksBody').html(
                                '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        $('#checksBody').html(
                            '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des chèques</td></tr>'
                        );
                        showToast('error', 'Erreur lors du chargement des chèques');
                    }
                });
            }

            // Display checks in table
            function displayChecks(checks, type) {
                var tbody = $('#checksBody');
                tbody.empty();
                var checkClass = type === 'client' ? 'client-check' : 'entreprise-check';

                if (checks.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">Aucun chèque disponible</td></tr>');
                } else {
                    checks.forEach(function(check) {
                        var row = `
                        <tr class="${checkClass}">
                            <td>${check.check_number || 'N/A'}</td>
                            <td>${check.bank_name || 'N/A'}</td>
                            <td>${formatNumber(check.amount)} DH</td>
                            <td>${formatNumber(check.available_amount)} DH</td>
                            <td>${check.issue_date ? formatDate(check.issue_date) : 'N/A'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary select-check"
                                        data-check-id="${check.check_id}"
                                        data-check-number="${check.check_number || 'N/A'}"
                                        data-amount="${check.amount}"
                                        data-available="${check.available_amount}"
                                        data-check-type="${type}"
                                        data-check-image="${check.check_image || ''}">
                                    Utiliser ce chèque
                                </button>
                            </td>
                        </tr>
                    `;
                        tbody.append(row);
                    });
                }

                bindSelectCheckEvent();
            }

            // Bind select check event
            function bindSelectCheckEvent() {
                $('.select-check').off('click').on('click', function() {
                    var checkId = $(this).data('check-id');
                    var checkNumber = $(this).data('check-number');
                    var availableAmount = $(this).data('available');
                    var checkType = $(this).data('check-type');
                    var checkImage = $(this).data('check-image');
                    var orderTotal = parseNumber($('#finalAmount').text());
                    var totalPaid = calculateTotalPaid();
                    var remaining = orderTotal - totalPaid;

                    var amountToAllocate = Math.min(availableAmount, Math.max(0, remaining));

                    if (amountToAllocate <= 0) {
                        showToast('error', 'Le reste à payer est de 0 DH');
                        return;
                    }

                    $('#checkSelectionModal').modal('hide');

                    var tempId = 'check_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                    var paymentDoc = {
                        temp_id: tempId,
                        payment_method: 'check',
                        check_id: checkId,
                        check_number: checkNumber,
                        check_type: checkType,
                        amount: amountToAllocate,
                        payment_date: new Date().toISOString().split('T')[0],
                        notes: 'Utilisation automatique du chèque',
                        check_image_url: checkImage ? '/storage/' + checkImage : null,
                        has_file: false
                    };

                    addPaymentDocumentRow(paymentDoc);
                    showToast('success', 'Chèque alloué avec succès (Montant: ' + formatNumber(
                        amountToAllocate) + ' DH)');
                });
            }

            // Save new check
            $('#saveNewCheckBtn').click(function() {
                var fileInput = $('#new_check_file')[0].files[0];
                var checkType = $('#new_check_type').val();

                if (!fileInput) {
                    showToast('error', 'Veuillez sélectionner une image du chèque');
                    return;
                }

                var formData = new FormData();
                formData.append('check_type', checkType);
                formData.append('check_number', $('#new_check_number').val());
                formData.append('bank_name', $('#new_check_bank').val());
                formData.append('amount', $('#new_check_amount').val());
                formData.append('issue_date', $('#new_check_issue_date').val());
                formData.append('due_date', $('#new_check_due_date').val());
                formData.append('deposit_date', new Date().toISOString().split('T')[0]);
                formData.append('payee', $('#new_check_payee').val());
                formData.append('check_file', fileInput);
                formData.append('_token', '{{ csrf_token() }}');

                if (!formData.get('check_number') || !formData.get('bank_name') || !formData.get(
                    'amount') ||
                    !formData.get('issue_date') || !formData.get('due_date')) {
                    showToast('error', 'Veuillez remplir tous les champs obligatoires');
                    return;
                }

                if (parseFloat(formData.get('amount')) <= 0) {
                    showToast('error', 'Le montant doit être supérieur à 0');
                    return;
                }

                $.ajax({
                    url: "{{ route('checks.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', 'Chèque ajouté avec succès');
                            $('#checkSelectionModal').modal('hide');

                            var orderTotal = parseNumber($('#finalAmount').text());
                            var totalPaid = calculateTotalPaid();
                            var remaining = orderTotal - totalPaid;
                            var amountToAllocate = Math.min(response.amount, Math.max(0,
                                remaining));

                            if (amountToAllocate <= 0) {
                                showToast('error', 'Le reste à payer est de 0 DH');
                                return;
                            }

                            var tempId = 'check_' + Date.now() + '_' + Math.random().toString(
                                36).substr(2, 9);

                            window.paymentFiles[tempId] = fileInput;

                            var paymentDoc = {
                                temp_id: tempId,
                                payment_method: 'check',
                                check_id: response.check_id,
                                check_number: response.check_number,
                                check_type: checkType,
                                amount: amountToAllocate,
                                payment_date: new Date().toISOString().split('T')[0],
                                notes: 'Nouveau chèque créé',
                                filename: fileInput.name,
                                has_file: true
                            };

                            addPaymentDocumentRow(paymentDoc);
                            showToast('success',
                                'Chèque créé et alloué avec succès (Montant: ' +
                                formatNumber(amountToAllocate) + ' DH)');
                        } else {
                            showToast('error', response.message ||
                                'Erreur lors de l\'ajout du chèque');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Erreur lors de l\'ajout du chèque';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showToast('error', errorMessage);
                    }
                });
            });

            // Function to add item row
            function addItemRow(item = {}) {
                var rowCount = $('#itemsBody tr').length;
                var rowId = 'item_' + Date.now() + '_' + rowCount;
                var itemType = item.item_type || 'raw_material';

                var row = `
                <tr id="${rowId}">
                    <td class="row-number">${rowCount + 1}</td>
                    <td>
                        <select class="form-control form-control-sm item-type-select" name="items[${rowCount}][item_type]" required>
                            <option value="raw_material" ${itemType === 'raw_material' ? 'selected' : ''}>Matière Première</option>
                            <option value="charge_diverse" ${itemType === 'charge_diverse' ? 'selected' : ''}>Charges Diverses</option>
                        </select>
                    </td>
                    <td class="detail-cell">
                        <select class="form-control form-control-sm material-select" name="items[${rowCount}][material_id]">
                            <option value="">Sélectionner</option>
                            @foreach ($materials as $material)
                                <option value="{{ $material->material_id }}"
                                        data-unit="{{ $material->unit_of_measure }}">
                                    {{ $material->material_name }} ({{ $material->material_code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="row g-1 mt-1 qty-price-group">
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm quantity"
                                       name="items[${rowCount}][quantity]"
                                       value="${item.quantity || ''}"
                                       placeholder="Quantité" min="0.01" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm unit-price"
                                       name="items[${rowCount}][unit_price]"
                                       value="${item.unit_price || ''}"
                                       placeholder="Prix Unitaire (DH)" min="0" step="0.01">
                            </div>
                        </div>
                        <input type="text" class="form-control form-control-sm description-input d-none mt-1"
                               name="items[${rowCount}][description]"
                               placeholder="Charges diverses"
                               value="${item.description || ''}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-total-input"
                               name="items[${rowCount}][total_price]"
                               value="${item.total_price || ''}"
                               min="0.01" step="0.01" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#itemsBody').append(row);

                $('#' + rowId + ' .material-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner...",
                    width: '100%'
                });
                if (item.material_id) {
                    $('#' + rowId + ' .material-select').val(item.material_id).trigger('change');
                }

                applyItemTypeUI(rowId);
                calculateItemTotal(rowId);
                updateTotals();

                $('#' + rowId + ' .item-type-select').on('change', function() {
                    applyItemTypeUI(rowId);
                    calculateItemTotal(rowId);
                    updateTotals();
                });

                $('#' + rowId + ' .quantity, #' + rowId + ' .unit-price').on('input', function() {
                    calculateItemTotal(rowId);
                    updateTotals();
                });

                $('#' + rowId + ' .item-total-input').on('input', function() {
                    updateTotals();
                });

                $('#' + rowId + ' .remove-item').click(function() {
                    $('#' + rowId).remove();
                    updateRowNumbers();
                    updateTotals();
                });
            }

            // Show/hide fields based on the selected item type. Quantité and Prix
            // Unitaire are not separate table columns — they're compact inputs nested
            // inside the Matière/Description cell, so toggling them never affects the
            // table's column layout.
            function applyItemTypeUI(rowId) {
                var $row = $('#' + rowId);
                var type = $row.find('.item-type-select').val();
                var $material = $row.find('.material-select');
                var $qtyPriceGroup = $row.find('.qty-price-group');
                var $description = $row.find('.description-input');
                var $total = $row.find('.item-total-input');

                if (type === 'charge_diverse') {
                    $material.next('.select2-container').addClass('d-none');
                    $qtyPriceGroup.addClass('d-none');
                    $row.find('.quantity').prop('required', false).val('');
                    $row.find('.unit-price').prop('required', false).val('');
                    $description.removeClass('d-none').prop('required', true);
                    if (!$description.val()) {
                        $description.val('Charges diverses');
                    }
                    $total.prop('readonly', false).prop('required', true);
                } else {
                    $material.next('.select2-container').removeClass('d-none');
                    $qtyPriceGroup.removeClass('d-none');
                    $row.find('.quantity').prop('required', true);
                    $row.find('.unit-price').prop('required', true);
                    $description.addClass('d-none').prop('required', false).val('');
                    $total.prop('readonly', true).prop('required', true);
                }
            }

            // Calculate item total
            function calculateItemTotal(rowId) {
                var $row = $('#' + rowId);
                if ($row.find('.item-type-select').val() === 'charge_diverse') {
                    return;
                }
                var quantity = parseFloat($row.find('.quantity').val()) || 0;
                var unitPrice = parseFloat($row.find('.unit-price').val()) || 0;
                var total = quantity * unitPrice;
                $row.find('.item-total-input').val(total.toFixed(2));
            }

            // Update row numbers
            function updateRowNumbers() {
                $('#itemsBody tr').each(function(index) {
                    $(this).find('.row-number').text(index + 1);
                    $(this).find('select, input').each(function() {
                        var name = $(this).attr('name');
                        if (name) {
                            var newName = name.replace(/items\[\d+\]/g, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
            }

            // Update totals
            function updateTotals() {
                var subtotal = 0;

                $('.item-total-input').each(function() {
                    var total = parseFloat($(this).val()) || 0;
                    subtotal += total;
                });

                var discountPercentage = parseFloat($('#discount_percentage').val()) || 0;
                var includeTva = $('#include_tva').is(':checked');

                var discountAmount = (subtotal * discountPercentage) / 100;
                var afterDiscount = subtotal - discountAmount;
                var finalAmount = afterDiscount;

                $('#subtotal').text(formatNumber(subtotal) + ' DH');
                $('#discount_amount_display').text(formatNumber(discountAmount) + ' DH');

                if (includeTva) {
                    $('#tva_badge').text('TVA incluse').removeClass('bg-secondary').addClass('bg-success');
                    $('#total_label').text('TTC');
                } else {
                    $('#tva_badge').text('TVA non incluse').removeClass('bg-success').addClass('bg-secondary');
                    $('#total_label').text('HT');
                }

                $('#finalAmount').text(formatNumber(finalAmount) + ' DH');

                updatePaymentStatus();
            }

            $('#include_tva').on('change', updateTotals);
            $('#discount_percentage').on('input', updateTotals);

            $('#checkSelectionModal').on('hidden.bs.modal', function() {
                $('#availableChecksList').hide();
                $('#newCheckForm').hide();
                $('#saveNewCheckBtn').hide();
            });

            $('#cashModal').on('hidden.bs.modal', function() {
                $('#cashForm')[0].reset();
            });

            $('#bankTransferModal').on('hidden.bs.modal', function() {
                $('#bankTransferForm')[0].reset();
            });

            $('#creditCardModal').on('hidden.bs.modal', function() {
                $('#creditCardForm')[0].reset();
            });

            // Purchase Form Submit
            $('#purchaseForm').submit(function(e) {
                e.preventDefault();

                if ($('#itemsBody tr').length === 0) {
                    showToast('error', 'Veuillez ajouter au moins un article');
                    return;
                }

                if (!$('#supplier_id').val()) {
                    showToast('error', 'Veuillez sélectionner un fournisseur');
                    return;
                }

                if (!$('#magazine_id').val()) {
                    showToast('error', 'Veuillez sélectionner un magasin');
                    return;
                }

                var formData = new FormData();

                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                formData.append('purchase_number', $('#purchase_number').val());
                formData.append('supplier_id', $('#supplier_id').val());
                formData.append('magazine_id', $('#magazine_id').val());
                formData.append('purchase_date', $('#purchase_date').val());
                formData.append('expected_delivery_date', $('#expected_delivery_date').val());
                formData.append('include_tva', $('#include_tva').is(':checked') ? 1 : 0);
                formData.append('discount_percentage', $('#discount_percentage').val());
                formData.append('notes', $('#notes').val());

                var orderTotal = parseNumber($('#finalAmount').text());
                var totalPaid = calculateTotalPaid();
                var paymentStatus = 'pending';

                if (totalPaid <= 0) {
                    paymentStatus = 'pending';
                } else if (totalPaid >= orderTotal - 0.01) {
                    paymentStatus = 'paid';
                } else {
                    paymentStatus = 'partial';
                }

                formData.append('payment_status', paymentStatus);

                var items = [];
                var validItems = true;

                $('#itemsBody tr').each(function(index) {
                    var itemType = $(this).find('.item-type-select').val();
                    var totalPrice = $(this).find('.item-total-input').val();

                    if (itemType === 'charge_diverse') {
                        var description = $.trim($(this).find('.description-input').val());

                        if (!description) {
                            showToast('error',
                                'Veuillez saisir une description pour la charge diverse ' + (
                                    index + 1));
                            validItems = false;
                            return false;
                        }

                        if (!totalPrice || totalPrice <= 0) {
                            showToast('error',
                                'Veuillez saisir un prix total valide pour la charge diverse ' + (
                                    index + 1));
                            validItems = false;
                            return false;
                        }

                        items.push({
                            item_type: 'charge_diverse',
                            description: description,
                            total_price: totalPrice
                        });
                    } else {
                        var materialId = $(this).find('.material-select').val();
                        var quantity = $(this).find('.quantity').val();
                        var unitPrice = $(this).find('.unit-price').val();

                        if (!materialId) {
                            showToast('error',
                                'Veuillez sélectionner une matière première pour l\'article ' + (
                                    index + 1));
                            validItems = false;
                            return false;
                        }

                        if (!quantity || quantity <= 0) {
                            showToast('error', 'Veuillez saisir une quantité valide pour l\'article ' +
                                (index + 1));
                            validItems = false;
                            return false;
                        }

                        if (!unitPrice || unitPrice <= 0) {
                            showToast('error',
                                'Veuillez saisir un prix unitaire valide pour l\'article ' + (
                                    index + 1));
                            validItems = false;
                            return false;
                        }

                        items.push({
                            item_type: 'raw_material',
                            material_id: materialId,
                            quantity: quantity,
                            unit_price: unitPrice,
                            notes: ''
                        });
                    }
                });

                if (!validItems) return;

                formData.append('items', JSON.stringify(items));

                var paymentDocs = [];
                var fileIndex = 0;

                $('.payment-document-row').each(function() {
                    var paymentInfo = $(this).find('.payment-info').text();
                    var amount = parseNumber($(this).find('.payment-amount-display').text());

                    var methodText = $(this).find('strong').text();
                    var paymentMethod = 'cash';
                    var checkId = $(this).data('check-id');

                    if (methodText.includes('Espèces')) paymentMethod = 'cash';
                    else if (methodText.includes('Virement')) paymentMethod = 'bank_transfer';
                    else if (methodText.includes('Chèque')) paymentMethod = 'check';
                    else if (methodText.includes('Carte')) paymentMethod = 'credit_card';

                    var paymentDate = extractDate(paymentInfo);
                    var notes = extractNotes(paymentInfo);

                    var paymentDoc = {
                        payment_method: paymentMethod,
                        amount: amount,
                        payment_date: paymentDate,
                        notes: notes
                    };

                    if (paymentMethod === 'check' && checkId) {
                        paymentDoc.check_id = checkId;
                    }

                    paymentDocs.push(paymentDoc);

                    var tempId = $(this).data('temp-id');
                    if (tempId && window.paymentFiles[tempId]) {
                        formData.append('payment_file_' + fileIndex, window.paymentFiles[tempId]);
                        paymentDoc.has_file = true;
                        paymentDoc.filename = window.paymentFiles[tempId].name;
                        fileIndex++;
                    }
                });

                formData.append('payment_documents', JSON.stringify(paymentDocs));
                formData.append('payment_files_count', fileIndex);

                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...').prop('disabled',
                    true);

                $.ajax({
                    url: "{{ route('raw-material-purchases.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('raw-material-purchases.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
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
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            function extractDate(info) {
                var match = info.match(/Date: (\d{2}\/\d{2}\/\d{4})/);
                if (match) {
                    var parts = match[1].split('/');
                    return parts[2] + '-' + parts[1] + '-' + parts[0];
                }
                return new Date().toISOString().split('T')[0];
            }

            function extractNotes(info) {
                var match = info.match(/Notes: (.*?)(?:\n|$)/);
                return match ? match[1] : '';
            }

            function showToast(type, message) {
                var toastId = 'toast_' + Date.now();
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

            $('#cash_amount, #transfer_amount, #card_amount, #allocation_amount').on('input', function() {
                var val = $(this).val();
                if (val && !/^\d*\.?\d*$/.test(val)) {
                    $(this).val(val.replace(/[^\d.]/g, ''));
                }
            });

            updateTotals();
        });
    </script>
@endpush
