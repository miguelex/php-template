<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ejemplo de test unitario con PHPUnit puro.
 * Renombrar / eliminar en proyectos reales.
 */
class ExampleTest extends TestCase
{
    public function test_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    public function test_basic_arithmetic(): void
    {
        $this->assertSame(4, 2 + 2);
    }
}
