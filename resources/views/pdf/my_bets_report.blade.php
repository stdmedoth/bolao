<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Apostas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            font-weight: bold;
        }

        .header {
            background-color: #ff0000;
            color: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #ffffff;
        }

        .header-content {
            text-align: left;
            padding-left: 50px;
            vertical-align: middle;
        }

        .app-name {
            display: inline-block;
            padding: 5px 105px;
            border-radius: 5px;
            background-color: white;
            color: black;
            font-size: 20px;
            font-family: 'Arial Black', sans-serif;
            font-weight: 900;
            /* Peso máximo */
            margin-bottom: 10px;
            white-space: nowrap;
        }

        .game-name {
            display: inline-block;
            padding: 5px 100px;
            border-radius: 5px;
            background-color: white;
            color: #0000CD;
            /* Azul mais forte (MediumBlue) ou #2604cc */
            font-size: 18px;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 900;
            /* Peso máximo */
            margin-bottom: 10px;
            white-space: nowrap;
        }

        .header-info {
            font-size: 16px;
            color: #00FF00;
            /* Verde neon puro (Lime) para destacar no vermelho */
            font-family: Arial, Helvetica, sans-serif;
            vertical-align: middle;
            margin-top: 8px;
            font-weight: 900;
            /* Peso máximo */
            text-transform: uppercase;
            /* Transforma em MAIÚSCULO igual a imagem */
            white-space: nowrap;
            text-shadow: 1px 1px 0 #000;
            /* Opcional: dá um contorno leve para leitura */
        }

        .header-info-row {
            display: inline-block;
            margin: 0 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .table thead {
            background-color: #f8f9fa;
            color: #495057;
        }

        .table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #495057;
        }

        .table td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .numbers-container {
            display: inline-block;
            text-align: center;
            white-space: nowrap;
            overflow: visible;
        }

        .number-ball {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 600;
            text-align: center;
            line-height: 20px;
            margin: 1px;
            border: 1px solid;
            flex-shrink: 0;
        }

        .number-ball.hit {
            background-color: #fbbf24;
            color: #1a365d;
            border-color: #f59e0b;
        }

        .number-ball.miss {
            background-color: #e5e7eb;
            color: #6b7280;
            border-color: #d1d5db;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <table width="100%" style="border: none; margin: 0; padding: 0;">
            <tr>
                <td align="left" style="width: 30%; vertical-align: middle; border: none;">
                    <img width="150px" src="{{ public_path('assets/img/logos/logo.png') }}" alt="Logo"
                        class="logo" />
                </td>

                <td align="center" style="vertical-align: middle; border: none;">
                    <div class="header-content">
                        <div class="app-name">Bolão entre amigos vip</div>
                        <div class="game-name">{{ $game->name }}</div>
                        <!-- Informações em verde claro lado a lado -->
                        <div class="header-info">
                            <span class="header-info-row">{{ $userName }}</span>
                            <span class="header-info-row">Quantidade de Jogos: {{ $totalGames }}</span>
                        </div>
                    </div>
                </td>

                <td style="width: 30%; border: none;"></td>
            </tr>
        </table>
    </div>

    <!-- Tabela de Apostas -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Participante</th>
                <th style="width: 10%;">Pts</th>
                <th style="width: 40%;">Números</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 20%;">Pago por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchasesData as $purchase)
                <tr>
                    <td>{{ $purchase['participant'] }}</td>
                    <td style="text-align: center;">
                        @if ($purchase['status'] == 'PAID')
                            <span class="badge badge-{{ $purchase['badge_color'] }}">{{ $purchase['points'] }}</span>
                        @else
                            <span style="color: #6c757d;">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="numbers-container">
                            @foreach ($purchase['numbers'] as $numData)
                                <span class="number-ball {{ $numData['isHit'] ? 'hit' : 'miss' }}">
                                    {{ $numData['number'] }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td style="text-align: center;">
                        @php
                            $statusLabels = [
                                'PAID' => 'Pago',
                                'PENDING' => 'Pendente',
                                'CANCELED' => 'Cancelado',
                                'FINISHED' => 'Finalizado',
                            ];
                            $statusLabel = $statusLabels[$purchase['status']] ?? $purchase['status'];

                            $statusBadge = 'badge-secondary';
                            if ($purchase['status'] === 'PAID') {
                                $statusBadge = 'badge-success';
                            } elseif ($purchase['status'] === 'PENDING') {
                                $statusBadge = 'badge-warning';
                            } elseif ($purchase['status'] === 'CANCELED') {
                                $statusBadge = 'badge-danger';
                            } elseif ($purchase['status'] === 'FINISHED') {
                                $statusBadge = 'badge-info';
                            }
                        @endphp
                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </td>
                    <td style="text-align: center; font-size: 9px;">{{ $purchase['paid_by'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6c757d;">
                        Nenhuma aposta encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo sistema em {{ date('d/m/Y') }} às {{ date('H:i:s') }}</p>
        <p>Usuário: {{ $userName }} | Concurso: {{ $game->name }}</p>
    </div>
</body>

</html>
