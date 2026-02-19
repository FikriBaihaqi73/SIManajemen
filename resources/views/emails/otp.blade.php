<x-mail::message>
# Verifikasi Akun SIManajemen

Terima kasih telah mendaftar. Gunakan kode OTP di bawah ini untuk memverifikasi akun Anda:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Kode ini akan kadaluarsa dalam 10 menit. Jangan berikan kode ini kepada siapapun.

Terima Kasih,<br>
{{ config('app.name') }}
</x-mail::message>
