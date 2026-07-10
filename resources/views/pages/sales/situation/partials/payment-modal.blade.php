<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Ajouter un paiement - Commande
                    #{{ $order->order_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="paymentForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="order_id" value="{{ $order->order_id }}">

                <div class="modal-body">
                    <!-- Order Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <small>Total commande:</small>
                                <strong>{{ number_format($order->final_amount, 2, ',', '.') }} DH</strong>
                            </div>
                            <div class="col-md-4">
                                <small>Déjà payé:</small>
                                <strong class="text-success">{{ number_format($order->paid_amount, 2, ',', '.') }} DH</strong>
                            </div>
                            <div class="col-md-4">
                                <small>Reste à payer:</small>
                                <strong class="text-danger"
                                    id="modal-remaining">{{ number_format($order->final_amount - $order->paid_amount, 2, ',', '.') }}
                                    DH</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Client Balance Info -->
                    @if ($order->client->available_advance > 0)
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle me-1"></i>
                            Ce client a un solde disponible de
                            <strong>{{ number_format($order->client->available_advance, 2, ',', '.') }} DH</strong>
                        </div>
                    @endif

                    <!-- Credit Info -->
                    @if ($order->client->credit_limit > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-1"></i>
                            Crédit utilisé: <strong>{{ number_format($order->client->credit_usage, 2, ',', '.') }} DH</strong> /
                            Limite: <strong>{{ number_format($order->client->credit_limit, 2, ',', '.') }} DH</strong>
                            @php
                                $availableCredit = $order->client->credit_limit - $order->client->credit_usage;
                            @endphp
                            @if ($availableCredit > 0)
                                <br><small>Crédit disponible: {{ number_format($availableCredit, 2, ',', '.') }} DH</small>
                            @endif
                        </div>
                    @endif

                    <!-- Payment Form -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="payment_method" class="form-label">Méthode de paiement *</label>
                                <select class="form-control" id="payment_method" name="method" required>
                                    <option value="">Sélectionner</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="traite">Traite / Lettre de change</option>
                                    @if ($order->client->available_advance > 0)
                                        <option value="advance" class="advance-option">Solde client
                                            ({{ number_format($order->client->available_advance, 2, ',', '.') }} DH)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="payment_amount" class="form-label">Montant (DH) *</label>
                                <input type="number" class="form-control" id="payment_amount" name="amount"
                                    min="0.01" step="0.01" required>
                                <small class="text-muted">Reste à payer:
                                    <strong id="hint-remaining">{{ number_format($order->final_amount - $order->paid_amount, 2, ',', '.') }} DH</strong></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="payment_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Excess payment section (shown when amount > remaining) -->
                    <div id="excess-section" class="mt-3 p-3 rounded border border-warning bg-warning bg-opacity-10" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-warning fw-bold">
                                <i class="fas fa-exclamation-triangle me-2"></i>Excédent détecté
                            </h6>
                            <span class="badge bg-warning text-dark fs-6 px-3" id="excess-amount-badge">0,00 DH</span>
                        </div>
                        <p class="small text-muted mb-2">Ce paiement dépasse le reste dû. Que souhaitez-vous faire avec l'excédent ?</p>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="excess_action" id="excess_balance" value="balance" checked>
                            <label class="form-check-label" for="excess_balance">
                                <i class="fas fa-wallet me-1 text-success"></i>
                                <strong>Ajouter au solde client</strong>
                                <small class="text-muted ms-1">— l'excédent sera crédité sur le compte</small>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="excess_action" id="excess_orders_radio" value="orders">
                            <label class="form-check-label" for="excess_orders_radio">
                                <i class="fas fa-receipt me-1 text-primary"></i>
                                <strong>Payer d'autres commandes impayées</strong>
                            </label>
                        </div>

                        <!-- Unpaid orders panel -->
                        <div id="excess-orders-section" style="display:none;">
                            <div id="excess-orders-loading" class="text-center py-2">
                                <i class="fas fa-spinner fa-spin me-1"></i> Chargement des commandes…
                            </div>
                            <div id="excess-orders-table-wrap" style="display:none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Commande</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-end">Reste dû</th>
                                                <th class="text-end" style="width:130px">Appliquer (DH)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="excess-orders-tbody"></tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded small">
                                    <span>Excédent: <strong id="ex-total">0,00 DH</strong></span>
                                    <span>Alloué: <strong class="text-success" id="ex-allocated">0,00 DH</strong></span>
                                    <span>Non alloué: <strong id="ex-unallocated" class="text-warning">0,00 DH</strong>
                                        <i class="fas fa-info-circle ms-1" title="Le reste sera ajouté au solde client" data-bs-toggle="tooltip"></i>
                                    </span>
                                </div>
                            </div>
                            <div id="excess-orders-empty" class="text-center text-muted small py-2" style="display:none;">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Aucune autre commande impayée — l'excédent sera ajouté au solde client.
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fields Container -->
                    <div id="payment-details-container" class="mt-3 p-3 bg-light rounded"></div>

                    <!-- Notes -->
                    <div class="form-group mt-3">
                        <label for="payment_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="payment_notes" name="notes" rows="2" placeholder="Notes supplémentaires..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="submitPayment">
                        <i class="fas fa-save me-1"></i> Ajouter le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
