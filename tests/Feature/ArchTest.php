<?php

declare(strict_types=1);

/**
 * Tests de arquitectura — reglas estructurales que se verifican solas.
 * Usan pestphp/pest-plugin-arch.
 */

arch('todo App usa strict_types')
    ->expect('App')
    ->toUseStrictTypes();

arch('el core no depende de capas superiores')
    ->expect('App\Core')
    ->not->toUse(['App\Controllers', 'App\Models', 'App\Repositories']);

arch('los modelos no dependen de los controllers')
    ->expect('App\Models')
    ->not->toUse('App\Controllers');

arch('las excepciones son lanzables')
    ->expect('App\Exceptions')
    ->toImplement(Throwable::class);

arch('no quedan helpers de debug')
    ->expect(['dd', 'ray', 'var_export'])
    ->not->toBeUsed();
