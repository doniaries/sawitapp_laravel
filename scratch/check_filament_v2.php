<?php
require __DIR__ . '/../vendor/autoload.php';

echo "Testing namespaces...\n";

$classes = [
    'Filament\Actions\EditAction',
    'Filament\Tables\Actions\EditAction',
    'Filament\Tables\Actions\BulkActionGroup',
    'Filament\Actions\BulkActionGroup',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "$class: EXISTS\n";
    } else {
        echo "$class: DOES NOT EXIST\n";
    }
}
