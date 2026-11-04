@extends('layouts.app')

@section('title', $cheque->exists ? 'Modifier le chèque' : 'Nouveau chèque')

@section('content')
    <div class="container-fluid" style="max-width:1100px !important">
        <div class="card card-body py-3 mb-4">
            <h4 class="mb-0 card-title">{{ $cheque->exists ? 'Modifier le chèque' : 'Nouveau chèque à imprimer' }}</h4>
        </div>
        @include('pages.printed-cheques._flash')

        <form method="POST" class="card"
            action="{{ $cheque->exists ? route('printed-cheques.update', $cheque) : route('printed-cheques.store') }}">
            @csrf
            @if ($cheque->exists) @method('PUT') @endif
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Banque *</label>
                    <select name="bank_key" class="form-select" required>
                        @foreach ($banks as $b)
                            <option value="{{ $b->key }}" @selected(old('bank_key', $cheque->bank_key) === $b->key)>{{ $b->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° de chèque</label>
                    <input name="cheque_number" class="form-control" value="{{ old('cheque_number', $cheque->cheque_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° de compte</label>
                    <input name="account_number" class="form-control" value="{{ old('account_number', $cheque->account_number) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Bénéficiaire *</label>
                    <input name="beneficiary" class="form-control" required value="{{ old('beneficiary', $cheque->beneficiary) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Montant (DH) *</label>
                    <input name="amount" id="amount" type="number" step="0.01" min="0.01" class="form-control" required value="{{ old('amount', $cheque->amount) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Langue des lettres</label>
                    <select name="amount_words_locale" id="locale" class="form-select">
                        <option value="fr" @selected(old('amount_words_locale', $cheque->amount_words_locale) === 'fr')>Français</option>
                        <option value="ar" @selected(old('amount_words_locale', $cheque->amount_words_locale) === 'ar')>العربية</option>
                    </select>
                </div>
                <div class="col-md-10">
                    <label class="form-label">Montant en lettres * <small class="text-muted">(rempli automatiquement, modifiable)</small></label>
                    <textarea name="amount_words" id="words" rows="2" class="form-control" required>{{ old('amount_words', $cheque->amount_words) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fait à *</label>
                    <input name="place" class="form-control" required value="{{ old('place', $cheque->place ?: 'Casablanca') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date *</label>
                    <input name="issue_date" type="date" class="form-control" required value="{{ old('issue_date', optional($cheque->issue_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payable à</label>
                    <input name="payable_at" class="form-control" value="{{ old('payable_at', $cheque->payable_at) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-control">{{ old('notes', $cheque->notes) }}</textarea>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-primary">{{ $cheque->exists ? 'Enregistrer' : 'Enregistrer et aller à l\'impression' }}</button>
                <a href="{{ route('printed-cheques.index') }}" class="btn btn-light">Annuler</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var timer, manual = {{ $cheque->exists ? 'true' : 'false' }};
        $('#words').on('input', function () { manual = true; });
        function refresh() {
            var amount = $('#amount').val();
            if (!amount || parseFloat(amount) <= 0) return;
            $.post("{{ route('printed-cheques.words') }}", {
                _token: "{{ csrf_token() }}", amount: amount, locale: $('#locale').val()
            }).done(function (r) { $('#words').val(r.words); manual = false; });
        }
        $('#amount, #locale').on('input change', function () {
            // Ne pas écraser une saisie manuelle sauf si le montant/la langue change.
            clearTimeout(timer); timer = setTimeout(refresh, 300);
        });
    })();
</script>
@endpush
