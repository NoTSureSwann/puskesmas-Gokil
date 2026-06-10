<x-mail::message>
# Halo, {{ $name }}!

Terima kasih telah melakukan registrasi akun di **Sistem Informasi Puskesmas & Klinik**. 

Silakan lakukan verifikasi alamat email Anda dengan mengeklik tombol di bawah ini agar dapat menggunakan fasilitas antrian online dan resep elektronik kami:

<x-mail::button :url="$url">
Verifikasi Alamat Email
</x-mail::button>

Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan tautan berikut ke peramban web Anda:
[{{ $url }}]({{ $url }})

Jika Anda tidak merasa mendaftar di sistem kami, harap abaikan email ini.

Salam Sehat,<br>
**Puskesmas & Klinik**
</x-mail::message>
