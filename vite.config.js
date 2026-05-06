import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/user/theme/css/user.css",
                "resources/user/theme/js/user.js",
                "resources/css/editor.css",
                "resources/js/room-editor.js",
            ],
            refresh: true,
        }),
    ],
});
