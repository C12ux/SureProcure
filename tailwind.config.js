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
            colors: {
                // Brand palette (from the supplied swatches)
                ink: '#070809',                       // Neutral
                mist: {                               // Primary, plus lighter tints for surfaces
                    50: '#F6F7FA',
                    100: '#ECEEF4',
                    200: '#DDDFEA',
                    DEFAULT: '#C4C7D9',
                },
                jackson: {                            // Jackson's Purple
                    DEFAULT: '#282391',
                    deep: '#1B1769',
                    soft: '#4D47C9',
                },
                // Status colors: use only to mean something
                success: '#9EE028',
                caution: '#D9AF0F',
                danger: {
                    DEFAULT: '#F60020',
                    deep: '#B8001B',                  // darker shade so small error text stays readable
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
