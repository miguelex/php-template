<?php

declare(strict_types=1);

/**
 * Bootstrap para PHPStan.
 * Define constantes que se declaran en runtime (public/index.php)
 * para que el análisis estático no las marque como no encontradas.
 */
define('BASE_PATH', dirname(__DIR__));
