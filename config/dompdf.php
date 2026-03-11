<?php

return [
    'show_warnings' => false, // Recommended for production
    'public_path' => public_path(),
    'options' => [
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'debugPng' => false,
        'debugKeepTemp' => false,
        'debugCss' => false,
        'debugLayout' => false,
        'debugLayoutLines' => false,
        'debugLayoutBlocks' => false,
        'debugLayoutInline' => false,
        'debugLayoutPaddingBox' => false,
        'defaultMediaType' => 'screen',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'sans-serif',
        'dpi' => 96,
        'fontHeightRatio' => 1.1
    ]
];
