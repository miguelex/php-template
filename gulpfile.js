/**
 * gulpfile.js — PHP Template
 * ─────────────────────────────────────────────
 * Tasks disponibles:
 *   gulp               → build + watch + BrowserSync
 *   gulp build         → compilar todo (dev)
 *   gulp watch         → watcher + BrowserSync
 *   gulp clean         → limpiar public/assets
 *
 * NODE_ENV=production gulp build  → minificado máximo + optimización completa
 *
 * Formatos de imagen generados:
 *   - Original optimizado (jpg/png/svg)
 *   - WebP  (quality 80)
 *   - AVIF  (quality 50 — mejor compresión, soporte moderno)
 */

import gulp          from 'gulp';
import * as dartSass from 'gulp-dart-sass';
import postcss       from 'gulp-postcss';
import autoprefixer  from 'autoprefixer';
import cssnano       from 'cssnano';
import sourcemaps    from 'gulp-sourcemaps';
import concat        from 'gulp-concat';
import terser        from 'gulp-terser';
import rename        from 'gulp-rename';
import imagemin,     { mozjpeg, optipng, svgo } from 'gulp-imagemin';
import cache         from 'gulp-cache';
import webp          from 'gulp-webp';
import avif          from 'gulp-avif';
import browserSync   from 'browser-sync';
import { deleteAsync } from 'del';

const bs     = browserSync.create();
const isProd = process.env.NODE_ENV === 'production';

// ── Rutas ────────────────────────────────────────────────────────────────────
const paths = {
    scss: {
        src:   'resources/scss/**/*.scss',
        entry: 'resources/scss/app.scss',
        dest:  'public/assets/css',
    },
    js: {
        src:  'resources/js/**/*.js',
        dest: 'public/assets/js',
    },
    img: {
        src:  'resources/img/**/*.{jpg,jpeg,png,gif,svg}',
        dest: 'public/assets/img',
    },
    php: {
        watch: './**/*.php',
    },
};

// ── Clean ────────────────────────────────────────────────────────────────────
export async function clean() {
    await deleteAsync([
        'public/assets/css',
        'public/assets/js',
        'public/assets/img',
    ]);
}

export async function clearCache() {
    return cache.clearAll();
}

// ── SCSS → CSS ───────────────────────────────────────────────────────────────
export function styles() {
    const postcssPlugins = [autoprefixer()];
    if (isProd) postcssPlugins.push(cssnano({ preset: 'default' }));

    return gulp.src(paths.scss.entry)
        .pipe(sourcemaps.init())
        .pipe(dartSass.default({ outputStyle: isProd ? 'compressed' : 'expanded' })
            .on('error', dartSass.default.logError))
        .pipe(postcss(postcssPlugins))
        .pipe(rename({ suffix: isProd ? '.min' : '' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.scss.dest))
        .pipe(bs.stream());
}

// ── JS → bundle + minify ─────────────────────────────────────────────────────
export function scripts() {
    return gulp.src(paths.js.src, { sourcemaps: true })
        .pipe(concat('app.js'))
        .pipe(isProd ? terser() : gulp.dest(paths.js.dest))
        .pipe(rename({ suffix: isProd ? '.min' : '' }))
        .pipe(gulp.dest(paths.js.dest, { sourcemaps: '.' }))
        .pipe(bs.stream());
}

// ── Images: optimizar (con caché — no reprocesa lo que no cambia) ────────────
export function images() {
    return gulp.src(paths.img.src)
        .pipe(cache(imagemin([
            mozjpeg({ quality: 80, progressive: true }),
            optipng({ optimizationLevel: 5 }),
            svgo({ plugins: [{ removeViewBox: false }] }),
        ], { verbose: true })))
        .pipe(gulp.dest(paths.img.dest));
}

// ── Images: generar WebP ─────────────────────────────────────────────────────
export function imagesToWebp() {
    return gulp.src('resources/img/**/*.{jpg,jpeg,png}')
        .pipe(cache(webp({ quality: 80 })))
        .pipe(gulp.dest(paths.img.dest));
}

// ── Images: generar AVIF (mejor compresión, navegadores modernos) ─────────────
export function imagesToAvif() {
    return gulp.src('resources/img/**/*.{jpg,jpeg,png}')
        .pipe(cache(avif({ quality: 50 })))
        .pipe(gulp.dest(paths.img.dest));
}

// ── BrowserSync ──────────────────────────────────────────────────────────────
export function serve(done) {
    bs.init({
        proxy:  'localhost:8000',
        notify: false,
        open:   false,
    });
    done();
}

// ── Reload helper ────────────────────────────────────────────────────────────
function reload(done) {
    bs.reload();
    done();
}

// ── Watch ────────────────────────────────────────────────────────────────────
export function watch() {
    gulp.watch(paths.scss.src,  styles);
    gulp.watch(paths.js.src,    scripts);
    gulp.watch(paths.img.src,   gulp.series(images, imagesToWebp, imagesToAvif));
    gulp.watch(paths.php.watch, reload);
}

// ── Tasks compuestas ─────────────────────────────────────────────────────────
export const imgAll = gulp.parallel(images, imagesToWebp, imagesToAvif);

export const build = gulp.series(
    clean,
    gulp.parallel(styles, scripts, imgAll),
);

export default gulp.series(build, serve, watch);
