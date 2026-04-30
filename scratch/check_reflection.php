<?php
require 'vendor/autoload.php';
$r = new ReflectionMethod('Illuminate\Database\Eloquent\Model', 'decrement');
echo "Decrement parameters: " . $r->getNumberOfParameters() . "\n";
foreach($r->getParameters() as $p) {
    echo "- " . $p->getName() . ($p->isOptional() ? " (optional)" : "") . "\n";
}

$r2 = new ReflectionMethod('Illuminate\Database\Eloquent\Builder', 'where');
echo "Where parameters: " . $r2->getNumberOfParameters() . "\n";
foreach($r2->getParameters() as $p) {
    echo "- " . $p->getName() . ($p->isOptional() ? " (optional)" : "") . "\n";
}
