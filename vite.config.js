import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',  
                'resources/css/auth.css', 
                'resources/css/mua-register.css',
                'resources/css/mua-review.css',
                'resources/css/mua-dashboard.css',
                'resources/css/mua-availability.css',
                'resources/css/mua-bookings.css',
                'resources/css/mua-services.css',
                'resources/css/mua-navbar.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
