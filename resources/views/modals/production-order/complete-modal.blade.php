<!-- Complete with BOM Consumption Modal -->
<div class="modal fade" id="completeOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Terminer la Production - Saisie des Consommations
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeOrderForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Veuillez renseigner les quantités réellement consommées pour chaque matière première et les
                        résultats de production.
                    </div>

                    <!-- Order Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations de l'Ordre</h6>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td width="40%">Numéro d'Ordre:</td>
                                    <td><strong id="completeOrderNumber"></strong></td>
                                </tr>
                                <tr>
                                    <td>Produit:</td>
                                    <td><strong id="completeProductName"></strong></td>
                                </tr>
                                <tr>
                                    <td>Quantité Planifiée:</td>
                                    <td><strong id="completePlannedQuantity"></strong></td>
                                </tr>
                                <tr>
                                    <td>Déjà Produit:</td>
                                    <td><strong id="completeAlreadyProduced"></strong></td>
                                </tr>
                                <tr class="table-warning">
                                    <td>Restant à Produire:</td>
                                    <td><strong id="completeRemaining"></strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Résultats de Production</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_produced" class="form-label">Quantité Produite *</label>
                                    <input type="number" class="form-control" id="quantity_produced"
                                        name="quantity_produced" required min="1">
                                    <small class="form-text text-muted" id="maxQuantityHelp"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_defective" class="form-label">Quantité Défectueuse *</label>
                                    <input type="number" class="form-control" id="quantity_defective"
                                        name="quantity_defective" required min="0" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="production_date" class="form-label">Date de Production *</label>
                                    <input type="date" class="form-control" id="production_date"
                                        name="production_date" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quality_grade" class="form-label">Qualité</label>
                                    <select class="form-control" id="quality_grade" name="quality_grade">
                                        <option value="excellent">Excellent</option>
                                        <option value="good" selected>Bon</option>
                                        <option value="average">Moyen</option>
                                        <option value="poor">Mauvais</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOM Consumption Table with Detailed Calculation -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-calculator me-2"></i>Calcul et Saisie des Consommations Matières
                                Premières
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Les quantités "Réellement Utilisées" sont pré-calculées en fonction de la
                                        quantité produite</li>
                                    <li>Vous pouvez modifier ces valeurs si la consommation réelle est différente</li>
                                    <li>Les déchets/pertes doivent être saisis séparément</li>
                                    <li>Le stock disponible est vérifié en temps réel</li>
                                </ul>
                            </div>

                            <!-- Calculation Summary -->
                            <div class="alert alert-info mb-3" id="calculationSummary">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Calcul automatique :</strong>
                                <span id="calcFormula">(Quantité Produite × Quantité/Unité) = Quantité Réellement
                                    Utilisée</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Matière Première</th>
                                            <th class="text-center">Stock Disponible</th>
                                            <th class="text-center">Quantité/Unité</th>
                                            <th class="text-center">Quantité Planifiée</th>
                                            <th class="text-center">Quantité Réellement Utilisée</th>
                                            <th class="text-center">Déchets/Pertes</th>
                                            <th class="text-center">Unité</th>
                                            <th class="text-center">Statut Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bomConsumptionTable">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="8" class="text-center text-muted">
                                                <small>
                                                    <i class="fas fa-lightbulb me-1"></i>
                                                    Les valeurs sont calculées automatiquement. Modifiez-les si
                                                    nécessaire.
                                                </small>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Quick Actions -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            id="resetToCalculated">
                                            <i class="fas fa-redo me-1"></i> Réinitialiser aux valeurs calculées
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm"
                                            id="copyPlannedToActual">
                                            <i class="fas fa-copy me-1"></i> Copier Planifié → Réel
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="skipConsumption"
                                            name="skip_consumption">
                                        <label class="form-check-label text-muted" for="skipConsumption">
                                            <small>Ignorer la saisie des consommations (pour production non
                                                matérielle)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Production Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Résumé de la Production
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Efficacité Matières</h6>
                                            <h3 id="materialEfficiency">100%</h3>
                                            <small class="text-muted">(Réel / Planifié)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Défauts</h6>
                                            <h3 id="defectPercentage">0%</h3>
                                            <small class="text-muted">(Défectueux / Produit)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Progression Ordre</h6>
                                            <h3 id="orderCompletion">0%</h3>
                                            <small class="text-muted">(Total Produit / Planifié)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note me-2"></i>Notes et Observations
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Observations, problèmes rencontrés, ajustements de consommation..."></textarea>
                    </div>

                    <!-- Validation Alert -->
                    <div class="alert alert-danger d-none" id="completeValidationAlert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div id="completeValidationMessage"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success" id="completeSubmitBtn">
                        <i class="fas fa-check me-1"></i> Enregistrer et Terminer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
