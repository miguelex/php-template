// tests/front/setup.js
// Setup global para Vitest

// Polyfills si son necesarios
// import '@testing-library/jest-dom';

// Limpieza entre tests
afterEach(() => {
    document.body.innerHTML = '';
});
