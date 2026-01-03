<?php

namespace App\Observers;

use App\Models\ReferEarn;
use App\Services\ReferEarnService;

class ReferEarnObserver
{
  /**
   * Handle the ReferEarn "updated" event.
   * Detecta mudanças em invited_user_bought e earn_paid para processar pagamento/estorno automático
   */
  public function updated(ReferEarn $referEarn): void
  {
    // Verifica se invited_user_bought mudou de false para true
    if ($referEarn->isDirty('invited_user_bought')) {
      $oldValue = $referEarn->getOriginal('invited_user_bought');
      $newValue = $referEarn->invited_user_bought;
      
      // Se mudou de false para true e ainda não foi pago, tenta pagar automaticamente
      if (!$oldValue && $newValue && !$referEarn->earn_paid) {
        // Usa withoutEvents para evitar loop infinito quando payAutomatically salvar o modelo
        ReferEarn::withoutEvents(function () use ($referEarn) {
          ReferEarnService::payAutomatically($referEarn);
        });
        return; // Evita processar earn_paid se já processou invited_user_bought
      }
      
      // Se mudou de true para false e já estava pago, estorna
      if ($oldValue && !$newValue && $referEarn->getOriginal('earn_paid')) {
        // Usa withoutEvents para evitar loop infinito quando refundAutomatically salvar o modelo
        ReferEarn::withoutEvents(function () use ($referEarn) {
          ReferEarnService::refundAutomatically($referEarn);
        });
        return;
      }
    }

    // Verifica se earn_paid mudou de true para false (estorno manual ou outra situação)
    // Mas só processa se invited_user_bought não mudou (para evitar processar duas vezes)
    if ($referEarn->isDirty('earn_paid') && !$referEarn->isDirty('invited_user_bought')) {
      $oldValue = $referEarn->getOriginal('earn_paid');
      $newValue = $referEarn->earn_paid;
      
      // Se mudou de true para false, estorna
      if ($oldValue && !$newValue) {
        // Usa withoutEvents para evitar loop infinito quando refundAutomatically salvar o modelo
        ReferEarn::withoutEvents(function () use ($referEarn) {
          ReferEarnService::refundAutomatically($referEarn);
        });
      }
    }
  }
}
