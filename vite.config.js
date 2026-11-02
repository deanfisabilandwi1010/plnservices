import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/coming-soon.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Raleway', {
                    weights: [500, 600, 700, 800],
                }),
                bunny('Open Sans', {
                    weights: [400, 600, 700],
                }),
                // Coming Soon page: the closest match to the logo's lettering
                bunny('Archivo Black', {
                    weights: [400],
                }),
                bunny('Archivo', {
                    weights: [400, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
