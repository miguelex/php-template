<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| Pest se ejecuta sobre PHPUnit. Este fichero permite configurar
| helpers globales, datasets compartidos y expectativas custom.
|
*/

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/
expect()->extend('toBeValidEmail', function () {
    return $this->toMatch('/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
});

/*
|--------------------------------------------------------------------------
| Helpers globales
|--------------------------------------------------------------------------
*/
function fixture(string $name): string
{
    return file_get_contents(__DIR__ . '/tests/fixtures/' . $name);
}
