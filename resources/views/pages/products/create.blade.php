@extends('layouts.app')

@section('title', 'Nouveau Produit')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <!-- Breadcrumb and Title Card -->
        <div class="card card-body py-3 mb-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-space-between">
                        <h4 class="mb-4 mb-sm-0 card-title">Nouveau Produit</h4>
                        <nav aria-label="breadcrumb" class="ms-auto">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item d-flex align-items-center">
                                    <a class="text-muted text-decoration-none d-flex" href="{{ route('dashboard') }}">
                                        <iconify-icon icon="solar:home-2-line-duotone" class="fs-6"></iconify-icon>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a class="text-muted text-decoration-none" href="{{ route('products.index') }}">
                                        Produits
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
                            <i class="fas fa-plus-circle me-2"></i>Créer un Nouveau Produit
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="productForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="section-header mb-4">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-info-circle me-2"></i>Informations de Base
                                </h6>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_code" class="form-label">Code Produit *</label>
                                        <input type="text" class="form-control" id="product_code" name="product_code"
                                            required maxlength="50" placeholder="Ex: PROD001">
                                        <small class="form-text text-muted">Code unique d'identification</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_name" class="form-label">Nom du Produit *</label>
                                        <input type="text" class="form-control" id="product_name" name="product_name"
                                            required maxlength="255" placeholder="Ex: Matelas Épique 140x190">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_type" class="form-label">Type de Production *</label>
                                        <select class="form-control" id="product_type" name="product_type" required>
                                            <option value="">Sélectionner un type</option>
                                            <option value="production">Production (Bloc)</option>
                                            <option value="decoupage">Découpage (Sous Bloc)</option>
                                            <option value="finale">Produit Final (Volume)</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            <strong>Production (Bloc):</strong> Produit fabriqué à partir de matières
                                            premières<br>
                                            <strong>Découpage (Sous Bloc):</strong> Produit intermédiaire obtenu par
                                            découpage<br>
                                            <strong>Produit Final (Volume):</strong> Produit fini prêt à la vente
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="unit_of_measure" class="form-label">Unité (affichée dans les
                                            ventes)</label>
                                        <input type="text" class="form-control" id="unit_of_measure"
                                            name="unit_of_measure" maxlength="50" value="pièce">
                                        <small class="form-text text-muted">
                                            Par défaut "pièce", modifiable (Ex: m3, kg, unité...)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Dimensions Section -->
                            <div class="section-header mb-4">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-ruler-combined me-2"></i>Dimensions
                                </h6>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="height_m" class="form-label">Hauteur (m)</label>
                                        <input type="number" class="form-control" id="height_m" name="height_m"
                                            min="0" step="0.001" placeholder="Ex: 2.000">
                                        <small class="form-text text-muted">En mètres</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="width_m" class="form-label">Largeur (m)</label>
                                        <input type="number" class="form-control" id="width_m" name="width_m"
                                            min="0" step="0.001" placeholder="Ex: 1.400">
                                        <small class="form-text text-muted">En mètres</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="depth_m" class="form-label">Profondeur (m)</label>
                                        <input type="number" class="form-control" id="depth_m" name="depth_m"
                                            min="0" step="0.001" placeholder="Ex: 0.250">
                                        <small class="form-text text-muted">En mètres</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="volume_m3" class="form-label">Volume (m³)</label>
                                        <input type="number" class="form-control" id="volume_m3" name="volume_m3"
                                            min="0" step="0.0001" placeholder="Ex: 0.700" readonly>
                                        <small class="form-text text-muted">Calculé automatiquement</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="weight_kg" class="form-label">Poids (kg)</label>
                                        <input type="number" class="form-control" id="weight_kg" name="weight_kg"
                                            min="0" step="0.01" placeholder="Ex: 25.50">
                                    </div>
                                </div>
                            </div>

                            <!-- Stock Levels -->
                            <div class="section-header mb-4">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-boxes me-2"></i>Niveaux de Stock
                                </h6>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_stock_level" class="form-label">Stock Minimum</label>
                                        <input type="number" class="form-control" id="min_stock_level"
                                            name="min_stock_level" min="0" step="0.01"
                                            placeholder="Ex: 10.00">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_stock_level" class="form-label">Stock Maximum</label>
                                        <input type="number" class="form-control" id="max_stock_level"
                                            name="max_stock_level" min="0" step="0.01"
                                            placeholder="Ex: 100.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Familles Association Section -->
                            <div class="section-header mb-4">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-layer-group me-2"></i>Familles et Prix Spécifiques
                                </h6>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Sélectionnez une famille et ses prix standards seront affichés. Vous pouvez modifier
                                        ces prix pour ce produit spécifique.
                                    </div>
                                </div>
                            </div>

                            <div id="famillesContainer">
                                <!-- Famille rows will be added here -->
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-outline-primary" id="addFamilleBtn">
                                        <i class="fas fa-plus me-2"></i>Ajouter une Famille
                                    </button>
                                </div>
                            </div>

                            <!-- Template for famille row -->
                            <template id="familleRowTemplate">
                                <div class="famille-row mb-3 border rounded p-3">
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Famille</h6>
                                                <button type="button" class="btn btn-sm btn-danger remove-famille-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Famille *</label>
                                            <select class="form-control famille-select" name="familles[INDEX][famille_id]"
                                                required>
                                                <option value="">Sélectionner une famille</option>
                                                @foreach ($familles as $famille)
                                                    <option value="{{ $famille->famille_id }}"
                                                        data-prix-client="{{ $famille->prix_client }}"
                                                        data-prix-grossiste="{{ $famille->prix_grossiste }}"
                                                        data-prix-commercial="{{ $famille->prix_commercial }}"
                                                        data-prix-special="{{ $famille->prix_special }}">
                                                        {{ $famille->famille_name }} ({{ $famille->famille_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prix Client (DH) *</label>
                                            <input type="number" class="form-control famille-prix-client"
                                                name="familles[INDEX][prix_client]" min="0" step="0.01"
                                                required>
                                            <small class="text-muted prix-client-standard"></small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prix Grossiste (DH) *</label>
                                            <input type="number" class="form-control famille-prix-grossiste"
                                                name="familles[INDEX][prix_grossiste]" min="0" step="0.01"
                                                required>
                                            <small class="text-muted prix-grossiste-standard"></small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prix Commercial (DH) *</label>
                                            <input type="number" class="form-control famille-prix-commercial"
                                                name="familles[INDEX][prix_commercial]" min="0" step="0.01"
                                                required>
                                            <small class="text-muted prix-commercial-standard"></small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prix Spécial *</label>
                                            <input type="number" class="form-control famille-prix-special"
                                                name="familles[INDEX][prix_special]" min="0" step="0.01"
                                                required>
                                            <small class="text-muted prix-special-standard"></small>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Les prix standards de la famille sont affichés. Modifiez-les si ce
                                                produit a des prix différents.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Bill of Materials Section (for production products only) -->
                            <div class="section-header mb-4" id="bomSectionHeader" style="display: none;">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-list-alt me-2"></i>Nomenclature (BOM) *
                                </h6>
                            </div>

                            <div class="row mb-3" id="bomSection" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Important:</strong> Les produits de type "Production" nécessitent une
                                        nomenclature. Définissez les matières premières nécessaires pour produire ce
                                        produit.
                                    </div>

                                    <div id="bomContainer">
                                        <!-- BOM rows will be added here -->
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-outline-primary" id="addBomBtn">
                                                <i class="fas fa-plus me-2"></i>Ajouter une Matière Première
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Template for BOM row -->
                                    <template id="bomRowTemplate">
                                        <div class="bom-row mb-3 border rounded p-3">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Matière Première *</label>
                                                    <select class="form-control select2-material" name="bom_materials[]"
                                                        required>
                                                        <option value="">Sélectionner une matière</option>
                                                        @foreach ($rawMaterials as $material)
                                                            <option value="{{ $material->material_id }}"
                                                                data-unit="{{ $material->unit_of_measure }}">
                                                                {{ $material->material_name }}
                                                                ({{ $material->material_code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Quantité *</label>
                                                    <input type="number" class="form-control bom-quantity"
                                                        name="bom_quantities[]" min="0.001" step="0.001" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Unité</label>
                                                    <div class="form-control-plaintext bom-unit">-</div>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-danger remove-bom-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="section-header mb-4">
                                <h6 class="section-title bg-primary text-white p-2 rounded">
                                    <i class="fas fa-tags me-2"></i>Informations Complémentaires
                                </h6>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_active" class="form-label">Statut</label>
                                        <select class="form-control" id="is_active" name="is_active">
                                            <option value="1" selected>Actif</option>
                                            <option value="0">Inactif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Description du produit..."></textarea>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">
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

@push('stylesheets')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .section-header {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .famille-row,
        .bom-row {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .famille-row.removing,
        .bom-row.removing {
            opacity: 0;
            transform: translateX(-100%);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 0;
        }

        .prix-client-standard,
        .prix-grossiste-standard,
        .prix-commercial-standard,
        .prix-special-standard {
            font-size: 0.75rem;
            display: block;
            margin-top: 2px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                language: "fr",
                placeholder: "Sélectionner...",
                allowClear: true
            });

            let familleRowIndex = 0;

            $('#product_type').on('change', function() {
                toggleBomSection();
            });

            // Calculate volume when dimensions change
            function calculateVolume() {
                var height = parseFloat($('#height_m').val()) || 0;
                var width = parseFloat($('#width_m').val()) || 0;
                var depth = parseFloat($('#depth_m').val()) || 0;

                if (height > 0 && width > 0 && depth > 0) {
                    var volume = height * width * depth;
                    $('#volume_m3').val(volume.toFixed(4));
                } else {
                    $('#volume_m3').val('');
                }
            }

            $('#height_m, #width_m, #depth_m').on('change', calculateVolume);

            // Add famille row
            function addFamilleRow(data = null) {
                const template = document.getElementById('familleRowTemplate');
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.famille-row');
                const index = familleRowIndex++;

                // Replace INDEX placeholder
                row.innerHTML = row.innerHTML.replace(/INDEX/g, index);

                // Initialize Select2 for the new row
                $(row).find('.famille-select').select2({
                    language: "fr",
                    placeholder: "Sélectionner une famille...",
                    allowClear: true
                });

                // Add change event to famille select to load and display prices
                $(row).find('.famille-select').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const prixClient = selectedOption.data('prix-client') || 0;
                    const prixGrossiste = selectedOption.data('prix-grossiste') || 0;
                    const prixCommercial = selectedOption.data('prix-commercial') || 0;
                    const prixSpecial = selectedOption.data('prix-special') || 0;

                    // Set the price fields with the famille's standard prices
                    $(row).find('.famille-prix-client').val(prixClient);
                    $(row).find('.famille-prix-grossiste').val(prixGrossiste);
                    $(row).find('.famille-prix-commercial').val(prixCommercial);
                    $(row).find('.famille-prix-special').val(prixSpecial);

                    // Show the standard prices as reference
                    $(row).find('.prix-client-standard').text('Std: ' + prixClient + ' DH');
                    $(row).find('.prix-grossiste-standard').text('Std: ' + prixGrossiste + ' DH');
                    $(row).find('.prix-commercial-standard').text('Std: ' + prixCommercial + ' DH');
                    $(row).find('.prix-special-standard').text('Std: ' + prixSpecial + ' DH');
                });

                // If data is provided (for edit mode), fill the values
                if (data) {
                    const select = $(row).find('.famille-select');
                    select.val(data.famille_id).trigger('change');

                    $(row).find('input[name="familles[' + index + '][quantity_per_unit]"]').val(data
                        .quantity_per_unit);

                    // Set the price fields with the saved values (these may differ from standard)
                    setTimeout(() => {
                        $(row).find('.famille-prix-client').val(data.prix_client);
                        $(row).find('.famille-prix-grossiste').val(data.prix_grossiste);
                        $(row).find('.famille-prix-commercial').val(data.prix_commercial);
                        $(row).find('.famille-prix-special').val(data.prix_special);
                    }, 100);
                }

                // Add remove functionality
                $(row).find('.remove-famille-btn').click(function() {
                    $(row).addClass('removing');
                    setTimeout(() => {
                        $(row).remove();
                    }, 300);
                });

                $('#famillesContainer').append(row);
            }

            // Add initial famille row
            addFamilleRow();

            // Add BOM row
            function addBomRow() {
                const template = document.getElementById('bomRowTemplate');
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.bom-row');

                // Initialize Select2 for the new row
                $(row).find('.select2-material').select2({
                    language: "fr",
                    placeholder: "Sélectionner une matière...",
                    allowClear: true
                }).on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const unit = selectedOption.data('unit') || '-';
                    $(this).closest('.bom-row').find('.bom-unit').text(unit);
                });

                // Add remove functionality
                $(row).find('.remove-bom-btn').click(function() {
                    const rowElement = $(this).closest('.bom-row');
                    rowElement.addClass('removing');
                    setTimeout(() => {
                        rowElement.remove();
                    }, 300);
                });

                $('#bomContainer').append(row);
            }

            // Show/hide BOM section based on product type
            function toggleBomSection() {
                const productType = $('#product_type').val();

                if (productType === 'production') {
                    $('#bomSectionHeader').show();
                    $('#bomSection').show();
                    // Add initial BOM row if none exist
                    if ($('#bomContainer .bom-row').length === 0) {
                        addBomRow();
                    }
                } else {
                    $('#bomSectionHeader').hide();
                    $('#bomSection').hide();
                    // Clear BOM container when not production
                    $('#bomContainer').empty();
                }
            }

            // Event listeners
            $('#addFamilleBtn').click(function() {
                addFamilleRow();
            });

            $('#addBomBtn').click(addBomRow);

            // Validate BOM for production products
            function validateBom() {
                const productType = $('#product_type').val();

                if (productType === 'production') {
                    const bomRows = $('#bomContainer .bom-row').length;
                    // if (bomRows === 0) {
                    //     showToast('error', 'Ajoutez au moins une matière première à la nomenclature');
                    //     return false;
                    // }

                    let isValid = true;
                    $('#bomContainer .bom-row').each(function() {
                        const materialId = $(this).find('.select2-material').val();
                        const quantity = $(this).find('.bom-quantity').val();

                        if (!materialId || !quantity || parseFloat(quantity) <= 0) {
                            isValid = false;
                            return false;
                        }
                    });

                    if (!isValid) {
                        showToast('error', 'Veuillez remplir tous les champs requis de la nomenclature');
                        return false;
                    }
                }

                return true;
            }

            // Validate famille rows
            function validateFamilles() {
                const familleRows = $('#famillesContainer .famille-row').length;

                if (familleRows === 0) {
                    showToast('error', 'Veuillez ajouter au moins une famille');
                    return false;
                }

                let isValid = true;
                $('#famillesContainer .famille-row').each(function() {
                    const familleId = $(this).find('.famille-select').val();
                    const prixClient = $(this).find('.famille-prix-client').val();
                    const prixGrossiste = $(this).find('.famille-prix-grossiste').val();
                    const prixCommercial = $(this).find('.famille-prix-commercial').val();
                    const prixSpecial = $(this).find('.famille-prix-special').val();

                    if (!familleId) {
                        isValid = false;
                        showToast('error', 'Veuillez sélectionner une famille pour chaque ligne');
                        return false;
                    }

                    if (parseFloat(prixClient) < 0 || parseFloat(prixGrossiste) < 0 ||
                        parseFloat(prixCommercial) < 0 || parseFloat(prixSpecial) < 0) {
                        isValid = false;
                        showToast('error', 'Les prix ne peuvent pas être négatifs');
                        return false;
                    }
                });

                return isValid;
            }

            // Form submission
            $('#productForm').submit(function(e) {
                e.preventDefault();

                // Validate familles
                if (!validateFamilles()) {
                    return;
                }

                // Validate BOM
                if (!validateBom()) {
                    return;
                }

                // Disable submit button
                const submitBtn = $('#submitBtn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...');

                // Prepare form data
                const formData = new FormData(this);

                // Remove BOM data if not production
                if ($('#product_type').val() !== 'production') {
                    formData.delete('bill_of_materials');
                } else {
                    // Prepare BOM data for production products
                    const bomData = [];
                    $('#bomContainer .bom-row').each(function() {
                        const materialId = $(this).find('.select2-material').val();
                        const quantity = $(this).find('.bom-quantity').val();
                        const unit = $(this).find('.bom-unit').text();

                        if (materialId && quantity) {
                            bomData.push({
                                material_id: parseInt(materialId),
                                quantity_required: parseFloat(quantity),
                                unit_of_measure: unit,
                                scrap_factor: 0,
                                notes: null
                            });
                        }
                    });

                    if (bomData.length > 0) {
                        formData.append('bill_of_materials', JSON.stringify(bomData));
                    }
                }

                // Submit form
                $.ajax({
                    url: "{{ route('products.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            setTimeout(function() {
                                window.location.href = response.redirect ||
                                    "{{ route('products.index') }}";
                            }, 1500);
                        } else {
                            showToast('error', response.message);
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMessage = '';

                        if (errors) {
                            Object.values(errors).forEach(function(errorArray) {
                                errorArray.forEach(function(error) {
                                    errorMessage += error + '\n';
                                });
                            });
                        } else {
                            errorMessage = xhr.responseJSON?.message ||
                                'Une erreur est survenue lors de l\'enregistrement';
                        }

                        showToast('error', errorMessage);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Toast notification
            function showToast(type, message) {
                const toast = $(`
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')} border-0"
                         role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message.replace(/\n/g, '<br>')}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);

                $('#toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                setTimeout(() => toast.remove(), 5000);
            }
        });
    </script>
@endpush
