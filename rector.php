<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * Configuración de Rector.
 *
 *   composer rector       → muestra los cambios propuestos (dry-run)
 *   composer rector:fix   → aplica los cambios
 *
 * El set de PHP apunta a 8.5. Al preparar la migración, ejecuta el dry-run
 * para ver qué moderniza (y qué deprecaciones detecta) antes de aplicarlo.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    );
