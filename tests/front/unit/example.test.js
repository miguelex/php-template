// tests/front/unit/example.test.js
import { describe, it, expect } from 'vitest';

/**
 * Ejemplo de test unitario JS con Vitest.
 * Renombrar / eliminar en proyectos reales.
 */
describe('Example', () => {
    it('passes a basic assertion', () => {
        expect(true).toBe(true);
    });

    it('can do arithmetic', () => {
        expect(2 + 2).toBe(4);
    });

    it('can manipulate strings', () => {
        const greeting = (name) => `Hello, ${name}!`;
        expect(greeting('World')).toBe('Hello, World!');
    });
});
