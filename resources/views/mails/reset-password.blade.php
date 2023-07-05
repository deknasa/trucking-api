@component('mail::message')

<p class="text-center text-black">
  <strong class> Penting! Jangan bagikan email ini kepada siapapun.</strong>
</p>

<p class="text-black text-center">
  Silahkan klik tombol di bawah untuk reset password.
</p>

<div class="text-center">
  <a href="{{ $resetLink }}" class="btn btn-primary no-link"> Reset Password </a>
</div>

<br>

<p class="text-black text-center">
  Link kedaluwarsa dalam {{ config('app.jwt_exp') }} menit, atau sesaat setelah password diganti. Harap ganti password anda segera.
</p>
@endcomponent