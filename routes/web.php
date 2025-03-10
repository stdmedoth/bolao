<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\UserAwardController;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\GameAwardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReferEarnController;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\WithdrawalController;

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('login');
Route::post('/auth/login-basic', [LoginBasic::class, 'validate'])->name('validate-login');
Route::get('/auth/logout', [LoginBasic::class, 'logout'])->name('logout');

Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('register');
Route::post('/auth/register-basic', [RegisterBasic::class, 'validate'])->name('register-validate');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('reset-password');


Route::middleware('auth:web')->group(function () {
  Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

  Route::get('/concursos/create_game_form', [AdminController::class, 'create_game_form'])->name('create-game-form');
  Route::post('/concursos/create', [AdminController::class, 'createGame'])->name('create-game');
  Route::get('/concursos/open/{id}', [AdminController::class, 'openGame'])->name('open-game');
  Route::get('/concursos/close/{id}', [AdminController::class, 'closeGame'])->name('close-game');



  Route::get('/usuarios', [AdminController::class, 'index'])->name('list-user');
  Route::get('/usuarios/create_user', [AdminController::class, 'create_user_form'])->name('create-user-form');
  Route::post('/usuarios', [AdminController::class, 'createUser'])->name('create-user');


  Route::delete('/usuarios/delete/{id}', [AdminController::class, 'delete'])->name('users.delete');

  //edit
  Route::get('/usuarios/edit/{id}', [AdminController::class, 'editUserForm'])->name('edit-user-form');
  Route::get('/usuarios/me', [AdminController::class, 'editMeForm'])->name('edit-user-me-form');
  Route::put('/usuarios/{id}', [AdminController::class, 'update'])->name('user-update');
  Route::get('/usuarios/user_limit_credit_restart/{id}', [AdminController::class, 'user_limit_credit_restart'])->name('user_limit_credit_restart');


  Route::get('/concursos/edit/{id}', [GameController::class, 'editGameForm'])->name('edit-game-form');
  Route::put('/concursos/{id}', [GameController::class, 'update'])->name('game-update');
  Route::get('/concursos/generate_pdf/{id}', [GameController::class, 'generatePdf'])->name('game-pdf');



  Route::get('/concursos', [GameController::class, 'index'])->name('games');
  Route::get('/concursos/{id}', [GameController::class, 'show'])->name('show-game');
  Route::post('/concursos/add_game_history/{id}', [AdminController::class, 'addGameHistory'])->name('add-game-history');

  Route::get('/concursos/resultados/historico/edit/{game_history_id}', [AdminController::class, 'editGameHistory'])->name('edit-game-history-form');
  Route::put('/concursos/resultados/historico/{game_history_id}', [AdminController::class, 'updateGameHistory'])->name('edit-game-history');
  Route::get('/concursos/resultados/historico/remove/{id}', [AdminController::class, 'removeGameHistory'])->name('remove-game-history');

  Route::get('/concursos/premios/add/{game_id}', [GameAwardController::class, 'create'])->name('create-game-award-form');
  Route::put('/concursos/premios/add/{game_award_id}', [GameAwardController::class, 'store'])->name('store-game-award');
  Route::get('/concursos/premios/edit/{game_award_id}', [GameAwardController::class, 'edit'])->name('edit-game-award-form');
  Route::put('/concursos/premios/{game_award_id}', [GameAwardController::class, 'update'])->name('edit-game-award');
  Route::get('/concursos/premios/remove/{id}', [GameAwardController::class, 'destroy'])->name('remove-game-award');




  Route::post('/purchase/repeat', [PurchaseController::class, 'repeat'])->name('purchase-repeat');
  Route::post('/purchase/{id}', [PurchaseController::class, 'store'])->name('purchase-store');
  Route::get('/purchase/pay/{id}', [PurchaseController::class, 'pay'])->name('purchase-pay');
  Route::get('/purchase/withdraw/{id}', [PurchaseController::class, 'withdraw'])->name('purchase-withdraw');
  Route::get('/minhas_compras', [PurchaseController::class, 'index'])->name('minhas_compras');
  Route::get('/minhas_compras/{id}', [PurchaseController::class, 'show'])->name('minhas_compras-view');
  Route::get('/minhas_compras/delete/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');


  Route::get('/meus_premios', [UserAwardController::class, 'index'])->name('meus_premios');
  Route::get('/meus_premios/pay/{id}', [UserAwardController::class, 'pay'])->name('user_award-pay');
  Route::get('/meus_premios/withdraw/{id}', [UserAwardController::class, 'withdraw'])->name('user_award-withdraw');

  Route::get('/deposito', [DepositController::class, 'index'])->name('deposito');
  Route::post('/deposito/criar_pix', [DepositController::class, 'create_pix'])->name('deposit-create-pix');
  Route::post('/deposito/cartao_credito', [DepositController::class, 'pay_credit_card'])->name('deposit-create-credit-card');


  Route::get('/saque', [WithdrawalController::class, 'index'])->name('saque');
  Route::post('/saque', [WithdrawalController::class, 'withdraw_pay'])->name('pay-withdraw');

  Route::get('/extrato', [TransactionsController::class, 'index'])->name('extract');

  Route::get('/indique_ganhe/estornar/{id}', [ReferEarnController::class, 'payback'])->name('refer_earns_payback');
  Route::get('/indique_ganhe/pagar/{id}', [ReferEarnController::class, 'pay'])->name('refer_earns_pay');
  Route::get('/indique_ganhe', [ReferEarnController::class, 'index'])->name('refer_earn-view');
});

Route::get('/indique_ganhe/register', [ReferEarnController::class, 'create'])->name('refer_earn-register');

Route::post('/deposit/webhook', [DepositController::class, 'webhook'])->name('deposit-webhook');


// Main Page Route

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
