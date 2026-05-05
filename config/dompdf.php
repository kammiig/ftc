<?php

return [
    'show_warnings' => false,
    'public_path' => null,
    'convert_entities' => true,
    'options' => [
        'font_dir' => storage_path('app/dompdf-fonts'),
        'font_cache' => storage_path('app/dompdf-fonts'),
        'temp_dir' => storage_path('app/dompdf-temp'),
        'chroot' => realpath(base_path()),
        'log_output_file' => storage_path('logs/dompdf.htm'),
        'default_font' => 'DejaVu Sans',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_media_type' => 'screen',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => false,
        'enable_html5_parser' => true,
        'enable_font_subsetting' => false,
        'font_height_ratio' => 1.1,
        'pdf_backend' => 'CPDF',
    ],
];
