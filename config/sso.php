<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Clients
    |--------------------------------------------------------------------------
    |
    | Daftar aplikasi yang diizinkan menggunakan Central Login.
    |
    */

    'clients' => [

        'zhpicture' => [

            'name' => 'ZH Picture',

            'redirect_uris' => [
                'http://zhpicture.test:8080/sso/callback',
            ],

        ],

    ],

];