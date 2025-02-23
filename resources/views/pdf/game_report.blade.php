<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatório do Jogo</title>

  <style>
    /* Inclui algumas classes básicas do Bootstrap */
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 100%;
      max-width: 1140px;
      margin: 0 auto;
      padding: 15px;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .table th,
    .table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }

    .table th {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    .text-center {
      text-align: center;
    }

    .page-break {
      page-break-before: always;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="text-center">
      <img src="{{ public_path('images/logo.png') }}" style="width: 100px;">
      <h2>Bolão entre Amigos VIP</h2>
    </div>

    <h3>{{ $game->name }}</h3>
    <p><strong>Início do jogo:</strong> {{ $lastClosedHistory->created_at }}</p>

    <h3>Resultados</h3>
    <table class="table">
      <tr>
        <th>Descrição</th>
        <th>Números</th>
        <th>Data</th>
      </tr>
      @foreach($gameHistories as $history)
      <tr>
        <td>{{ $history->description }}</td>
        <td>{{ $history->numbers }}</td>
        <td>{{ $history->created_at }}</td>
      </tr>
      @endforeach
    </table>

    <div class="page-break"></div> <!-- Quebra de página para melhorar o layout -->

    <h3>Classificação</h3>
    <table class="table">
      <tr>
        <th>Nome</th>
        <th>Vendedor</th>
        <th>Pontos</th>
        <th>Números</th>
      </tr>
      @foreach($purchases_data as $purchase)
      <tr>
        <td>{{ $purchase['gambler_name'] }}</td>
        <td>{{ $purchase['seller'] }}</td>
        <td>{{ $purchase['points'] }}</td>
        <td>{{ $purchase['numbers'] }}</td>
      </tr>
      @endforeach
    </table>

    <h3>Números Sorteados</h3>
    <table class="table">
      <tr>
        @for ($i = 0; $i < 100; $i++)
          <td class="{{ in_array($i, $uniqueNumbers) ? 'text-danger font-weight-bold' : '' }}">
          {{ $i }}
          </td>
          @if(($i + 1) % 10 == 0)
      </tr>
      <tr>
        @endif
        @endfor
      </tr>
    </table>

    <h3>Prêmios</h3>
    <table class="table">
      <tr>
        <th>Nome</th>
        <th>Tipo</th>
        <th>Valor</th>
      </tr>
      @foreach($awards as $award)
      <tr>
        <td>{{ $award->name }}</td>
        <td>{{ $award->condition_type }}</td>
        <td>{{ $award->amount }}</td>
      </tr>
      @endforeach
    </table>

    <div class="page-break"></div>

    <h3>Compras</h3>
    @foreach($purchases->chunk(100) as $chunk)
    <table class="table">
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Vendedor</th>
        <th>Pontos</th>
        <th>Números</th>
      </tr>
      @foreach($chunk as $purchase)
      <tr>
        <td>{{ $purchase->id }}</td>
        <td>{{ $purchase->gambler_name }}</td>
        <td>{{ $purchase->user->name }}</td>
        <td>{{ count(array_intersect(explode(' ', $purchase->numbers), $uniqueNumbers)) }}</td>
        <td>{{ $purchase->numbers }}</td>
      </tr>
      @endforeach
    </table>
    <div class="page-break"></div>
    @endforeach

  </div>

</body>

</html>