<?php
require __DIR__ . '/../vendor/autoload.php';

// Mocking some Laravel/Filament stuff if needed, or just try to autoload the class
try {
    $class = 'App\Filament\Resources\TutupHaris\Tables\TutupHariTable';
    if (class_exists($class)) {
        echo "Class $class exists\n";
        // Try to trigger the configure method
        // We need a Table object.
        // But let's just see if we can instantiate or check the file.
        $reflection = new ReflectionClass($class);
        echo "File: " . $reflection->getFileName() . "\n";
    } else {
        echo "Class $class NOT found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
