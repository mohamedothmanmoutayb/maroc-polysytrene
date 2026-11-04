@extends('layouts.app')

@section('title', 'Chèques à imprimer')

@section('content')
    <div class="container-fluid" style="max-width:1531px !important">
        <div class="card card-body py-3 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h4 class="mb-0 card-title">Chèques à imprimer</h4>
                <div class="d-flex gap-2">
                    @can('create_checks')
                        <a href="{{ route('cheque-banks.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-ruler-combined me-1"></i>Banques &amp; dimensions</a>
                        <a href="{{ route('printed-cheques.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Nouveau chèque</a>
                    @endcan
                </div>
            </div>
        </div>

        @include('pages.printed-cheques._flash')

        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Bénéficiaire ou n° de chèque"></div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Tous les statuts</option>
                            @foreach (\App\Models\PrintedCheque::STATUSES as $k => $s)
                                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $s[0] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-sm btn-primary">Filtrer</button></div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Date</th><th>N°</th><th>Banque</th><th>Bénéficiaire</th><th class="text-end">Montant</th><th>Statut</th><th class="text-center">Impr.</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($cheques as $c)
                            <tr>
                                <td>{{ $c->issue_date->format('d/m/Y') }}</td>
                                <td>{{ $c->cheque_number ?: '—' }}</td>
                                <td>{{ $c->bank->label ?? $c->bank_key }}</td>
                                <td>{{ $c->beneficiary }}</td>
                                <td class="text-end">{{ number_format($c->amount, 2, ',', ' ') }} DH</td>
                                <td><span class="badge bg-{{ $c->statusColor() }}">{{ $c->statusLabel() }}</span></td>
                                <td class="text-center">{{ $c->print_count }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('printed-cheques.print', $c) }}" class="btn btn-sm btn-outline-primary" title="Imprimer"><i class="fas fa-print"></i></a>
                                    @can('create_checks')
                                        <a href="{{ route('printed-cheques.edit', $c) }}" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="fas fa-edit"></i></a>
                                        <form method="POST" action="{{ route('printed-cheques.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Supprimer ce chèque ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">Aucun chèque</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $cheques->links() }}
            </div>
        </div>
    </div>
@endsection
