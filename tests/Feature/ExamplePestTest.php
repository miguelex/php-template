<?php

declare(strict_types=1);

/**
 * Ejemplo de test con Pest.
 * Renombrar / eliminar en proyectos reales.
 */
it('passes a basic assertion', function (): void {
    expect(true)->toBeTrue();
});

it('can do arithmetic', function (): void {
    expect(2 + 2)->toBe(4);
});

dataset('numbers', [1, 2, 3, 4, 5]);

it('works with datasets', function (int $number): void {
    expect($number)->toBeGreaterThan(0);
})->with('numbers');
