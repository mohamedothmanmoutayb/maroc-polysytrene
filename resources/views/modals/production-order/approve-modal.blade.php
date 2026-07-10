<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approuver l'ordre de production
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
