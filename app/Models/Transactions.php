<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transactions extends Model
{
  use HasFactory;

  protected $fillable = [
    "type",
    'external_id',
    "amount",
    "description",

    "user_id",
    "game_id",
    "purchase_id",
    "user_award_id",
  ];  


  protected $with = [
    "user"
  ];


  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function game(): BelongsTo
  {
    return $this->belongsTo(Game::class);
  }

  public function purchase(): BelongsTo
  {
    return $this->belongsTo(Purchase::class);
  }

  /**
   * Gera descrição automática baseada no tipo de transação
   */
  public static function generateDescription($type, $amount, $data = [])
  {
    $user = $data['user'] ?? null;
    $game = $data['game'] ?? null;
    $purchase = $data['purchase'] ?? null;
    $paidByUser = $data['paid_by_user'] ?? null;
    $seller = $data['seller'] ?? null;
    $referUser = $data['refer_user'] ?? null;
    $invitedUser = $data['invited_user'] ?? null;

    $amountFormatted = 'R$ ' . number_format($amount, 2, ',', '.');

    switch ($type) {
      case 'PAY_PURCHASE':
        if ($purchase && $purchase->game && $paidByUser) {
          return "Aposta paga pelo usuário {$paidByUser->name} no jogo {$purchase->game->name}";
        } elseif ($purchase && $purchase->game) {
          return "Aposta paga no jogo {$purchase->game->name}";
        }
        return "Pagamento de aposta - {$amountFormatted}";

      case 'PAY_PURCHASE_WITHDRAWAL':
        if ($purchase && $purchase->game && $user) {
          return "Estorno de aposta paga pelo usuário {$user->name} no jogo {$purchase->game->name}";
        } elseif ($purchase && $purchase->game) {
          return "Estorno de aposta no jogo {$purchase->game->name}";
        }
        return "Estorno de pagamento de aposta - {$amountFormatted}";

      case 'PAY_PURCHASE_COMISSION':
        if ($purchase && $purchase->game && $seller) {
          return "Comissão de venda recebida por {$seller->name} no jogo {$purchase->game->name}";
        } elseif ($purchase && $purchase->game && $user) {
          return "Comissão de venda recebida por {$user->name} no jogo {$purchase->game->name}";
        } elseif ($user) {
          return "Comissão de venda recebida por {$user->name} - {$amountFormatted}";
        }
        return "Comissão de venda - {$amountFormatted}";

      case 'PAY_PURCHASE_COMISSION_WITHDRAWAL':
        if ($purchase && $purchase->game && $user) {
          return "Estorno de comissão de venda do usuário {$user->name} no jogo {$purchase->game->name}";
        } elseif ($user) {
          return "Estorno de comissão de venda do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de comissão de venda - {$amountFormatted}";

      case 'DEPOSIT':
        if ($user) {
          return "Depósito realizado por {$user->name} - {$amountFormatted}";
        }
        return "Depósito - {$amountFormatted}";

      case 'WITHDRAWAL':
        if ($user) {
          return "Saque realizado por {$user->name} - {$amountFormatted}";
        }
        return "Saque - {$amountFormatted}";

      case 'DEPOSIT_REVERSAL':
        if ($user) {
          return "Estorno de depósito do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de depósito - {$amountFormatted}";

      case 'WITHDRAWAL_REVERSAL':
        if ($user) {
          return "Estorno de saque do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de saque - {$amountFormatted}";

      case 'REFER_EARN':
        if ($referUser && $invitedUser) {
          return "Bônus de indicação recebido por {$referUser->name} pela indicação de {$invitedUser->name} - {$amountFormatted}";
        } elseif ($user) {
          return "Bônus de indicação recebido por {$user->name} - {$amountFormatted}";
        }
        return "Bônus de indicação - {$amountFormatted}";

      case 'REFER_EARN_REVERSAL':
        if ($referUser && $invitedUser) {
          return "Estorno de bônus de indicação do usuário {$referUser->name} pela indicação de {$invitedUser->name} - {$amountFormatted}";
        } elseif ($user) {
          return "Estorno de bônus de indicação do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de bônus de indicação - {$amountFormatted}";

      case 'PAY_AWARD':
        if ($game && $user) {
          return "Prêmio pago para {$user->name} no jogo {$game->name} - {$amountFormatted}";
        } elseif ($game) {
          return "Prêmio pago no jogo {$game->name} - {$amountFormatted}";
        } elseif ($user) {
          return "Prêmio pago para {$user->name} - {$amountFormatted}";
        }
        return "Pagamento de prêmio - {$amountFormatted}";

      case 'PAY_AWARD_WITHDRAWAL':
        if ($game && $user) {
          return "Estorno de prêmio do usuário {$user->name} no jogo {$game->name} - {$amountFormatted}";
        } elseif ($game) {
          return "Estorno de prêmio no jogo {$game->name} - {$amountFormatted}";
        } elseif ($user) {
          return "Estorno de prêmio do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de pagamento de prêmio - {$amountFormatted}";

      case 'GAME_CREDIT':
        if ($user) {
          return "Crédito para jogar adicionado para {$user->name} - {$amountFormatted}";
        }
        return "Crédito para jogar - {$amountFormatted}";

      case 'GAME_CREDIT_REVERSAL':
        if ($user) {
          return "Estorno de crédito para jogar do usuário {$user->name} - {$amountFormatted}";
        }
        return "Estorno de crédito para jogar - {$amountFormatted}";

      case 'CUSTOM_INCOME':
      case 'CUSTOM_OUTCOME':
        // Para transações customizadas, a descrição já vem do request
        return $data['description'] ?? "Transação personalizada - {$amountFormatted}";

      default:
        return "Transação - {$amountFormatted}";
    }
  }
}
