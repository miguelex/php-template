// vite.config.js
// Alternativa moderna a Gulp para proyectos PHP.
// Uso: npm run dev:vite  /  npm run build:vite
//
// Requiere que PHP esté corriendo en localhost:8000
// BrowserSync hace proxy → localhost:5173 con HMR

import { defineConfig } from 'vite';
import { resolve }      from 'path';
// Descomentar si se usa imagemin con Vite:
// import viteImagemin from 'vite-plugin-imagemin';

export default defineConfig(({ mode }) => ({
    // Punto de entrada: Vite compila lo que se importa aquí
    build: {
        outDir:   'public/assets',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'resources/js/app.js'),
                // css: resolve(__dirname, 'resources/scss/app.scss'),
            },
            output: {
                // Nombres de salida con hash para cache busting
                entryFileNames: 'js/[name].[hash].js',
                chunkFileNames: 'js/[name].[hash].js',
                assetFileNames: (assetInfo) => {
                    const ext = assetInfo.name?.split('.').pop() ?? '';
                    if (/css/i.test(ext))  return 'css/[name].[hash].[ext]';
                    if (/png|jpe?g|webp|avif|gif|svg/i.test(ext)) return 'img/[name].[hash].[ext]';
                    return 'assets/[name].[hash].[ext]';
                },
            },
        },
        sourcemap: mode === 'development',
        minify:    mode === 'production' ? 'terser' : false,
    },

    // CSS / SCSS
    css: {
        preprocessorOptions: {
            scss: {
                // Variables globales disponibles en todos los .scss sin importar
                additionalData: `@use "${resolve(__dirname, 'resources/scss/_variables.scss')}" as v;`,
            },
        },
        devSourcemap: true,
    },

    // Proxy hacia el servidor PHP durante desarrollo
    server: {
        proxy: {
            // Todo lo que no sea un asset de Vite → PHP
            '^(?!/resources|/@vite|/node_modules).*': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
        },
        // Puerto de Vite (acceder desde aquí en dev)
        port:     5173,
        open:     false,
        // HMR habilitado por defecto
    },

    // Alias de rutas
    resolve: {
        alias: {
            '@js':    resolve(__dirname, 'resources/js'),
            '@scss':  resolve(__dirname, 'resources/scss'),
            '@img':   resolve(__dirname, 'resources/img'),
        },
    },

    // plugins: [
    //     viteImagemin({
    //         mozjpeg: { quality: 80 },
    //         optipng: { optimizationLevel: 5 },
    //         webp:    { quality: 80 },
    //     }),
    // ],
}));
