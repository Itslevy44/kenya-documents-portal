<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. Many of these can be overridden when you create
    | a new instance of DomPDF.
    |
    */

    'show_warnings' => false,   // Throw an Exception on warnings from dompdf

    'public_path' => (function () {
        // First check standard public_path()
        if (function_exists('public_path')) {
            try {
                $p = public_path();
                if ($p && is_dir($p)) {
                    return $p;
                }
            } catch (\Throwable $e) {}
        }
        // Check base_path('public')
        if (function_exists('base_path')) {
            $bp = base_path('public');
            if (is_dir($bp)) {
                return $bp;
            }
        }
        // Check parent directory (InfinityFree htdocs)
        $parent = dirname(base_path());
        if (is_dir($parent)) {
            return $parent;
        }
        return base_path();
    })(),

    /*
     * Dejavu Sans font is embedded in dompdf for packet size reasons and doesn't
     * support all character sets. For full unicode support consider using a
     * different font.
     */
    'convert_entities' => true,

    'options' => [
        'font_dir' => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => (function () {
            return dirname(base_path());
        })(),
        'allowed_protocols' => [
            'file://' => ['rules' => []],
            'http://' => ['rules' => []],
            'https://' => ['rules' => []],
        ],
        'artifact_dir' => null,
        'log_output_file' => null,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'serif',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => true,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
