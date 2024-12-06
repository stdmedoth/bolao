@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        © <script>document.write(new Date().getFullYear())</script>, {{config('variables.creatorName')}}</>
      </div>
      <div class="d-none d-lg-inline-block">
        <!-- <a href="{{ config('variables.licenseUrl') ? config('variables.licenseUrl') : '#' }}" class="footer-link me-4" target="_blank">License</a> -->
        <!-- <a href="{{ config('variables.moreThemes') ? config('variables.moreThemes') : '#' }}" target="_blank" class="footer-link me-4">More Themes</a> -->
        <!-- <a href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}" target="_blank" class="footer-link me-4">Documentation</a> -->
        <!-- <a href="{{ config('variables.support') ? config('variables.supportNumber') : '#' }}" target="_blank" class="footer-link d-none d-sm-inline-block">Contato</a> -->
        <p class="footer-link me-4">19 997655946</p>
        <p class="footer-link me-4">jovictor210@gmail.com</p>
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
