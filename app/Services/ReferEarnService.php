<?php

namespace App\Services;

use App\Models\ReferEarn;
use App\Models\Transactions;
use App\Models\User;

class ReferEarnService
{
  /**
   * Paga automaticamente a comissão de indicação
   * Só paga se invited_user_bought = true e earn_paid = false
   * 
   * @param ReferEarn $referEarn
   * @return bool Retorna true se o pagamento foi processado, false caso contrário
   */
  public static function payAutomatically(ReferEarn $referEarn): bool
  {
    // Verifica se deve pagar: invited_user_bought deve ser true e earn_paid deve ser false
    if (!$referEarn->invited_user_bought || $referEarn->earn_paid) {
      return false;
    }

    // Verifica se o amount está definido
    if (!$referEarn->amount || $referEarn->amount <= 0) {
      return false;
    }

    // Busca o usuário que indicou
    $user = User::find($referEarn->refer_user_id);
    if (!$user) {
      return false;
    }

    // Adiciona crédito ao usuário
    $user->game_credit += $referEarn->amount;
    $user->save();

    // Marca como pago (sem acionar observers para evitar loop)
    $referEarn->earn_paid = true;
    ReferEarn::withoutEvents(function () use ($referEarn) {
      $referEarn->save();
    });

    // Carrega relacionamentos para a descrição
    $referEarn->load(['referUser', 'invitedUser']);
    
    // Cria transação
    $description = Transactions::generateDescription('REFER_EARN', $referEarn->amount, [
      'user' => $user,
      'refer_user' => $referEarn->referUser,
      'invited_user' => $referEarn->invitedUser,
    ]);
    
    Transactions::create([
      "type" => 'REFER_EARN',
      "amount" => $referEarn->amount,
      "user_id" => $user->id,
      "description" => $description,
    ]);

    return true;
  }

  /**
   * Estorna automaticamente a comissão de indicação
   * Só estorna se earn_paid = true
   * 
   * @param ReferEarn $referEarn
   * @return bool Retorna true se o estorno foi processado, false caso contrário
   */
  public static function refundAutomatically(ReferEarn $referEarn): bool
  {
    // Verifica se deve estornar: earn_paid deve ser true
    if (!$referEarn->earn_paid) {
      return false;
    }

    // Verifica se o amount está definido
    if (!$referEarn->amount || $referEarn->amount <= 0) {
      return false;
    }

    // Busca o usuário que indicou
    $user = User::find($referEarn->refer_user_id);
    if (!$user) {
      return false;
    }

    // Remove crédito do usuário
    $user->game_credit -= $referEarn->amount;
    $user->save();

    // Marca como não pago (sem acionar observers para evitar loop)
    $referEarn->earn_paid = false;
    ReferEarn::withoutEvents(function () use ($referEarn) {
      $referEarn->save();
    });

    // Carrega relacionamentos para a descrição
    $referEarn->load(['referUser', 'invitedUser']);
    
    // Cria transação de estorno
    $description = Transactions::generateDescription('REFER_EARN_REVERSAL', $referEarn->amount, [
      'user' => $user,
      'refer_user' => $referEarn->referUser,
      'invited_user' => $referEarn->invitedUser,
    ]);
    
    Transactions::create([
      "type" => 'REFER_EARN_REVERSAL',
      "amount" => $referEarn->amount,
      "user_id" => $user->id,
      "description" => $description,
    ]);

    return true;
  }
}
