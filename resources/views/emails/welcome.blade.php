<x-mail::message>
# Selamat Datang, {{ $name }}!

Akun Anda di **Sistem Informasi Puskesmas & Klinik** telah berhasil diverifikasi dan kini telah aktif.

Detail Akun Anda:
- **Nama:** {{ $name }}
- **Peran Sistem:** {{ ucfirst($role) }}

Silakan masuk ke aplikasi untuk mulai memanfaatkan layanan kami:

<x-mail::button :url="$loginUrl">
Masuk Aplikasi
</x-mail::button>

Semoga layanan kami dapat membantu Anda mendapatkan fasilitas kesehatan yang maksimal.

Salam Sehat,<br>
**Puskesmas & Klinik**
</x-mail::message>
