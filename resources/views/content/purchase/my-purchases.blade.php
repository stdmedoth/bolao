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
  @if (!in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
  <h1 class="my-4">Minhas Compras</h1>
  @else
  <h1 class="my-4">Compras realizadas</h1>
  @endif

  <div class="modal" tabindex="-1" id="modal_repeat_game">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ url('/purchase/repeat') }}" method="POST" class="mb-4">
          @csrf
          @method('POST')

          <div class="modal-header">
            <h5 class="modal-title">Repetir jogo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="col-md-12">
              <div class="form-group">
                <label for="repeat_game_id">Jogo para repetir</label>
                <select name="repeat_game_id" class="form-select">
                  @foreach ($games as $game)
                  <option value="{{ $game->id }}" {{ (request('game_id') == $game->id) ? 'selected' : '' }}>{{ $game->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="repeat_game_numbers">Números para repetir</label>
                <input id="repeat_game_numbers_id" name="repeat_game_numbers" type="text" disabled class="form-control" value="">
              </div>
              <input id="repeat_game_purchase_id" name="repeat_game_purchase_id" type="hidden" class="form-control" value="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button id="repeat_game_repeat_button_id" type="submit" class="btn btn-primary">Repetir</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabela de Compras -->
  <div class="card">

    <div class="table-responsive text-nowrap">

      <!-- Formulário de Pesquisa e Filtro -->
      <form action="{{ url('/minhas_compras') }}" method="GET" class="mb-4">
        <div class="row">
          <div class="col-md-4">
            <!-- Campo de pesquisa -->
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Pesquisar por nome do concurso, numeros..." value="{{ request('search') }}">
              <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
          </div>

          <div class="col-md-3">
            <!-- Select de filtro por role -->
            <select name="game_id" class="form-select">
              <option value="">Todos Concursos</option>
              @foreach ($games as $game)
              <option value="{{ $game->id }}" {{ (request('game_id') == $game->id) ? 'selected' : '' }}>{{ $game->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <!-- Select de filtro por role -->
            <select name="status" class="form-select">
              <option value="">Todos os status</option>
              @foreach (['PAID', 'PENDING', 'CANCELED', 'FINISHED'] as $status)
              <option value="{{ $status }}" {{ (request('status') == $status) ? 'selected' : '' }}>{{ __($status) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <button class="btn btn-secondary w-100" type="submit">Aplicar Filtros</button>
          </div>
        </div>
      </form>


      <table class="table">
        <thead>
          <tr>
            <th>Jogo</th>
            <th>Apostador</th>
            @if (in_array(Auth::user()->role->level_id, ['admin' , 'seller']))
            <!-- Usuario de quem Comprou -->
            <th>Usuário</th>
            @endif
            @if (in_array(Auth::user()->role->level_id, ['admin' , 'seller']))
            <th>Vendedor</th>
            @endif
            <th>Compra em</th>
            <th>Números</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <!-- Aqui iteramos sobre as compras -->

          <!-- A collection de compras como uma só -->
          @foreach ($purchases as $purchase)
          <tr>
            <td>
              <a href='/concursos/{{$purchase->game->id}}'>
                <!-- Mostrando o nome do jogo relacionado -->
                <i class="bx bxl-game bx-md text-info me-4"></i>
                <span>{{ $purchase->game ? $purchase->game->name : '-' }}</span>
                <!-- Nome do jogo é o dia na semana em que se passa a aposta-->
              </a>
            </td>
            <td>
              <span class="badge bg-label-primary me-1">{{ __($purchase->gambler_name) }}</span>
            </td>

            @if (in_array(auth()->user()->role->level_id, ['admin' , 'seller']))
            <!-- Usuario de quem Comprou -->
            <td>{{ $purchase->user->name }}</td>

            <!-- Quem é o Vendedor -->

            <!-- Se foi o vendedor que comprou, então ele é o proprio vendedor -->
            @if (in_array($purchase->user->role->level_id, ['seller']))
            <td>{{ $purchase->user->name }}</td>

            <!-- Se foi o admin que comprou, então a banca central é o vendedor -->
            @elseif (in_array($purchase->user->role->level_id, ['admin']))
            <td>Banca Central</td>

            <!-- Se foi o apostador que comprou, então verifica se foi um vendedor que indicou -->
            @elseif ($purchase->user->invited_by)
            <td>{{ in_array($purchase->user->invited_by->role->level_id, ['gambler']) ? 'Banca Central'  : $purchase->user->invited_by->name  }}</td>
            @endif

            @endif


            <!-- Usar timestamp do próprio produto? -->

            <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
            <td> {{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}</td>
            <td>
              <span class="badge bg-label-primary me-1">{{ __($purchase->status) }}</span>
            </td>
            <td>
              <a href="{{ route('purchase-pay', array_merge([$purchase->id], request()->query())) }}"
                class="btn btn-success {{ (!in_array($purchase->user->role->level_id, ['admin']) && (($purchase->status !== 'PENDING') || ($purchase->game->status == 'CLOSED'))) ? 'disabled' : ''}}">
                Pagar
              </a>

              <a href="{{ route('purchase-withdraw', array_merge([$purchase->id], request()->query())) }}"
                class="btn btn-warning {{ (!in_array($purchase->user->role->level_id, ['admin']) && (($purchase->status !== 'PAID') || ($purchase->game->status == 'CLOSED'))) ? 'disabled' : ''}}">
                Estornar
              </a>

              <a href="{{ route('purchases.destroy', array_merge([$purchase->id], request()->query())) }}"
                class="btn btn-danger {{ (!in_array($purchase->user->role->level_id, ['admin']) && (($purchase->status == 'PAID') || ($purchase->game->status == 'CLOSED'))) ? 'disabled' : ''}}">
                Deletar
              </a>

              <a href="#"
                data-purchase_id="{{$purchase->id}}"
                data-numbers="{{ collect(explode(' ', $purchase->numbers))->map(fn($num) => str_pad($num, 2, '0', STR_PAD_LEFT))->implode(' ') }}"
                class="btn btn-secondary repeat_game_button">
                Repetir
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <!-- Controles de paginação -->
      <div class="d-flex justify-content-center mt-4">
        {{ $purchases->appends(request()->all())->links('pagination::bootstrap-5') }}
      </div>

    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {

    repeat_game_buttons = document.getElementsByClassName('repeat_game_button');
    repeat_game_repeat_button_id = document.getElementsByClassName('repeat_game_repeat_button_id');

    for (var i = 0; i < repeat_game_buttons.length; i++) {
      (function(index) {
        repeat_game_buttons[index].addEventListener("click", function(e) {
          var myModal = new bootstrap.Modal(document.getElementById('modal_repeat_game'), {
            focus: true
          });

          numbers = e.target.getAttribute('data-numbers');
          repeat_game_numbers_id = document.getElementById('repeat_game_numbers_id')
          repeat_game_numbers_id.value = numbers;

          purchase_id = e.target.getAttribute('data-purchase_id');
          repeat_game_purchase_id = document.getElementById('repeat_game_purchase_id')
          repeat_game_purchase_id.value = purchase_id;

          myModal.show();

        });
      })(i);
    }
  });
</script>
@endsection