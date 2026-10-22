import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',   // admin shell (shared)
                'resources/js/public.js', // customer portal (animations)
            ],
            refresh: true,
        }),
    ],
});
