<?php

$file = __DIR__ . '/../../../../../composer/autoload_real.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;