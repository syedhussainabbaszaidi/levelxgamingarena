import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Orbitron', 'Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Red neon primary.
                neon: {
                    50: '#fff1f3',
                    100: '#ffe0e5',
                    200: '#ffc6cf',
                    300: '#ff9caa',
                    400: '#ff6178',
                    500: '#ff2d55',
                    600: '#ed1046',
                    700: '#c80a3a',
                    800: '#a70c36',
                    900: '#8f0f34',
                },
                // Deep dark surfaces.
                ink: {
                    950: '#050507',
                    900: '#08080b',
                    800: '#0d0d12',
                    700: '#121219',
                    600: '#17171f',
                    500: '#22222c',
                    400: '#2f2f3b',
                },
            },
            boxShadow: {
                neon: '0 0 24px -4px rgba(255, 45, 85, 0.45)',
                'neon-sm': '0 0 12px -2px rgba(255, 45, 85, 0.35)',
                panel: '0 18px 40px -24px rgba(0, 0, 0, 0.9)',
            },
            keyframes: {
                'pulse-glow': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.6' },
                },
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'pulse-glow': 'pulse-glow 2.4s ease-in-out infinite',
                'fade-up': 'fade-up 0.35s ease-out both',
            },
        },
    },

    plugins: [forms],
};
