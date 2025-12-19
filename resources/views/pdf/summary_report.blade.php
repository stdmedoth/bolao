<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo de Transações</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            background-color: #ff0000;
            color: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #5a67d8;
            /* Removi text-align: center para permitir controle via tabela */
        }

        .app-name {
            display: inline-block;
            /* Essencial para o background branco envolver o texto */
            padding: 10px 20px;
            /* Aumentei o padding */
            border-radius: 5px;
            background-color: white;
            color: black;
            font-size: 20px;
            /* Aumentei a fonte como pediu */
            font-weight: bold;
        }

        .report-date {
            font-size: 10px;
            color: #ffffff;
            text-align: right;
            /* Força a data para a direita */
            margin-top: 5px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #ffffff;
            font-weight: bold;
        }

        .header p {
            font-size: 12px;
            color: #ffffff;
        }

        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-section h3 {
            color: #495057;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #495057;
            padding: 5px 10px 5px 0;
            width: 40%;
        }

        .info-value {
            display: table-cell;
            color: #212529;
            padding: 5px 0;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .summary-card.income {
            background-color: #d4edda;
            border-color: #28a745;
        }

        .summary-card.outcome {
            background-color: #f8d7da;
            border-color: #dc3545;
        }

        .summary-card.net {
            background-color: #d1ecf1;
            border-color: #17a2b8;
        }

        .summary-card h4 {
            font-size: 12px;
            color: #495057;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .summary-card .amount {
            font-size: 18px;
            font-weight: bold;
        }

        .summary-card.income .amount {
            color: #155724;
        }

        .summary-card.outcome .amount {
            color: #721c24;
        }

        .summary-card.net .amount {
            color: #0c5460;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .table thead {
            background-color: #667eea;
            color: white;
        }

        .table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #5a67d8;
        }

        .table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-success {
            color: #28a745;
            font-weight: bold;
        }

        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        .page-break {
            page-break-before: always;
        }

        .filter-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .filter-info strong {
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="header" style="background-color: #ff0000; padding: 20px; margin-bottom: 20px; border: 2px solid #ffffff;">
        <table width="100%" style="border: none; margin: 0; padding: 0;">
            <tr>
                <td align="left" style="width: 40%; vertical-align: middle; border: none;">
                    <img width="150px" src="{{ public_path('assets/img/logos/logo.png') }}" alt="Logo"
                        class="logo" />
                </td>

                <td align="right" style="vertical-align: middle; border: none;">
                    <div class="app-name">Bolão entre amigos vip</div>
                    <p class="report-date">Relatório gerado em {{ date('d/m/Y H:i:s') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Informações do Usuário -->
    <div class="info-section">
        <h3>Informações do Usuário</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nome:</div>
                <div class="info-value">{{ $userInfo['name'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Porcentagem de Comissão:</div>
                <div class="info-value">{{ number_format($userInfo['comission_percent'], 2, ',', '.') }}%</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total de Jogos:</div>
                <div class="info-value">{{ $userInfo['total_games'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if ($filterInfo['game'] || $filterInfo['month'] || $filterInfo['seller'])
        <div class="filter-info">
            <strong>Filtros Aplicados:</strong><br>
            @if ($filterInfo['game'])
                Concurso: {{ $filterInfo['game']->name }}<br>
            @endif
            @if ($filterInfo['month'])
                Mês:
                {{ ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'][$filterInfo['month']] }}<br>
            @endif
            @if ($filterInfo['seller'])
                Vendedor: {{ $filterInfo['seller']->name }}<br>
            @endif
        </div>
    @endif

    <!-- Resumo Financeiro -->
    <div class="summary-cards">
        <div class="summary-card income">
            <h4>Limite Inicial de Crédito</h4>
            <div class="amount">R$ {{ number_format($userInfo['game_credit_limit'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card income">
            <h4>Crédito Atual</h4>
            <div class="amount">R$ {{ number_format($userInfo['game_credit'], 2, ',', '.') }}</div>
        </div>
        <div class="summary-card outcome">
            <h4>Saldo Devedor</h4>
            <div class="amount {{ $userInfo['credit_debt'] >= 0 ? 'text-success' : 'text-danger' }}">
                R$ {{ number_format($userInfo['credit_debt'], 2, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Tabela Detalhada -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Descrição</th>
                <th style="width: 20%;">Concurso</th>
                <th style="width: 12%;">Quantidade</th>
                <th style="width: 15%;">Usuário</th>
                <th style="width: 18%;">Total</th>
                <th style="width: 10%;">Categoria</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $isIncome = $row['category'] === 'income';
                @endphp
                <tr>
                    <td><strong>{{ $row['type'] }}</strong></td>
                    <td>{{ $row['game_name'] }}</td>
                    <td style="text-align: center;">{{ $row['quantity'] }}</td>
                    <td style="font-size: 9px; color: #6c757d;">
                        {{ isset($row['user_name']) && $row['user_name'] ? $row['user_name'] : '-' }}
                    </td>
                    <td style="text-align: right;" class="{{ $isIncome ? 'text-success' : 'text-danger' }}">
                        {{ $isIncome ? 'R$' : '-R$' }} {{ number_format($row['total'], 2, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $isIncome ? 'badge-success' : 'badge-danger' }}">
                            {{ $isIncome ? 'Entrada' : 'Saída' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #6c757d;">
                        Nenhum registro encontrado para os filtros selecionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema em {{ date('d/m/Y') }} às {{ date('H:i:s') }}</p>
        <p>Usuário: {{ $selectedUser->name ?? 'N/A' }} | Email: {{ $selectedUser->email ?? 'N/A' }}</p>
    </div>
</body>

</html>
