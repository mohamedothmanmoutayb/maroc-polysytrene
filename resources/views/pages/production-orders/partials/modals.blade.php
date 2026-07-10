<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Approuver l'ordre de production
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous approuver l'ordre de production suivant ?</p>
                <div class="alert alert-info">
                    <strong id="approveOrderNumberDisplay"></strong>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Une fois approuvé, l'ordre sera prêt pour la production.
                </div>
                <div id="approveMaterialsCheck" class="d-none">
                    <h6 class="mb-2">Vérification des matériaux :</h6>
                    <ul id="materialsList" class="list-group list-group-flush"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-info" id="confirmApprove">
                    <i class="fas fa-check-circle me-2"></i>Approuver
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>Annuler l'ordre de production
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Veuillez confirmer l'annulation de l'ordre :</p>
                <div class="alert alert-warning">
                    <strong id="cancelOrderNumberDisplay"></strong>
                </div>

                <div class="mb-3">
                    <label for="cancellationReason" class="form-label">
                        <i class="fas fa-comment-dots me-2"></i>Raison de l'annulation *
                    </label>
                    <select class="form-control" id="cancellationReason" required>
                        <option value="">Sélectionner une raison</option>
                        <option value="stock_insufficient">Stock insuffisant</option>
                        <option value="customer_cancelled">Commande client annulée</option>
                        <option value="technical_issue">Problème technique</option>
                        <option value="schedule_conflict">Conflit d'horaire</option>
                        <option value="quality_concerns">Problèmes de qualité</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="additionalNotes" class="form-label">
                        <i class="fas fa-sticky-note me-2"></i>Notes supplémentaires
                    </label>
                    <textarea class="form-control" id="additionalNotes" rows="3"
                        placeholder="Ajouter des détails sur l'annulation..."></textarea>
                </div>

                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Cette action ne peut être annulée.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-warning" id="confirmCancel">
                    <i class="fas fa-ban me-2"></i>Annuler l'ordre
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Start Modal -->
<div class="modal fade" id="startModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-play me-2"></i>Démarrer la production
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Confirmer le démarrage de la production pour l'ordre :</p>
                <div class="alert alert-primary">
                    <strong id="startOrderNumberDisplay"></strong>
                </div>

                <div id="startConversionInfo" class="alert alert-info d-none">
                    <i class="fas fa-exchange-alt me-2"></i>
                    <strong>Type : Conversion</strong>
                    <div class="mt-2">
                        <span id="sourceProductInfo"></span> →
                        <span id="targetProductInfo"></span>
                    </div>
                    <div class="mt-1">
                        <small>Taux de conversion: <span id="conversionRate"></span></small>
                    </div>
                </div>

                <div id="startMaterialsInfo" class="alert alert-warning d-none">
                    <i class="fas fa-boxes me-2"></i>
                    <strong>Les matériaux suivants seront consommés :</strong>
                    <ul id="materialsToConsume" class="mb-0 mt-2"></ul>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette action consommera les matériaux/réserves nécessaires et ne peut être annulée.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <button type="button" class="btn btn-primary" id="confirmStart">
                    <i class="fas fa-play me-2"></i>Démarrer la production
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Attention!</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer l'ordre de production :</p>
                <div class="alert alert-light">
                    <strong id="deleteOrderNumberDisplay"></strong>
                </div>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Toutes les données associées seront définitivement supprimées.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Annuler
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
