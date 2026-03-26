<?php
require 'vendor/autoload.php';

$classes = [
    'EightCedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin',
    'Eightcedars\FilamentInactivityGuard\FilamentInactivityGuardPlugin',
    'EightCedars\FilamentInactivityGuard\InactivityGuardPlugin',
    'EightCedars\FilamentInactivityGuard\InactivityGuard',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "FOUND: $class\n";
    } else {
        echo "NOT FOUND: $class\n";
    }
}
