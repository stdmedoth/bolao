<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDezenas implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // 1. Remove espaços extras no início/fim e divide a string por um ou mais espaços em branco.
    // O `preg_split` com `\s+` é mais robusto que `explode` porque lida com espaços duplos.
    $numeros = preg_split('/\s+/', trim($value));

    // 2. Filtra qualquer elemento vazio que possa ter sobrado (caso de espaços no final)
    $numeros = array_filter($numeros);

    // 3. Verifica se temos exatamente 11 dezenas
    if (count($numeros) !== 11) {
      $fail('Os numeros devem conter exatamente 11 dezenas.');
      return; // Para a execução aqui se a contagem estiver errada.
    }

    if (count($numeros) !== count(array_unique($numeros))) {
      $fail('Os numeros não podem conter dezenas repetidas.');
      return;
    }

    // 4. (Opcional, mas recomendado) Verifica se cada dezena é válida
    foreach ($numeros as $numero) {
      // Verifica se é numérico e se tem 1 ou 2 dígitos.
      // A regex `^\d{1,2}$` garante que seja um número com 1 ou 2 dígitos.
      if (!is_numeric($numero) || !preg_match('/^\d{1,2}$/', $numero)) {
        $fail("O valor '{$numero}' não é uma dezena válida (deve ser um número com 1 ou 2 dígitos).");
        return;
      }
    }
  }
}
