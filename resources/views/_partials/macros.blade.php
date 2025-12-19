@php
    $width = $width ?? '25'; // Largura padrão definida como 25.
@endphp

<img src="http://192.168.15.7:8000/assets/img/logos/logo.png" alt="Logo" width="{{ $width * 2.5 }}"
    style="height: auto;">
