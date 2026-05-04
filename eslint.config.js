// eslint.config.js  (flat config — ESLint v9+)
export default [
    {
        files: ['resources/js/**/*.js', 'tests/front/**/*.js'],
        languageOptions: {
            ecmaVersion: 2024,
            sourceType: 'module',
            globals: {
                window:    'readonly',
                document:  'readonly',
                console:   'readonly',
                fetch:     'readonly',
            },
        },
        rules: {
            // Errores
            'no-unused-vars':           ['error', { argsIgnorePattern: '^_' }],
            'no-undef':                 'error',
            'no-console':               ['warn', { allow: ['warn', 'error'] }],

            // Estilo
            'semi':                     ['error', 'always'],
            'quotes':                   ['error', 'single'],
            'indent':                   ['error', 4],
            'comma-dangle':             ['error', 'always-multiline'],
            'eol-last':                 ['error', 'always'],
            'no-trailing-spaces':       'error',
            'object-curly-spacing':     ['error', 'always'],
            'arrow-parens':             ['error', 'always'],

            // Buenas prácticas
            'eqeqeq':                   ['error', 'always'],
            'prefer-const':             'error',
            'prefer-arrow-callback':    'error',
            'no-var':                   'error',
        },
    },
    {
        // Ignorar generados
        ignores: ['public/assets/**', 'vendor/**', 'node_modules/**'],
    },
];
