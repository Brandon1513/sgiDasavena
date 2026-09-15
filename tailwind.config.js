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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Century Gothic"', '"Trebuchet MS"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dasavena: {
                    purple: {
                        DEFAULT: '#6A2C75',
                        light: '#8E3D9E',
                        dark: '#4a1f55',
                    },
                    gold: {
                        DEFAULT: '#D6A644',
                        dark: '#b38600',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
