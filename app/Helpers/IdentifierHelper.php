<?php

if (!function_exists('generate_identifier')) {
  function generate_identifier(): string
  {
    $numbers = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $letter = chr(random_int(65, 90)); // Letra maiúscula A-Z
    return $numbers . $letter;
  }
}
