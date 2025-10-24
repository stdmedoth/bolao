@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

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

<style>
  @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap');
  
  body, html {
    overflow-x: hidden;
  }

  .welcome-card {
    background-image: url('{{ asset("assets/img/illustrations/bg-xx.png") }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    overflow: hidden;
    font-family: Public+Sans, sans-serif;
  }

  .welcome-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0);
  }

  .welcome-card .card-body {
    position: relative;
    z-index: 1;
  }

  .welcome-card h1,
  .welcome-card h4 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
  }

  .welcome-card p,
  .welcome-card b {
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
  }

  .welcome-card b {
    font-weight: 600;
  }

  .welcome-card .btn {
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    min-width: 200px;
    text-align: center;
    margin-bottom: 8px;
  }

  .btn-group-custom {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
  }

  .announcement-board {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
  }
  
  .announcement-board h1 {
    color: #384551;
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 600;
  }
  
  .announcement-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
  }
  
  .announcement-list li {
    margin-bottom: 12px;
    padding-left: 5px;
    font-size: 1rem;
  }
  
  .announcement-list a {
    color: #3173A8;
    text-decoration: none;
    font-weight: 500;
  }
  
  .announcement-list a:hover {
    text-decoration: underline;
  }
  
  .banner-image {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 15px;
  }
  
  .banner-image img {
    max-width: 100%;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  /* Layout para Desktop */
  @media (min-width: 992px) {
    .desktop-order-1 {
      order: 1;
    }
    
    .desktop-order-2 {
      order: 2;
    }
  }
  
  /* Layout para Mobile */
  @media (max-width: 991px) {
    .mobile-order-1 {
      order: 1;
    }
    
    .mobile-order-2 {
      order: 2;
    }
  }
<style>
  .banner-image img {
    display: block;         /* remove espaçamentos automáticos */
    width: 100%;            /* ocupa toda a largura do container */
    height: auto;           /* mantém a proporção da imagem */
    margin: 0;              /* remove margens extras */
    padding: 0;             /* remove paddings extras */
  }

  .banner-image {
    margin: 0;
    padding: 0;
  }
</style>

<div class="row">
  <div class="col-12 mb-6 order-0">
    <div class="card welcome-card">
      <div class="row align-items-start">
        
        <!-- Coluna do Quadro de Avisos -->
        <div class="col-lg-5 desktop-order-2 mobile-order-2">
          <div class="card-body">
            <div class="announcement-board">
              <h1>QUADRO DE AVISOS:</h1>
              <ul class="announcement-list">
                <li>🎯 <b>Aviso 1:</b> Confira <b><a href="{{route('games')}}">Concursos Abertos!</a></b></li>
                <li>🎯 <b>Aviso 2:</b> Entre na <a href="https://chat.whatsapp.com/ECZ2DJ8TNkmK9aNNBOwNUp?mode=r_c" target="_blank" rel="noopener noreferrer"><b>Nossa Comunidade!</b></a></li>
                <li>🎯 <b>Aviso 3:</b> Dúvidas consulte <b><a href="https://api.whatsapp.com/send/?phone=5511979894075&text&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer">Nosso Suporte!</a></b></li>
                <li>🎯 <b>Dica:</b> Para repetir um jogo, procure o concurso finalizado!</li></br>
					 <li>🤖  <b>NOVO APP OFICIAL: </b></br> Faça o download para Android <a href="https://bolaoentreamigosvip.com/apk/Bolao-Entre-Amigos-1.0.apk" target="_blank" rel="noopener noreferrer"><b>Clique Aqui</b></a></li>
              </ul>
            </div>
          </div>
        </div>

<!-- Coluna Saudação + Banner -->
<div class="col-lg-7 desktop-order-1 mobile-order-1">
  <div class="card-body">
    <h4 style="color: #3173A8; font-size: 1.5rem; margin-bottom: 20px;">
      Olá, {{ auth()->user()->name }}
    </h4>

    <div class="btn-group-custom">
      <a href="/concursos" class="btn btn-sm btn-blink" style="margin-bottom: 20px;">
        </b>VEJA OS CONCURSOS
      </a>
    </div>


<!-- Estilo do botão piscando corrigido -->
<style>
  .btn-blink {
    background-color: #00FF00 !important;
    color: #000000 !important;
    box-shadow: 0 0 10px rgba(33, 33, 33, 0.5);
    border: none !important;
    animation: blink 1.5s infinite;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }

  .btn-blink:hover,
  .btn-blink:focus,
  .btn-blink:active {
    background-color: darkred !important;
    color: white !important;
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
    transform: scale(1.05);
    text-decoration: none;
  }

  @keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.5; }
  }
</style>


            <!-- Banner/Imagem -->
            <div class="banner-image">
              <img src="https://dev.bolaoentreamigosvip.com/assets/img/illustrations/slide-01h.png" alt="Banner do sistema">
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</div>


<!-- Resto do seu código (seções comentadas) -->
<!--
  <div class="col-lg-4 col-md-4 order-1">
    <div class="row">
      <div class="col-lg-6 col-md-12 col-6 mb-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-4">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded">
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true, aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                  <a class="dropdown-item" href="javascript:void(0);">View More</a>
                  <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Profit</p>
            <h4 class="card-title mb-3">$12,628</h4>
            <small class="text-success fw-medium"><i class='bx bx-up-arrow-alt'></i> +72.80%</small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 col-6 mb-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-4">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('assets/img/icons/unicons/wallet-info.png')}}" alt="wallet info" class="rounded">
              </div>
              <div class="dropdown">
                <button class="btn p-0, type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                  <a class="dropdown-item" href="javascript:void(0);">View More</a>
                  <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Sales</p>
            <h4 class="card-title mb-3">$4,679</h4>
            <small class="text-success fw-medium"><i class='bx bx-up-arrow-alt'></i> +28.42%</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  -->
  <!-- Total Revenue -->
   <!--
  <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
    <div class="card">
      <div class="row row-bordered g-0">
        <div class="col-lg-8">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="card-title mb-0">
              <h5 class="m-0 me-2">Total Revenue</h5>
            </div>
            <div class="dropdown">
              <button class="btn p-0" type="button" id="totalRevenue" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded bx-lg text-muted"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalRevenue">
                <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                <a class="dropdown-item" href="javascript:void(0);">Share</a>
              </div>
            </div>
          </div>
          <div id="totalRevenueChart" class="px-3"></div>
        </div>
        <div class="col-lg-4 d-flex align-items-center">
          <div class="card-body px-xl-9">
            <div class="text-center mb-6">
              <div class="btn-group">
                <button type="button" class="btn btn-outline-primary">
                  <script>
                  document.write(new Date().getFullYear() - 1)

                  </script>
                </button>
                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="javascript:void(0);">2021</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);">2020</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);">2019</a></li>
                </ul>
              </div>
            </div>

            <div id="growthChart"></div>
            <div class="text-center fw-medium my-6">62% Company Growth</div>

            <div class="d-flex gap-3 justify-content-between">
              <div class="d-flex">
                <div class="avatar me-2">
                  <span class="avatar-initial rounded-2 bg-label-primary"><i class="bx bx-dollar bx-lg text-primary"></i></span>
                </div>
                <div class="d-flex flex-column">
                  <small>
                    <script>
                    document.write(new Date().getFullYear() - 1)

                    </script>
                  </small>
                  <h6 class="mb-0">$32.5k</h6>
                </div>
              </div>
              <div class="d-flex">
                <div class="avatar me-2">
                  <span class="avatar-initial rounded-2 bg-label-info"><i class="bx bx-wallet bx-lg text-info"></i></span>
                </div>
                <div class="d-flex flex-column">
                  <small>
                    <script>
                    document.write(new Date().getFullYear() - 2)

                    </script>
                  </small>
                  <h6 class="mb-0">$41.2k</h6>
                </div>
              </div>
            </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  -->
  <!--/ Total Revenue -->
  <!--
  <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
    <div class="row">
      <div class="col-6 mb-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-4">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('assets/img/icons/unicons/paypal.png')}}" alt="paypal" class="rounded">
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                  <a class="dropdown-item" href="javascript:void(0);">View More</a>
                  <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Payments</p>
            <h4 class="card-title mb-3">$2,456</h4>
            <small class="text-danger fw-medium"><i class='bx bx-down-arrow-alt'></i> -14.82%</small>
          </div>
        </div>
      </div>
      <div class="col-6 mb-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between mb-4">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('assets/img/icons/unicons/cc-primary.png')}}" alt="Credit Card" class="rounded">
              </div>
              <div class="dropdown">
                <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded text-muted"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="cardOpt1">
                  <a class="dropdown-item" href="javascript:void(0);">View More</a>
                  <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                </div>
              </div>
            </div>
            <p class="mb-1">Transactions</p>
            <h4 class="card-title mb-3">$14,857</h4>
            <small class="text-success fw-medium"><i class='bx bx-up-arrow-alt'></i> +28.14%</small>
          </div>
        </div>
      </div>
      <div class="col-12 mb-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10">
              <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                <div class="card-title mb-6">
                  <h5 class="text-nowrap mb-1">Profile Report</h5>
                  <span class="badge bg-label-warning">YEAR 2022</span>
                </div>
                <div class="mt-sm-auto">
                  <span class="text-success text-nowrap fw-medium"><i class='bx bx-up-arrow-alt'></i> 68.2%</span>
                  <h4 class="mb-0">$84,686k</h4>
                </div>
              </div>
              <div id="profileReportChart"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
-->
  <!-- Order Statistics -->
   <!--
  <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <div class="card-title mb-0">
          <h5 class="mb-1 me-2">Order Statistics</h5>
          <p class="card-subtitle">42.82k Total Sales</p>
        </div>
        <div class="dropdown">
          <button class="btn text-muted p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded bx-lg"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Share</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-6">
          <div class="d-flex flex-column align-items-center gap-1">
            <h3 class="mb-1">8,258</h3>
            <small>Total Orders</small>
          </div>
          <div id="orderStatisticsChart"></div>
        </div>
        <ul class="p-0 m-0">
          <li class="d-flex align-items-center mb-5">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary"><i class='bx bx-mobile-alt'></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Electronic</h6>
                <small>Mobile, Earbuds, TV</small>
              </div>
              <div class="user-progress">
                <h6 class="mb-0">82.5k</h6>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-5">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-success"><i class='bx bx-closet'></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Fashion</h6>
                <small>T-shirt, Jeans, Shoes</small>
              </div>
              <div class="user-progress">
                <h6 class="mb-0">23.8k</h6>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-5">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-info"><i class='bx bx-home-alt'></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Decor</h6>
                <small>Fine Art, Dining</small>
              </div>
              <div class="user-progress">
                <h6 class="mb-0">849k</h6>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-secondary"><i class='bx bx-football'></i></span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">Sports</h6>
                <small>Football, Cricket Kit</small>
              </div>
              <div class="user-progress">
                <h6 class="mb-0">99</h6>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
-->
  <!--/ Order Statistics -->

  <!-- Expense Overview -->
  <!--
  <div class="col-md-6 col-lg-4 order-1 mb-6">
    <div class="card h-100">
      <div class="card-header nav-align-top">
        <ul class="nav nav-pills" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tabs-line-card-income" aria-controls="navs-tabs-line-card-income" aria-selected="true">Income</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab">Expenses</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab">Profit</button>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content p-0">
          <div class="tab-pane fade show active" id="navs-tabs-line-card-income" role="tabpanel">
            <div class="d-flex mb-6">
              <div class="avatar flex-shrink-0 me-3">
                <img src="{{asset('assets/img/icons/unicons/wallet.png')}}" alt="User">
              </div>
              <div>
                <p class="mb-0">Total Balance</p>
                <div class="d-flex align-items-center">
                  <h6 class="mb-0 me-1">$459.10</h6>
                  <small class="text-success fw-medium">
                    <i class='bx bx-chevron-up bx-lg'></i>
                    42.9%
                  </small>
                </div>
              </div>
            </div>
            <div id="incomeChart"></div>
            <div class="d-flex align-items-center justify-content-center mt-6 gap-3">
              <div class="flex-shrink-0">
                <div id="expensesOfWeek"></div>
              </div>
              <div>
                <h6 class="mb-0">Income this week</h6>
                <small>$39k less than last week</small>
              </div>
            </div>
            </div>
        </div>
      </div>
    </div>
  </div>
-->
  <!--/ Expense Overview -->

  <!-- Transactions -->
   <!--
  <div class="col-md-6 col-lg-4 order-2 mb-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">Transactions</h5>
        <div class="dropdown">
          <button class="btn text-muted p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded bx-lg"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
            <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
            <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
          </div>
        </div>
      </div>
      <div class="card-body pt-4">
        <ul class="p-0 m-0">
          <li class="d-flex align-items-center mb-6">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/paypal.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Paypal</small>
                <h6 class="fw-normal mb-0">Send money</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">+82.6</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-6">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/wallet.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Wallet</small>
                <h6 class="fw-normal mb-0">Mac'D</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">+270.69</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-6">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/chart.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Transfer</small>
                <h6 class="fw-normal mb-0">Refund</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">+637.91</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-6">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/cc-primary.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Credit Card</small>
                <h6 class="fw-normal mb-0">Ordered Food</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">-838.71</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center mb-6">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/wallet.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Wallet</small>
                <h6 class="fw-normal mb-0">Starbucks</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">+203.33</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{asset('assets/img/icons/unicons/cc-warning.png')}}" alt="User" class="rounded">
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="d-block">Mastercard</small>
                <h6 class="fw-normal mb-0">Ordered Food</h6>
              </div>
              <div class="user-progress d-flex align-items-center gap-2">
                <h6 class="fw-normal mb-0">-92.45</h6> <span class="text-muted">USD</span>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
-->
  <!--/ Transactions -->
</div>
@endsection