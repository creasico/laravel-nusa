<?php

declare(strict_types=1);

return [
    'address' => [
        'label' => 'Alamat',
    ],

    'address_line' => [
        'label' => 'Alamat Lengkap',
        'placeholder' => 'Alamat Jalan/Gang/Blok dan Nomor Rumah',
    ],

    'province_code' => [
        'label' => 'Provinsi',
        'placeholder' => 'Pilih Provinsi',
    ],

    'regency_code' => [
        'label' => 'Kota/Kabupaten',
        'placeholder' => 'Pilih Kota/Kabupaten',
        'hint' => 'Pilih Provinsi terlebih dahulu',
    ],

    'district_code' => [
        'label' => 'Kecamatan',
        'placeholder' => 'Pilih Kecamatan',
        'hint' => 'Pilih Kota/Kabupaten terlebih dahulu',
    ],

    'village_code' => [
        'label' => 'Desa/Kelurahan',
        'placeholder' => 'Pilih Desa/Kelurahan',
        'hint' => 'Pilih Kecamatan terlebih dahulu',
    ],

    'postal_code' => [
        'label' => 'Kode Pos',
        'placeholder' => '5 Digit Kode Pos',
        'invalid' => 'Kode pos tidak ditemukan.',
        'failed' => 'Gagal memuat kode pos.',
    ],
];
