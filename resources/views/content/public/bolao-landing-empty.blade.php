@extends('layouts/blankLayout')

@section('title', 'Bolão - Página Pública')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
          <h2 class="fw-bold mb-3">Nenhum concurso disponível no momento</h2>
          <p class="text-muted mb-4">
            Assim que um novo bolão estiver ativo, todas as informações públicas serão exibidas aqui.
          </p>
          <a href="{{ url('/') }}" class="btn btn-primary">
            Voltar para a página inicial
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
