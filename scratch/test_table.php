<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Filament\Resources\TutupHaris\Tables\TutupHariTable;
use Filament\Tables\Table;

// Mock the Table object if needed, but we just want to see if the class loads and executes
try {
    // We don't even need to call it, just loading the file might trigger something if there's a weird dependency
    echo "Attempting to load TutupHariTable...\n";
    
    // In Filament 5, Table might need more context, but let's try a simple instantiation if possible
    // Actually, configure() is static.
    echo "Class exists: " . (class_exists(TutupHariTable::class) ? 'Yes' : 'No') . "\n";
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}
