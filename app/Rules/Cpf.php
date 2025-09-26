<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
  /**
   * Valida um número de CPF.
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // 1. Limpa o CPF, deixando apenas os números
    $cpf = preg_replace('/[^0-9]/', '', (string) $value);

    // 2. Verifica se o CPF tem 11 dígitos
    if (strlen($cpf) != 11) {
      $fail('O campo de documento não é um CPF válido.');
      return;
    }

    // 3. Verifica se todos os dígitos são iguais (ex: 111.111.111-11), que é um caso inválido
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      $fail('O campo de documento não é um CPF válido.');
      return;
    }

    // 4. Calcula o primeiro dígito verificador
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
      $sum += (int)$cpf[$i] * (10 - $i);
    }
    $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

    // 5. Calcula o segundo dígito verificador
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
      $sum += (int)$cpf[$i] * (11 - $i);
    }
    $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

    // 6. Compara os dígitos calculados com os dígitos do CPF
    if ($digit1 != (int)$cpf[9] || $digit2 != (int)$cpf[10]) {
      $fail('O campo de documento não é um CPF válido.');
    }
  }
}
