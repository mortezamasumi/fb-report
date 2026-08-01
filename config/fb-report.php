<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF protection password
    |--------------------------------------------------------------------------
    |
    | Password used to protect the generated PDF against copying and printing.
    | Set FB_REPORT_PDF_PASSWORD in your app's .env to override the default.
    |
    */

    'pdf_password' => env('FB_REPORT_PDF_PASSWORD', 'SG@%$ashgf236dShsd&*7253'),

    /*
    |--------------------------------------------------------------------------
    | mPDF defaults
    |--------------------------------------------------------------------------
    |
    | Default page format and orientation for generated PDFs. Individual
    | reporters may override these via their getConfig() return value.
    |
    */

    'pdf_format' => env('FB_REPORT_PDF_FORMAT', 'A4'),

    'pdf_orientation' => env('FB_REPORT_PDF_ORIENTATION', 'P'),

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Directory containing the font files referenced below. Leave null to use
    | the fonts bundled with this package (resources/fonts).
    |
    */

    'font_dir' => null,

    /*
    |--------------------------------------------------------------------------
    | Custom font data
    |--------------------------------------------------------------------------
    |
    | mPDF font definitions. Keys map to the 'default_font' option and are
    | usable from CSS font-family rules inside report views.
    |
    */

    'fonts' => [
        'gandom' => [
            'R' => 'Gandom.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'homa' => [
            'R' => 'Homa.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'iran' => [
            'R' => 'Iran.ttf',
            'B' => 'Iran-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'keyhan' => [
            'R' => 'Keyhan.ttf',
            'B' => 'Keyhan-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'keyhannavaar' => [
            'R' => 'Keyhan-Navaar.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'keyhanpook' => [
            'R' => 'Keyhan-Pook.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'keyhansayeh' => [
            'R' => 'Keyhan-Sayeh.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'koodak' => [
            'R' => 'Koodak.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'lalezar' => [
            'R' => 'Lalezar.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'nastaliq' => [
            'R' => 'Nastaliq.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'nazli' => [
            'R' => 'Nazli.ttf',
            'B' => 'Nazli-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'parastoo' => [
            'R' => 'Parastoo.ttf',
            'B' => 'Parastoo-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'sahel' => [
            'R' => 'Sahel.ttf',
            'B' => 'Sahel-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'shabnam' => [
            'R' => 'Shabnam.ttf',
            'B' => 'Shabnam-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'shafigh' => [
            'R' => 'Shafigh.ttf',
            'B' => 'Shafigh-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'vahid' => [
            'R' => 'Vahid.ttf',
            'B' => 'Vahid-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'vazir' => [
            'R' => 'Vazirmatn.ttf',
            'B' => 'Vazirmatn-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'yaghut' => [
            'R' => 'Yaghut.ttf',
            'B' => 'Yaghut-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'yas' => [
            'R' => 'Yas.ttf',
            'B' => 'Yas-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'yermook' => [
            'R' => 'Yermook.ttf',
            'B' => 'Yermook-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'zar' => [
            'R' => 'Zar.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'ziba' => [
            'R' => 'Ziba.ttf',
            'B' => 'Ziba-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
        'titr' => [
            'R' => 'Titr.ttf',
            'B' => 'Titr-Bold.ttf',
            'useKashida' => 75,
            'useOTL' => 0xFF,
        ],
    ],
];
