<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Versi Aplikasi
    |--------------------------------------------------------------------------
    |
    | Mengikuti Semantic Versioning (MAJOR.MINOR.PATCH):
    |   MAJOR — perubahan besar yang mengubah alur/struktur data.
    |   MINOR — penambahan fitur yang tetap kompatibel.
    |   PATCH — perbaikan bug / penyesuaian UI.
    |
    | Saat menaikkan versi: ubah nilai di sini, commit, lalu buat tag git yang
    | sama (mis. `git tag -a v1.0.0 -m "..."` dan `git push origin v1.0.0`).
    |
    */

    'app' => env('APP_VERSION', '1.0.0'),

];
