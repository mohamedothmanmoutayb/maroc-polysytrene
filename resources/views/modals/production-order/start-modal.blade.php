    <div class="modal fade" id="startModal" tabindex="-1" role="dialog" aria-labelledby="startModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startModalLabel">
                        <i class="fas fa-play me-2"></i>Démarrer la production
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
