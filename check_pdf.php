<?php
require 'vendor/autoload.php';

$classes = [
    'Barryvdh\DomPDF\Facade\Pdf',
    'Barryvdh\DomPDF\Facade',
    'Barryvdh\DomPDF\PDF',
    'App\Services\JurnalKeuanganService',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "FOUND: $class\n";
    } else {
        echo "NOT FOUND: $class\n";
    }
}
