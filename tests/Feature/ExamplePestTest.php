<?php

declare(strict_types=1);

/**
 * Ejemplo de test con Pest.
 * Renombrar / eliminar en proyectos reales.
 */
it('passes a basic assertion', function () {
    expect(true)->toBeTrue();
});

it('can do arithmetic', function () {
    expect(2 + 2)->toBe(4);
});

dataset('numbers', [1, 2, 3, 4, 5]);

it('works with datasets', function (int $number) {
    expect($number)->toBeGreaterThan(0);
})->with('numbers');
