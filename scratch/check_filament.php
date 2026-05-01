<?php
require __DIR__ . '/../vendor/autoload.php';

try {
    if (class_exists('Filament\Tables\Actions\EditAction')) {
        echo "Filament\Tables\Actions\EditAction exists\n";
    } else {
        echo "Filament\Tables\Actions\EditAction DOES NOT exist\n";
    }

    if (class_exists('Filament\Actions\EditAction')) {
        echo "Filament\Actions\EditAction exists\n";
    } else {
        echo "Filament\Actions\EditAction DOES NOT exist\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
