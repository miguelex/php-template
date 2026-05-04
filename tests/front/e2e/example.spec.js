// tests/front/e2e/example.spec.js
import { test, expect } from '@playwright/test';

/**
 * Ejemplo de test E2E con Playwright.
 * Renombrar / eliminar en proyectos reales.
 */
test.describe('Homepage', () => {
    test('loads successfully', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveTitle(/.+/);
    });

    test('returns 200 status', async ({ request }) => {
        const response = await request.get('/');
        expect(response.status()).toBe(200);
    });
});
