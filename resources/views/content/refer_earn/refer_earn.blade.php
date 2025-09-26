@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Minhas Compras')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/dashboards-analytics.js')
@endsection

@section('content')

    <div class="container">
        <h1 class="my-4">Indique e Ganhe</h1>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="referralLink" class="form-label">Seu Link de Indicação</label>
                    <div class="input-group">
                        <input type="text" id="referralLink" class="form-control"
                            value="{{ env('APP_URL') }}/indique_ganhe/register?code={{ $code }}" readonly>
                        <button class="btn btn-primary" onclick="copyLink()">Copiar</button>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Estatísticas de Indicação</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-info mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Pessoas Cadastradas</h6>
                                    <p class="display-4" id="totalRegistrations">{{ $refered_qnt }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Pessoas que Compraram</h6>
                                    <p class="display-4" id="totalPurchases">{{ $refered_qnt_bought }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-warning mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Valor Recebido por Indicação</h6>
                                    <p class="display-4" id="totalEarned">R$
                                        {{ number_format($refered_amount_earned, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Orientações de Indicação -->
        <div class="alert alert-info mt-3">
            <h6>Como Funciona:</h6>
            @php
                $value = 10;
            @endphp
            <ul class="mb-0">
                <li>Envie seu link para algum amigo.</li>
                <li>O seu amigo deve criar uma conta em nosso site com seu link.</li>
                <li>Ganhe um bônus de R$ {{ number_format($value, 2, ',', '.') }} na primeira compra do amigo que você
                    indicou!</li>
                <li>O seu amigo deve realizar uma compra mínima de R$ {{ number_format($value, 2, ',', '.') }} para que o
                    bônus seja ativado.</li>
            </ul>
        </div>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("referralLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // Para dispositivos móveis
            navigator.clipboard.writeText(copyText.value).then(() => {
                alert("Link copiado com sucesso!");
            }).catch(err => {
                console.error('Erro ao copiar o link: ', err);
            });
        }
    </script>


    <div class="card shadow-lg p-3 mb-5 bg-white rounded">
        <h5 class="card-header">Bônus de Indicação</h5>
        <div class="row mt-3">
            <div class="col-12">
                <div class="table-responsive">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário Indicador</th>
                                <th>Usuário Convidado</th>
                                <th>Convidado Comprou?</th>
                                <th>Bônus Pago?</th>
                                <th>Valor do Bônus</th>
                                <th>Data</th>
                                @if (auth()->user()->role->level_id == 'admin')
                                    <th>Ações</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referEarns as $referEarn)
                                <tr>
                                    <td>{{ $referEarn->id }}</td>
                                    <td>{{ isset($referEarn->referUser) ? $referEarn->referUser->name : 'N/A' }}</td>
                                    <td>{{ isset($referEarn->invitedUser) ? $referEarn->invitedUser->name : 'N/A' }}</td>
                                    <td>{{ $statusTranslations['invited_user_bought'][$referEarn->invited_user_bought] ?? 'Indefinido' }}
                                    </td>
                                    <td>{{ $statusTranslations['earn_paid'][$referEarn->earn_paid] ?? 'Indefinido' }}</td>
                                    <td>{{ $referEarn->amount ? 'R$ ' . number_format($referEarn->amount, 2, ',', '.') : 'N/A' }}
                                    </td>
                                    <td>{{ $referEarn->created_at }}</td>
                                    <td>
                                        @if (auth()->user()->role->level_id == 'admin')
                                            @if ($referEarn->earn_paid)
                                                <a href="{{ route('refer_earns_payback', $referEarn->id) }}"
                                                    class="btn btn-warning">Estornar</a>
                                            @endif
                                            @if (!$referEarn->earn_paid)
                                                <a href="{{ route('refer_earns_pay', $referEarn->id) }}"
                                                    class="btn btn-success">Pagar</a>
                                            @endif

                                            @if (!$referEarn->earn_paid)
                                                <a href="{{ route('refer_earns_delete', $referEarn->id) }}"
                                                    class="btn btn-danger"
                                                    onclick="return confirm('Tem certeza que deseja deletar este registro?')">Deletar</a>
                                            @endif

                                    </td>
                            @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Nenhum registro encontrado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Controles de paginação -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $referEarns->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
