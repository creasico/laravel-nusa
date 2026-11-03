<?php

declare(strict_types=1);

return [
    'address' => [
        'label' => 'Address',
    ],

    'address_line' => [
        'label' => 'Address Line',
        'placeholder' => 'Address Line/Street/Block and Number',
    ],

    'province_code' => [
        'label' => 'Province',
        'placeholder' => 'Choose Province',
    ],

    'regency_code' => [
        'label' => 'Regency',
        'placeholder' => 'Choose Regency',
        'hint' => 'Choose Province first',
    ],

    'district_code' => [
        'label' => 'District',
        'placeholder' => 'Choose District',
        'hint' => 'Choose Regency first',
    ],

    'village_code' => [
        'label' => 'Village',
        'placeholder' => 'Choose Village',
        'hint' => 'Choose District first',
    ],

    'postal_code' => [
        'label' => 'Postal Code',
        'placeholder' => '5 Digit Postal Code',
        'invalid' => 'Postal code not found.',
    ],
];
