<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="fas fa-ban me-2"></i>Annuler l'ordre de production
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <strong>Attention :</strong> Cette action libérera les matériaux réservés et ne peut être annulée.
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
