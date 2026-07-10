@extends('layouts.app')

@section('title', 'Nouvelle Sortie de Production')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouvelle Sortie de Production</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('production-orders.index') }}">Ordres de
                                        Production</a></li>
                                <li class="breadcrumb-item active">Nouvelle Sortie</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Order Information -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Informations de l'Ordre
                        </h6>
                    </div>
                    <div class="card-body" id="orderInfoContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Chargement...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Production Results -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Résultats de Production
                        </h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" id="production_order_id" name="production_order_id"
                            value="{{ request('order_id', isset($productionOrder) ? $productionOrder->order_id : '') }}">

                        <form id="productionOutputForm">
                            @csrf

                            <!-- Famille Selection -->
                            <div class="mb-3" id="familleSelectionDiv" style="display: none;">
                                <label for="famille_id" class="form-label">Famille de Destination *</label>
                                <select class="form-control select2" id="famille_id" name="famille_id" required
                                    style="width: 100%;">
                                    <option value="">Sélectionner une famille...</option>
                                </select>
                                <small class="form-text text-muted" id="familleHelpText"></small>
                            </div>

                            <!-- Source Famille (for type2/type3 - hidden) -->
                            <input type="hidden" id="source_famille_id" name="source_famille_id" value="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_produced" class="form-label">Quantité Produite *</label>
                                    <input type="number" class="form-control" id="quantity_produced"
                                        name="quantity_produced" required min="0.01" value="1" step="0.01">
                                    <small class="form-text text-muted" id="quantityHelp"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_defective" class="form-label">Quantité Défectueuse *</label>
                                    <input type="number" class="form-control" id="quantity_defective"
                                        name="quantity_defective" required min="0" value="0" step="0.01">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="production_date" class="form-label">Date de Production *</label>
                                    <input type="date" class="form-control" id="production_date" name="production_date"
                                        required value="{{ date('Y-m-d') }}">
                                </div>
                            </div>

                            <!-- Calculated Values -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="alert alert-success mb-0">
                                        <small class="text-muted">Produits bons:</small>
                                        <h5 class="mb-0" id="calculatedGood">0</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info mb-0">
                                        <small class="text-muted">Volume total:</small>
                                        <h5 class="mb-0" id="calculatedVolume">0.0000 m³</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-danger mb-0">
                                        <small class="text-muted">Volume défectueux:</small>
                                        <h5 class="mb-0" id="calculatedWasteVolume">0.0000 m³</h5>
                                    </div>
                                </div>
                            </div>

                            <!-- BOM Consumption Button -->
                            <div class="mb-3" id="bomButtonDiv" style="display: none;">
                                <button type="button" class="btn btn-warning btn-lg w-100" data-bs-toggle="modal"
                                    data-bs-target="#bomModal">
                                    <i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières (Finalisation)
                                </button>
                                <small class="form-text text-muted d-block mt-2 text-center">
                                    ⚠️ Important: Cliquez ici pour saisir les consommations avant de finaliser la production
                                </small>
                                <div id="bomStatus" class="mt-2"></div>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" id="unit_volume_m3" name="unit_volume_m3" value="0">
                            <input type="hidden" id="total_volume_m3" name="total_volume_m3" value="0">
                            <input type="hidden" id="waste_volume_m3" name="waste_volume_m3" value="0">

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Observations..."></textarea>
                            </div>

                            <!-- Production by Famille Table -->
                            <div class="mb-4" id="productionTableDiv" style="display: none;">
                                <h6 class="mb-2">Production par Famille</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Famille</th>
                                                <th class="text-center">Quantité</th>
                                                <th class="text-center">Volume</th>
                                                <th class="text-center">Défauts</th>
                                                <th class="text-center">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody id="familleProductionBody">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Aucune production</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-save me-2"></i> Enregistrer la Sortie
                                </button>
                                <a href="{{ route('production-orders.index') }}" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOM Modal -->
    <div class="modal fade" id="bomModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="bomInfoAlert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information:</strong> La production a atteint la quantité cible.
                        Veuillez saisir les quantités réellement consommées.
                        <br><small>Note: Un écart de plus de 1% par rapport à la quantité planifiée sera signalé.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Matière Première</th>
                                    <th class="text-center">Stock Disponible</th>
                                    <th class="text-center">Quantité Planifiée (Total)</th>
                                    <th class="text-center">Quantité Consommée *</th>
                                    <th>Unité</th>
                                    <th class="text-center">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="bomModalBody">
                                <tr>
                                    <td colspan="6" class="text-center">Chargement...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <label for="consumption_global_notes" class="form-label">Notes générales sur la
                            consommation</label>
                        <textarea class="form-control" id="consumption_global_notes" name="consumption_global_notes" rows="2"
                            placeholder="Observations générales sur les consommations (optionnel)..."></textarea>
                    </div>
                    <div id="consumptionErrorsContainer" class="mt-3" style="display: none;">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention: Écarts de consommation détectés!</strong>
                            <ul id="errorsList" class="mt-2 mb-0"></ul>
                            <small>Ces écarts dépassent la tolérance de 1%. Veuillez vérifier et ajuster les
                                quantités.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveBomBtn">
                        <i class="fas fa-save me-2"></i>Valider les consommations
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
@endsection

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .order-info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-info-label {
            font-weight: 600;
            color: #495057;
        }

        .order-info-value {
            color: #212529;
        }

        .progress {
            height: 20px;
            border-radius: 10px;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            const orderId = $('#production_order_id').val();
            let currentOrderDetails = null;
            let currentProductionType = null;
            let currentTargetFamilleId = null;
            let currentSourceFamilleId = null;
            let currentUnitVolume = 0;
            let currentQuantityToProduce = 0;
            let currentRemaining = 0;
            let availableFamilles = [];
            let bomConsumptions = {};
            let selectedFamilleId = null;
            let bomValidated = false;
            let currentConsumptions = [];
            let consumptionErrors = [];

            if (orderId) {
                loadOrderDetails(orderId);
            }

            // Famille selection change
            $('#famille_id').change(function() {
                selectedFamilleId = $(this).val();
                updateFamilleInfo();

                const currentTargetProduced = currentOrderDetails.outputs
                    ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                    .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;
                const newTotal = currentTargetProduced + quantityProduced;
                const isFinalOutput = newTotal >= currentOrderDetails.quantity_to_produce;
                const isTargetFamille = (selectedFamilleId == currentOrderDetails.famille_id);

                const showConsumptionButton = isTargetFamille && isFinalOutput && currentRemaining > 0;

                if (showConsumptionButton) {
                    $('#bomButtonDiv').show();
                    $('#bomButtonDiv .btn-warning').html(
                        '<i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières (Finalisation)'
                    );
                } else {
                    $('#bomButtonDiv').hide();
                }
            });


            // Quantity inputs
            $('#quantity_produced, #quantity_defective').on('input', function() {
                updateCalculations();

                const currentTargetProduced = currentOrderDetails.outputs
                    ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                    .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;
                const newTotal = currentTargetProduced + quantityProduced;
                const isFinalOutput = newTotal >= currentOrderDetails.quantity_to_produce;
                const isTargetFamille = (selectedFamilleId == currentOrderDetails.famille_id);

                const showConsumptionButton = isTargetFamille && isFinalOutput && currentRemaining > 0;

                if (showConsumptionButton) {
                    $('#bomButtonDiv').show();
                    $('#bomButtonDiv .btn-warning').html(
                        '<i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières (Finalisation)'
                    );

                    $('#bomInfoAlert').html(`
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        <strong>Prêt pour finalisation!</strong>
                        Vous allez produire ${quantityProduced} unités (reste: ${currentRemaining} unités).
                        <br>Veuillez saisir les consommations des matières premières.
                    `);
                } else {
                    $('#bomButtonDiv').hide();
                }

                if (currentProductionType === 'type1') {
                    if ($('#bomModal').hasClass('show')) {
                        updateBOMQuantities();
                    }
                }
            });

            $('#bomModal').on('show.bs.modal', function() {
                if (currentProductionType === 'type1') {
                    const currentTargetProduced = currentOrderDetails.outputs
                        ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                        .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                    const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;
                    const newTotal = currentTargetProduced + quantityProduced;
                    const isFinalOutput = newTotal >= currentOrderDetails.quantity_to_produce;
                    const isTargetFamille = (selectedFamilleId == currentOrderDetails.famille_id);

                    if (isTargetFamille && isFinalOutput && currentRemaining > 0) {
                        loadBOMModal();
                    } else {
                        $('#bomModal').modal('hide');
                        if (isTargetFamille) {
                            showToast('warning',
                                'Les conditions pour saisir les consommations ne sont pas remplies. La quantité produite doit épuiser le stock restant.'
                            );
                        } else {
                            $('#bomModal').modal('hide');
                        }
                    }
                }
            });

            function updateRemainingDisplay() {
                const currentTargetProduced = currentOrderDetails.outputs
                    ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                    .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                const remaining = currentOrderDetails.quantity_to_produce - currentTargetProduced;
                const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;
                const willComplete = quantityProduced >= remaining;
                const isTargetFamille = (selectedFamilleId == currentOrderDetails.famille_id);

                if (isTargetFamille && willComplete && remaining > 0) {
                    $('#quantityHelp').html(`
                        <strong class="text-success">
                            ⚡ Finalisation! N'oubliez pas de saisir les consommations des matières premières.
                        </strong>
                    `);
                    $('#bomButtonDiv').show();
                } else if (isTargetFamille && remaining > 0) {
                    $('#quantityHelp').html(`
                        Production directe: maximum ${currentRemaining} unités.
                        <br>Encore ${remaining} unités à produire pour finaliser.
                        ${quantityProduced < remaining ? `Il vous faudra encore ${remaining - quantityProduced} unités supplémentaires.` : ''}
                    `);
                    $('#bomButtonDiv').hide();
                } else {
                    $('#quantityHelp').html(`Production directe: maximum ${currentRemaining} unités`);
                    $('#bomButtonDiv').hide();
                }
            }

            $('#quantity_produced').on('input', function() {
                updateRemainingDisplay();
            });

            $('#famille_id').change(function() {
                updateRemainingDisplay();
            });


            // $('#saveBomBtn').on('click', function() {
            //     const saved = saveBomConsumptions();
            //     if (saved) {
            //         $('#bomModal').modal('hide');
            //         // Update button appearance to show BOM is ready
            //         $('#bomButtonDiv .btn-warning').removeClass('btn-warning').addClass('btn-success');
            //         $('#bomButtonDiv .btn-success').html(
            //             '<i class="fas fa-check-circle me-2"></i>Consommation enregistrée ✓');
            //     }
            // });

            let lastQuantity = 0;
            setInterval(function() {
                const currentQty = parseFloat($('#quantity_produced').val()) || 0;
                if (Math.abs(currentQty - lastQuantity) > 0.1 && currentProductionType === 'type1') {
                    lastQuantity = currentQty;
                    bomValidated = false;
                    $('#bomButtonDiv .btn-success').removeClass('btn-success').addClass('btn-warning');
                    $('#bomButtonDiv .btn-warning').html(
                        '<i class="fas fa-list-alt me-2"></i>Consommation des Matières Premières');
                    $('#bomStatus').html(
                        '<div class="alert alert-warning">⚠️ Quantité modifiée - Vérifiez les consommations</div>'
                    );
                    setTimeout(() => $('#bomStatus .alert').fadeOut(3000), 3000);
                }
            }, 500);

            // Form submission
            $('#productionOutputForm').submit(function(e) {
                e.preventDefault();
                submitProductionOutput();
            });

            function loadOrderDetails(orderId) {
                $('#orderInfoContent').html(
                    '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Chargement...</p></div>'
                );

                $.ajax({
                    url: "{{ url('api/production-orders') }}/" + orderId,
                    type: "GET",
                    success: function(response) {
                        if (response && response.success) {
                            currentOrderDetails = response.order || response.data?.order || response;
                            processOrderData();
                            updateOrderInfoUI();
                            loadFamilles();
                            loadProductionTable();

                            if (currentProductionType === 'type1') {
                                loadBOMModal();
                                $('#bomButtonDiv').show();
                            }

                            updateCalculations();
                        } else {
                            $('#orderInfoContent').html(
                                '<div class="alert alert-danger">Erreur lors du chargement</div>');
                        }
                    },
                    error: function() {
                        $('#orderInfoContent').html(
                            '<div class="alert alert-danger">Erreur de connexion</div>');
                    }
                });
            }

            function processOrderData() {
                currentProductionType = currentOrderDetails.production_type;
                if (currentProductionType === 'direct') currentProductionType = 'type1';
                else if (currentProductionType === 'decoupage') currentProductionType = 'type2';
                else if (currentProductionType === 'conversion') currentProductionType = 'type3';

                currentTargetFamilleId = currentOrderDetails.famille_id;
                currentSourceFamilleId = currentOrderDetails.source_famille_id;
                currentUnitVolume = currentOrderDetails.product?.total_volume || 0;
                currentQuantityToProduce = currentOrderDetails.quantity_to_produce || 0;

                if (currentProductionType === 'type2' || currentProductionType === 'type3') {
                    if (currentSourceFamilleId) {
                        $('#source_famille_id').val(currentSourceFamilleId);
                    } else if (currentTargetFamilleId) {
                        $('#source_famille_id').val(currentTargetFamilleId);
                        currentSourceFamilleId = currentTargetFamilleId;
                    }
                }

                calculateRemaining();
            }

            function calculateRemaining() {
                const outputs = currentOrderDetails.outputs || [];
                if (currentProductionType === 'type1') {
                    const totalTargetProduced = outputs
                        .filter(o => o.famille_id == currentOrderDetails.famille_id)
                        .reduce((s, o) => s + (o.quantity_produced || 0), 0);
                    currentRemaining = currentOrderDetails.quantity_to_produce - totalTargetProduced;
                } else {
                    const totalConsumed = outputs
                        .filter(o => o.output_type === currentProductionType)
                        .reduce((s, o) => s + (o.quantity_consumed || 0), 0);
                    currentRemaining = currentOrderDetails.required_quantity - totalConsumed;
                }
                currentRemaining = Math.max(0, currentRemaining);
            }

            function updateOrderInfoUI() {
                const product = currentOrderDetails.product || {};
                const outputs = currentOrderDetails.outputs || [];
                const isCompleted = currentOrderDetails.status === 'completed';

                const totalTargetProduced = outputs
                    .filter(o => o.famille_id == currentOrderDetails.famille_id)
                    .reduce((s, o) => s + (o.quantity_produced || 0), 0);
                const totalAllProduced = outputs.reduce((s, o) => s + (o.quantity_produced || 0), 0);
                const totalAllVolume = outputs.reduce((s, o) => s + (o.total_volume_m3 || 0), 0);

                const progress = currentQuantityToProduce > 0 ? (totalTargetProduced / currentQuantityToProduce) *
                    100 : 0;

                let typeBadge = '';
                let typeText = '';
                if (currentProductionType === 'type1') {
                    typeBadge = 'bg-primary';
                    typeText = 'Production Directe';
                } else if (currentProductionType === 'type2') {
                    typeBadge = 'bg-warning';
                    typeText = 'Découpage';
                } else {
                    typeBadge = 'bg-success';
                    typeText = 'Conversion';
                }

                let html = `
                    <div class="order-info-card">
                        <h6 class="mb-3"><i class="fas fa-box me-2"></i>${currentOrderDetails.order_number || 'N/A'}</h6>
                        <div class="order-info-row"><span class="order-info-label">Produit:</span><span class="order-info-value"><strong>${product.product_name || 'N/A'}</strong> <span class="badge ${typeBadge}">${typeText}</span></span></div>
                        <div class="order-info-row"><span class="order-info-label">Volume Unitaire:</span><span class="order-info-value">${currentUnitVolume.toFixed(4)} m³</span></div>
                        <div class="order-info-row"><span class="order-info-label">Quantité Cible:</span><span class="order-info-value">${currentQuantityToProduce} unités</span></div>
                        <div class="order-info-row"><span class="order-info-label">Famille Cible:</span><span class="order-info-value"><span class="badge bg-primary">${currentOrderDetails.famille?.famille_name || 'Non spécifié'}</span></span></div>
                `;

                if (currentProductionType === 'type2' || currentProductionType === 'type3') {
                    const sourceFamilleName = currentOrderDetails.source_famille_name || currentOrderDetails
                        .sourceFamille?.famille_name || 'Non spécifié';
                    html +=
                        `<div class="order-info-row"><span class="order-info-label">Famille Source:</span><span class="order-info-value"><span class="badge bg-warning">${sourceFamilleName}</span></span></div>`;
                }

                html += `
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1"><span>Progression:</span><span>${isCompleted ? '100%' : progress.toFixed(1) + '%'}</span></div>
                            <div class="progress"><div class="progress-bar ${isCompleted ? 'bg-success' : 'bg-primary'}" style="width: ${isCompleted ? '100' : Math.min(progress, 100)}%"></div></div>
                        </div>
                        <div class="order-info-row mt-3"><span class="order-info-label">Déjà Produit Cible:</span><span class="order-info-value">${totalTargetProduced} unités</span></div>
                        <div class="order-info-row"><span class="order-info-label">Restant Cible:</span><span class="order-info-value"><strong>${currentRemaining} unités</strong></span></div>
                        <div class="order-info-row"><span class="order-info-label">Total Toutes Familles:</span><span class="order-info-value">${totalAllProduced} unités</span></div>
                        <div class="order-info-row"><span class="order-info-label">Volume Total Produit:</span><span class="order-info-value">${totalAllVolume} m³</span></div>
                    </div>
                `;

                $('#orderInfoContent').html(html);

                if (!isCompleted && currentRemaining > 0) {
                    const maxQuantity = currentRemaining;
                    $('#quantity_produced').attr('max', maxQuantity).val(maxQuantity);
                    if (currentProductionType === 'type2') {
                        $('#quantityHelp').text(`Découpage: maximum ${maxQuantity} blocs à découper`);
                    } else if (currentProductionType === 'type3') {
                        $('#quantityHelp').text(`Conversion: maximum ${maxQuantity} sous-blocs à convertir`);
                    } else {
                        $('#quantityHelp').text(`Production directe: maximum ${maxQuantity} unités`);
                    }
                } else if (isCompleted || currentRemaining === 0) {
                    $('#quantity_produced, #quantity_defective').prop('disabled', true);
                    $('#submitBtn').prop('disabled', true).html(
                        '<i class="fas fa-check me-2"></i>Commande Terminée');
                }
            }

            function loadFamilles() {
                const product = currentOrderDetails.product;
                if (!product) return;

                if (product.has_familles) {
                    $.ajax({
                        url: "{{ url('api/products') }}/" + product.product_id + "/familles",
                        type: "GET",
                        success: function(response) {
                            if (response && response.success) {
                                availableFamilles = response.data || [];
                                populateFamilleSelect();
                                $('#familleSelectionDiv').show();
                            }
                        },
                        error: function() {
                            console.error('Error loading familles');
                        }
                    });
                } else {
                    $('#familleSelectionDiv').hide();
                    if (currentTargetFamilleId) {
                        $('#famille_id').val(currentTargetFamilleId);
                        selectedFamilleId = currentTargetFamilleId;
                    }
                }
            }

            function populateFamilleSelect() {
                const select = $('#famille_id');
                select.empty();
                select.append('<option value="">Sélectionner une famille...</option>');

                availableFamilles.forEach(famille => {
                    const isTarget = (famille.famille_id == currentTargetFamilleId);
                    const selected = (isTarget && !selectedFamilleId) ? 'selected' : '';
                    select.append(
                        `<option value="${famille.famille_id}" ${selected} data-is-target="${isTarget}">${famille.famille_name} ${isTarget ? '(Cible)' : ''}</option>`
                    );
                });

                select.select2({
                    language: "fr",
                    placeholder: "Sélectionner une famille",
                    allowClear: false
                });

                if (!selectedFamilleId && currentTargetFamilleId) {
                    select.val(currentTargetFamilleId).trigger('change');
                    selectedFamilleId = currentTargetFamilleId;
                } else if (selectedFamilleId) {
                    select.val(selectedFamilleId).trigger('change');
                }
            }

            function updateFamilleInfo() {
                const familleId = $('#famille_id').val();
                const isTarget = (familleId == currentTargetFamilleId);

                if (familleId) {
                    if (isTarget) {
                        $('#familleHelpText').html(
                            '<span class="text-success"><i class="fas fa-bullseye"></i> Production pour la famille cible - compte pour l\'objectif</span>'
                        );
                    } else {
                        $('#familleHelpText').html(
                            '<span class="text-warning"><i class="fas fa-random"></i> Production pour une famille mixte - hors objectif</span>'
                        );
                    }
                } else {
                    $('#familleHelpText').html('');
                }
            }

            function loadProductionTable() {
                const outputs = currentOrderDetails.outputs || [];

                if (outputs.length === 0) {
                    $('#familleProductionBody').html(
                        '<tr><td colspan="5" class="text-center text-muted">Aucune production enregistrée</td></tr>'
                    );
                    $('#productionTableDiv').show();
                    return;
                }

                const familleGroups = {};
                outputs.forEach(output => {
                    const familleId = output.famille_id;
                    const familleName = output.famille_name || 'Inconnu';

                    if (!familleGroups[familleId]) {
                        familleGroups[familleId] = {
                            famille_id: familleId,
                            famille_name: familleName,
                            quantity: 0,
                            volume: 0,
                            defective: 0,
                            is_target: (familleId == currentOrderDetails.famille_id)
                        };
                    }

                    familleGroups[familleId].quantity += output.quantity_produced || 0;
                    familleGroups[familleId].volume += output.total_volume_m3 || 0;
                    familleGroups[familleId].defective += output.quantity_defective || 0;
                });

                let html = '';
                Object.values(familleGroups).forEach(group => {
                    const targetBadge = group.is_target ?
                        '<span class="badge bg-success ms-1">Cible</span>' :
                        '<span class="badge bg-secondary ms-1">Mixte</span>';
                    html += `
                        <tr>
                            <td>${group.famille_name} ${targetBadge}</td>
                            <td class="text-center">${group.quantity} unités</td>
                            <td class="text-center">${group.volume} m³</td>
                            <td class="text-center">${group.defective} unités</td>
                            <td class="text-center">${group.is_target ? '<span class="badge bg-success">Objectif</span>' : '<span class="badge bg-info">Hors objectif</span>'}</td>
                        </tr>
                    `;
                });

                $('#familleProductionBody').html(html);
                $('#productionTableDiv').show();
            }

            function loadConsumptionsFromOrder() {
                const orderId = currentOrderDetails.order_id;

                $.ajax({
                    url: `/api/production-orders/${orderId}/consumptions`,
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.consumptions) {
                            currentConsumptions = response.consumptions;
                            displayConsumptionsModal();
                        } else {
                            showToast('error', 'Erreur lors du chargement des consommations');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading consumptions:', xhr);
                        showToast('error', 'Erreur lors du chargement des consommations');
                    }
                });
            }

            // Display consumptions in modal
            function displayConsumptionsModal() {
                let html = '';

                if (!currentConsumptions || currentConsumptions.length === 0) {
                    html = '<tr><td colspan="6" class="text-center">Aucune matière première à consommer</td></tr>';
                    $('#bomModalBody').html(html);
                    return;
                }

                currentConsumptions.forEach((consumption) => {
                    const material = consumption.raw_material || {};
                    const materialId = consumption.material_id;
                    const materialName = material.material_name || 'Inconnu';
                    const materialCode = material.material_code || '';
                    const unitOfMeasure = material.unit_of_measure || 'unité';
                    const availableStock = material.current_stock || 0;

                    // Get planned quantity from ProductionConsumption table
                    const plannedQuantity = parseFloat(consumption.planned_quantity) || 0;
                    const actualQuantityUsed = parseFloat(consumption.actual_quantity_used) || 0;

                    const isChute = materialCode === 'CHUTE-PRODUCTION' || materialName.toLowerCase()
                        .includes('chute');
                    const rowClass = isChute ? 'table-warning' : '';
                    const stockClass = availableStock >= plannedQuantity ? 'text-success' : 'text-warning';
                    const stockBadge = availableStock >= plannedQuantity ? 'bg-success' : 'bg-warning';

                    html += `
            <tr class="${rowClass}" data-material-id="${materialId}">
                <td>
                    <strong>${escapeHtml(materialName)}</strong>
                    <br><small class="text-muted">${escapeHtml(materialCode)}</small>
                    ${isChute ? '<br><small class="text-muted text-warning">Chutes de production</small>' : ''}
                </td>
                <td class="text-center">
                    <span class="badge ${stockBadge}">
                        ${availableStock}
                    </span>
                 </td>
                <td class="text-center">
                    <strong class="text-primary">${plannedQuantity.toFixed(2)}</strong>
                    <br><small class="text-muted">${unitOfMeasure}</small>
                 </td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm bom-quantity"
                        data-material-id="${materialId}"
                        data-planned="${plannedQuantity}"
                        data-material-name="${escapeHtml(materialName)}"
                        data-unit="${unitOfMeasure}"
                        value="${actualQuantityUsed > 0 ? actualQuantityUsed.toFixed(2) : plannedQuantity.toFixed(2)}"
                        step="0.01" min="0"
                        style="width: 140px; margin: 0 auto;">
                 </td>
                <td class="text-center">${escapeHtml(unitOfMeasure)}</td>
                <td class="text-center">
                    <input type="text" class="form-control form-control-sm consumption-notes"
                        data-material-id="${materialId}"
                        placeholder="Optionnel..."
                        value="${escapeHtml(consumption.notes || '')}"
                        style="width: 150px;">
                 </td>
             </tr>
        `;
                });

                $('#bomModalBody').html(html);
                attachConsumptionValidation();
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Attach validation on consumption inputs
            function attachConsumptionValidation() {
                $('.bom-quantity').off('input').on('input', function() {
                    const planned = parseFloat($(this).data('planned')) || 0;
                    const actual = parseFloat($(this).val()) || 0;
                    const materialName = $(this).data('material-name');
                    const $row = $(this).closest('tr');

                    if (planned > 0) {
                        const difference = Math.abs(actual - planned);
                        const percentage = (difference / planned) * 100;

                        // Remove existing classes
                        $row.removeClass('table-danger table-warning');
                        $(this).removeClass('is-invalid is-warning');

                        if (percentage > 1) {
                            $row.addClass('table-danger');
                            $(this).addClass('is-invalid');
                            $(this).attr('title',
                                `⚠️ Écart de ${percentage.toFixed(2)}% - Hors tolérance (max 1%)`);
                        } else if (percentage > 0) {
                            $row.addClass('table-warning');
                            $(this).addClass('is-warning');
                            $(this).attr('title', `⚠️ Écart de ${percentage.toFixed(2)}% - Dans tolérance`);
                        } else {
                            $(this).attr('title', '✓ Quantité conforme');
                        }
                    }

                    // Show/hide tooltip
                    if ($(this).attr('title')) {
                        $(this).tooltip('dispose');
                        $(this).tooltip({
                            trigger: 'manual',
                            placement: 'top'
                        });
                        $(this).tooltip('show');
                        setTimeout(() => $(this).tooltip('hide'), 2000);
                    }
                });

                // Initialize tooltips
                $('.bom-quantity').tooltip();
            }

            // Save consumptions to database
            function saveConsumptionsToOrder() {
                const consumptionsData = [];
                let hasErrors = false;
                const errorsList = [];

                // Collect all consumption data
                $('.bom-quantity').each(function() {
                    const materialId = $(this).data('material-id');
                    const actualQuantity = parseFloat($(this).val()) || 0;
                    const plannedQuantity = parseFloat($(this).data('planned')) || 0;
                    const materialName = $(this).data('material-name');

                    // Get notes for this material
                    const notes = $(`.consumption-notes[data-material-id="${materialId}"]`).val() || '';

                    // Validate
                    if (plannedQuantity > 0) {
                        const difference = Math.abs(actualQuantity - plannedQuantity);
                        const percentage = (difference / plannedQuantity) * 100;

                        if (percentage > 1) {
                            hasErrors = true;
                            errorsList.push({
                                material: materialName,
                                planned: plannedQuantity.toFixed(2),
                                actual: actualQuantity.toFixed(2),
                                percentage: percentage.toFixed(2)
                            });
                        }
                    }

                    consumptionsData.push({
                        material_id: materialId,
                        actual_quantity_used: actualQuantity,
                        notes: notes
                    });
                });

                if (hasErrors) {
                    let errorsHtml = '';
                    errorsList.forEach(error => {
                        errorsHtml +=
                            `<li><strong>${error.material}</strong>: ${error.actual} ${error.planned} (écart ${error.percentage}%) - Hors tolérance 1%</li>`;
                    });
                    $('#errorsList').html(errorsHtml);
                    $('#consumptionErrorsContainer').show();

                    // Scroll to errors
                    $('html, body').animate({
                        scrollTop: $('#consumptionErrorsContainer').offset().top - 100
                    }, 500);

                    return false;
                }

                const globalNotes = $('#consumption_global_notes').val() || '';
                const orderId = currentOrderDetails.order_id;

                // Save consumptions via AJAX
                $.ajax({
                    url: `/api/production-orders/${orderId}/consumptions`,
                    type: "POST",
                    data: JSON.stringify({
                        consumptions: consumptionsData,
                        global_notes: globalNotes
                    }),
                    contentType: "application/json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#bomModal').modal('hide');
                            showToast('success', 'Consommations enregistrées avec succès');

                            // Update button appearance
                            $('#bomButtonDiv .btn-warning').removeClass('btn-warning').addClass(
                                'btn-success');
                            $('#bomButtonDiv .btn-success').html(
                                '<i class="fas fa-check-circle me-2"></i>Consommation validée ✓');
                            $('#bomStatus').html(
                                '<div class="alert alert-success mt-2">✓ Consommations enregistrées et conformes</div>'
                            );

                            // Store that consumptions are validated
                            bomValidated = true;

                            // Auto-submit the form after consumptions are saved
                            submitProductionOutput();
                        } else {
                            showToast('error', response.message || 'Erreur lors de l\'enregistrement');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.type === 'validation_error' && response.errors) {
                            let errorsHtml = '';
                            response.errors.forEach(error => {
                                errorsHtml +=
                                    `<li><strong>${error.material_name}</strong>: ${error.actual} unités au lieu de ${error.planned} (écart ${error.percentage}%) - Hors tolérance 1%</li>`;
                            });
                            $('#errorsList').html(errorsHtml);
                            $('#consumptionErrorsContainer').show();
                            showToast('warning', 'Veuillez corriger les écarts de consommation');
                        } else {
                            showToast('error', response?.message || 'Erreur lors de l\'enregistrement');
                        }
                    }
                });

                return false;
            }

            function loadBOMModal() {
                const currentTargetProduced = currentOrderDetails.outputs
                    ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                    .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;
                const newTotal = currentTargetProduced + quantityProduced;
                const isFinalOutput = newTotal >= currentOrderDetails.quantity_to_produce;
                const isTargetFamille = (selectedFamilleId == currentOrderDetails.famille_id);

                const showConsumptionButton = isTargetFamille && isFinalOutput && currentRemaining > 0;

                if (!showConsumptionButton) {
                    $('#bomButtonDiv').hide();

                    if (isTargetFamille && !isFinalOutput && currentRemaining > 0) {
                        $('#bomInfoAlert').html(`
                <i class="fas fa-info-circle me-2"></i>
                <strong>Information:</strong> La consommation des matières premières sera saisie
                lorsque vous produirez la quantité restante (${currentRemaining} unités) pour la famille cible.
                <br><small>Cette production (${quantityProduced} unités) ne complète pas la quantité cible.</small>
                <br><small>Encore ${currentRemaining - quantityProduced} unités à produire pour finaliser.</small>
            `);
                    } else if (!isTargetFamille) {
                        $('#bomInfoAlert').html(`
                <i class="fas fa-info-circle me-2"></i>
                <strong>Information:</strong> La saisie des consommations n'est requise que pour la production de la famille cible.
                <br><small>Famille actuelle: ${selectedFamilleId == currentOrderDetails.famille_id ? 'Cible' : 'Non cible'}</small>
                <br><small>Famille cible: ${currentOrderDetails.famille?.famille_name || 'Non spécifié'}</small>
            `);
                    }

                    $('#saveBomBtn').prop('disabled', true);
                    $('#bomModalBody').html(`
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-clock me-2"></i>
                    La saisie des consommations n'est pas requise pour cette production.
                    <br><br>
                    <strong>Conditions requises pour afficher le formulaire:</strong>
                    <ul class="text-start mt-2">
                        <li>Vous devez produire pour la famille cible (${currentOrderDetails.famille?.famille_name || 'Non spécifié'})</li>
                        <li>La quantité produite doit épuiser le stock restant (${currentRemaining} unités)</li>
                    </ul>
                    <strong>Statut actuel:</strong>
                    <ul class="text-start mt-2">
                        <li>Famille sélectionnée: ${selectedFamilleId == currentOrderDetails.famille_id ? '✓ Cible' : '✗ Non cible'}</li>
                        <li>Quantité à produire: ${quantityProduced} unités</li>
                        <li>Reste à produire: ${currentRemaining} unités</li>
                        <li>Finalisation: ${isFinalOutput ? '✓ Oui' : '✗ Non'}</li>
                    </ul>
                </td>
            </tr>
        `);
                    return;
                }

                $('#bomButtonDiv').show();
                $('#bomInfoAlert').html(`
        <i class="fas fa-check-circle me-2 text-success"></i>
        <strong>Quantité cible atteinte!</strong>
        Vous êtes sur le point de finaliser la production pour la famille cible.
        <br>Veuillez saisir les quantités réellement consommées pour finaliser la production.
        <br><small>Note: Un écart de plus de 1% par rapport à la quantité planifiée sera signalé comme qualité non conforme.</small>
        <br><strong class="text-primary">Quantité à produire: ${quantityProduced} unités (Reste: ${currentRemaining} unités)</strong>
    `);
                $('#saveBomBtn').prop('disabled', false);

                loadConsumptionsFromOrder();
            }
            $('#saveBomBtn').off('click').on('click', function() {
                saveConsumptionsToOrder();
            });


            function updateBOMQuantities() {
                const quantityProduced = parseFloat($('#quantity_produced').val()) || 1;

                $('#bomModalBody tr').each(function() {
                    const quantityInput = $(this).find('.bom-quantity');
                    if (quantityInput.length) {
                        const planned = parseFloat(quantityInput.data('planned')) || 0;
                        const baseQuantity = parseFloat(planned) / (quantityProduced + 0.001);
                        const newCalculated = baseQuantity * quantityProduced;

                        const calculatedCell = $(this).find('td:eq(2)');
                        if (calculatedCell.length && !isNaN(newCalculated)) {
                            calculatedCell.text(newCalculated.toFixed(2));
                        }

                        const isChute = quantityInput.data('is-chute') === true;
                        if (!isChute && !quantityInput.data('manual')) {
                            quantityInput.val(newCalculated.toFixed(2));
                            quantityInput.data('planned', newCalculated);
                        }
                    }
                });
            }

            function saveBomConsumptions() {
                bomConsumptions = {};
                let hasConsumption = false;
                let hasNonChuteConsumption = false;
                let missingRequired = false;

                $('#bomModalBody .bom-quantity').each(function() {
                    const materialId = $(this).data('material-id');
                    const quantity = parseFloat($(this).val()) || 0;
                    const isChute = $(this).data('is-chute') === true;

                    // Check if there's a planned quantity (required material)
                    const planned = parseFloat($(this).data('planned')) || 0;
                    const isRequired = planned > 0 && !isChute;

                    if (isRequired && quantity === 0) {
                        missingRequired = true;
                    }

                    if (quantity > 0) {
                        bomConsumptions[materialId] = quantity;
                        hasConsumption = true;
                        if (!isChute) hasNonChuteConsumption = true;
                    }
                });

                // Check if there are any required materials
                const hasRequiredMaterials = $('#bomModalBody .bom-quantity[data-is-chute="false"]').length > 0;

                if (hasRequiredMaterials && missingRequired) {
                    $('#bomStatus').html(
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Veuillez saisir la quantité pour toutes les matières premières requises</div>'
                    );
                    bomValidated = false;
                    return false;
                }

                if (hasRequiredMaterials && !hasNonChuteConsumption) {
                    $('#bomStatus').html(
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Au moins une matière première requise doit être consommée</div>'
                    );
                    bomValidated = false;
                    return false;
                }

                // All validations passed
                bomValidated = true;

                if (!hasConsumption) {
                    $('#bomStatus').html(
                        '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Aucune consommation saisie. Vous pourrez modifier plus tard.</div>'
                    );
                } else {
                    const count = Object.keys(bomConsumptions).length;
                    $('#bomStatus').html(
                        `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${count} matière(s) saisie(s) - Prêt pour l'enregistrement</div>`
                    );
                }

                return true;
            }

            function updateCalculations() {
                const quantityProduced = parseFloat($('#quantity_produced').val()) || 0;
                const quantityDefective = parseFloat($('#quantity_defective').val()) || 0;

                const goodQuantity = quantityProduced - quantityDefective;
                const autoWasteVolume = currentUnitVolume * quantityDefective;
                const totalVolume = currentUnitVolume * quantityProduced;

                $('#calculatedGood').text(goodQuantity + ' unités');
                $('#calculatedVolume').text(totalVolume.toFixed(4) + ' m³');
                $('#calculatedWasteVolume').text(autoWasteVolume.toFixed(4) + ' m³');

                $('#total_volume_m3').val(totalVolume);
                $('#waste_volume_m3').val(autoWasteVolume);
                $('#unit_volume_m3').val(currentUnitVolume);
            }

            function submitProductionOutput() {
                const quantityProduced = parseFloat($('#quantity_produced').val()) || 0;
                const quantityDefective = parseFloat($('#quantity_defective').val()) || 0;
                const familleId = $('#famille_id').val();

                if (quantityDefective > quantityProduced) {
                    showToast('error', 'La quantité défectueuse ne peut pas dépasser la quantité produite');
                    return;
                }

                if (quantityProduced > currentRemaining && currentRemaining > 0) {
                    showToast('error', `Quantité excessive. Maximum ${currentRemaining} unités autorisées.`);
                    return;
                }

                if (!familleId && currentOrderDetails.product?.has_familles) {
                    showToast('error', 'Veuillez sélectionner une famille');
                    return;
                }

                const isTargetFamille = (familleId == currentOrderDetails.famille_id);

                if (currentProductionType === 'type1' && isTargetFamille) {
                    const currentTargetProduced = currentOrderDetails.outputs
                        ?.filter(o => o.famille_id == currentOrderDetails.famille_id)
                        .reduce((s, o) => s + (o.quantity_produced || 0), 0) || 0;

                    const newTotal = currentTargetProduced + quantityProduced;
                    const willCompleteTarget = newTotal >= currentOrderDetails.quantity_to_produce;

                    if (willCompleteTarget && currentRemaining > 0 && !bomValidated) {
                        loadBOMModal();
                        $('#bomModal').modal('show');
                        return;
                    }
                }

                const formData = new FormData($('#productionOutputForm')[0]);
                formData.append('production_order_id', orderId);
                formData.append('product_id', currentOrderDetails.product_id);

                if (familleId) {
                    formData.append('famille_id', familleId);
                }

                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...');

                $.ajax({
                    url: "{{ route('production-output.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(
                                '<i class="fas fa-save me-2"></i> Enregistrer la Sortie');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        let message = response?.message || 'Une erreur est survenue';

                        if (response?.type === 'consumption_required') {
                            message = response.message;
                            loadBOMModal();
                            $('#bomModal').modal('show');
                        } else if (response?.type === 'consumption_error' && response?.errors) {
                            message = 'Erreurs de consommation détectées. Veuillez corriger.';
                            loadBOMModal();
                            $('#bomModal').modal('show');
                            setTimeout(() => {
                                displayConsumptionErrors(response.errors);
                            }, 500);
                        } else {
                            showToast('error', message);
                        }
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i> Enregistrer la Sortie');
                    }
                });
            }

            function displayConsumptionErrors(errors) {
                let errorsHtml = '';
                errors.forEach(error => {
                    errorsHtml +=
                        `<li><strong>${error.material_name}</strong>: ${error.actual} au lieu de ${error.planned} (écart ${error.percentage}%)</li>`;
                });
                $('#errorsList').html(errorsHtml);
                $('#consumptionErrorsContainer').show();
            }

            function showToast(type, message) {
                const toast = $(
                    `<div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`
                );
                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                setTimeout(() => toast.remove(), 5000);
            }
        });
    </script>
@endpush
