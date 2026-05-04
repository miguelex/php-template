// playwright.config.js
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir:        './tests/front/e2e',
    fullyParallel:  true,
    forbidOnly:     !!process.env.CI,
    retries:        process.env.CI ? 2 : 0,
    workers:        process.env.CI ? 1 : undefined,

    reporter: [
        ['html', { outputFolder: 'storage/playwright-report' }],
        ['list'],
    ],

    use: {
        baseURL:        'http://localhost:8000',
        trace:          'on-first-retry',
        screenshot:     'only-on-failure',
        video:          'on-first-retry',
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
        },
        // Descomenta para mobile:
        // {
        //     name: 'Mobile Chrome',
        //     use: { ...devices['Pixel 5'] },
        // },
    ],

    // Arrancar servidor PHP antes de los tests E2E
    webServer: {
        command:        'php -S localhost:8000 -t public',
        url:            'http://localhost:8000',
        reuseExistingServer: !process.env.CI,
        timeout:        5000,
    },
});
