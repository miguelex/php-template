// vitest.config.js
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment:    'jsdom',
        globals:        true,
        setupFiles:     ['./tests/front/setup.js'],
        include:        ['tests/front/unit/**/*.{test,spec}.js'],
        coverage: {
            provider:   'v8',
            reporter:   ['text', 'html', 'lcov'],
            reportsDirectory: 'storage/coverage/front',
            include:    ['resources/js/**'],
            exclude:    ['node_modules', 'public'],
        },
    },
});
