<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório do Jogo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            background-color: blue;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 2140px;
            margin: 0 auto;
            padding: 2px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 2px solid #dddddd;
            padding: 5px;
            text-align: center;
        }

        .table th {
            background-color: blue;
            /* Corrigido: removi as aspas simples */
            color: #ffffff;
            /* Alterado para branco para melhor contraste */
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .card {
            border: 1px solid #dddddd;
            padding: 2px;
            margin: 3px;
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .title-div {
            border: 1px solid #D80000;
            background-color: #dddddd;
            border-radius: 1px;
        }

        /* Cores de fundo para cards */
        .bg-color-1 {
            background-color: #ffcccc;
        }

        /* Vermelho claro */
        .bg-color-2 {
            background-color: #ccffcc;
        }

        /* Verde claro */
        .bg-color-3 {
            background-color: #ccccff;
        }

        /* Azul claro */
        .bg-color-4 {
            background-color: #ffccff;
        }

        /* Rosa claro */
        .bg-color-5 {
            background-color: #ccffff;
        }

        /* Ciano claro */
        .bg-color-6 {
            background-color: #ffffcc;
        }

        /* Amarelo claro */
        .bg-color-7 {
            background-color: #ffcc99;
        }

        /* Laranja claro */
        .bg-color-8 {
            background-color: #d1c4e9;
        }

        /* Lilás claro */
    </style>
</head>

<body>

    <div class="container">
        <table class="table">
            <tr>
                <td style="border: 3px solid blue;">
                    <div class=" text-center">
                        <img src="{{ asset('assets/img/logos/logo.png') }}" style="width: 200px;">
                    </div>
                </td>
                <td style="border: 3px solid blue;">
                    <table class="table">
                        <tr>
                            <td colspan="2" style="border: 3px solid blue;">
                                <div style="border: 3px solid #D80000; background-color: #dddddd; border-radius: 50px;">
                                    <h1 style="font-size: 50px;font-weight: bold;">Bolão entre Amigos VIP</h1>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 3px solid blue;">
                                <div style="border: 3px solid #D80000; background-color: #dddddd; border-radius: 50px;">
                                    <h3>{{ $game->name }}</h3>
                                </div>
                            </td>
                            <td style="border: 3px solid blue;">
                                <div style="border: 3px solid #D80000; background-color: #dddddd; border-radius: 50px;">
                                    <p><strong>Início do jogo:</strong>
                                        {{ Carbon\Carbon::parse($lastClosedHistory->created_at)->format('d/m/Y') }}</p>

                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>


        <!-- Layout com três colunas para alinhar tudo na mesma linha -->
        <table class="table">
            <tr>
                <td style="border: 3px solid blue;">
                    <table class="table">
                        <tr>
                            <th>Descrição</th>
                            <th>Números Válidos</th>
                            <th>Data</th>
                        </tr>
                        @foreach ($gameHistories as $index => $history)
                            <tr <?php echo 'style="background-color:' . ($index % 2 == 0 ? '#ffffff' : '#add8e6') . ';"'; ?>>
                                <td>{{ $history->description }}</td>
                                <td>{{ collect(explode(' ', $history->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}
                                </td>
                                <td>{{ Carbon\Carbon::parse($history->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>

                <td style="border: 3px solid blue;">
                    <h3>Números Sorteados</h3>
                    <table class="table">
                        @for ($i = 0; $i < 100; $i++)
                            @if ($i % 10 == 0)
                                <!-- Abre uma nova linha a cada 10 números -->
                                <tr>
                            @endif
                            <td class="{{ in_array($i, $uniqueNumbers) ? 'text-danger font-weight-bold' : '' }}"
                                <?php echo 'style="background-color:' . (in_array($i, $uniqueNumbers) ? '#D80000' : '#686868') . '; color:' . (in_array($i, $uniqueNumbers) ? '#FFFFFF' : '#000000') . ';"'; ?>>

                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            @if (($i + 1) % 10 == 0)
                                <!-- Fecha a linha a cada 10 números -->
            </tr>
            @endif
            @endfor
        </table>
        </td>

        </tr>
        </table>

        <!-- Prêmios -->
        <table class="table" style="background-color: #dddddd;">
            @foreach ($awards->chunk(3) as $chunk)
                <tr>
                    @foreach ($chunk as $index => $award)
                        @php
                            // Define a classe com base no índice
                            $colorClass = 'bg-color-' . (($index % 8) + 1);
                        @endphp
                        <td>
                            <div class="card p-3 {{ $colorClass }}"
                                style="border: 5px solid #000000; border-radius: 15px;">
                                <h4>{{ $award->name }}</h4>
                                <p><strong>Prêmio:</strong> R$ {{ number_format($award->amount, 2, ',', '.') }}</p>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>

        <table class="table">
            @php
                // Calculate chunks to show more lines per page (adjust 15 to your desired row count per column)
                $rowsPerColumn = 47; // Change this number to control how many rows appear per column
                $purchasesChunks = $purchases->chunk($rowsPerColumn);
                $purchasesDataChunks = collect($purchases_data)->chunk($rowsPerColumn);

                // Get the maximum number of chunks between both data sets
                $maxChunks = max($purchasesChunks->count(), $purchasesDataChunks->count());
            @endphp

            @for ($i = 0; $i < $maxChunks; $i++)
                <tr style="page-break-inside: avoid;">
                    <!-- Left Column (Purchases) -->
                    <td style="vertical-align: top; page-break-inside: avoid; border: 3px solid blue;">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Vendedor</th>
                                <th>Pontos</th>
                                <th>Números</th>
                            </tr>
                            @if (isset($purchasesChunks[$i]))
                                @foreach ($purchasesChunks[$i] as $index => $purchase)
                                    <tr style="background-color: {{ $index % 2 == 0 ? '#ffffff' : '#add8e6' }};">
                                        <td>{{ $purchase->id }}</td>
                                        <td>{{ $purchase->gambler_name }}</td>
                                        @if (in_array($purchase->user->role->level_id, ['seller']))
                                            <td>{{ $purchase->user->name }}</td>
                                        @elseif (in_array($purchase->user->role->level_id, ['admin']))
                                            <td>Banca Central</td>
                                        @elseif ($purchase->user->invited_by_id)
                                            <td>{{ in_array(\App\Models\User::find($purchase->user->invited_by_id)->role->level_id, ['gambler']) ? 'Banca Central' : \App\Models\User::find($purchase->user->invited_by_id)->name }}
                                            </td>
                                        @endif
                                        <td>{{ count(array_intersect(explode(' ', $purchase->numbers), $uniqueNumbers)) }}
                                        </td>
                                        <td>{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </table>
                    </td>

                    <!-- Right Column (Purchase Data) -->
                    <td style="vertical-align: top; page-break-inside: avoid;border: 3px solid blue;">
                        <table class="table">
                            <tr>
                                <th colspan="3">Classificação</th>
                            </tr>
                            @if (isset($purchasesDataChunks[$i]))
                                @foreach ($purchasesDataChunks[$i] as $index => $purchase)
                                    <tr style="background-color: {{ $index % 2 == 0 ? '#ffffff' : '#fdcec9' }};">
                                        <td>{{ $purchase['gambler_name'] }}</td>
                                        <td>{{ $purchase['seller'] }}</td>
                                        <td>{{ $purchase['points'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </table>
                    </td>
                </tr>
            @endfor
        </table>

    </div>

</body>

</html>
