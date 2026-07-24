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
                sans: ['Manrope', 'Inter', 'Segoe UI', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', 'Inter', 'Segoe UI', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                slate: {
                    50: '#f7f8f6',
                    100: '#edf0ec',
                    200: '#dbe2dc',
                    300: '#becbc3',
                    400: '#8fa39a',
                    500: '#647b73',
                    600: '#49615a',
                    700: '#354a45',
                    800: '#263a37',
                    900: '#1a2e2c',
                    950: '#0d201f',
                },
                sky: {
                    50: '#eff8f5',
                    100: '#dcefe9',
                    200: '#b9dfd6',
                    300: '#88c8bc',
                    400: '#55a99d',
                    500: '#358b81',
                    600: '#286f68',
                    700: '#225a55',
                    800: '#1d4945',
                    900: '#193d3a',
                    950: '#0d2422',
                },
                cyan: {
                    50: '#f2faf8',
                    100: '#ddf3ee',
                    200: '#bce6dc',
                    300: '#8fd3c5',
                    400: '#60b9ab',
                    500: '#429d91',
                    600: '#347e76',
                    700: '#2c665f',
                    800: '#27514d',
                    900: '#234541',
                    950: '#102725',
                },
                indigo: {
                    50: '#f4f4f8',
                    100: '#e8e8f0',
                    200: '#d4d3e2',
                    300: '#b4b2cc',
                    400: '#918dab',
                    500: '#746f92',
                    600: '#5d5878',
                    700: '#4c4862',
                    800: '#403d52',
                    900: '#383648',
                    950: '#211f2e',
                },
            },
            boxShadow: {
                'soft': '0 14px 40px -24px rgba(22, 61, 58, 0.35)',
                'panel': '0 18px 55px -34px rgba(13, 36, 34, 0.45)',
                'lift': '0 22px 60px -30px rgba(13, 36, 34, 0.50)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            backgroundImage: {
                'ledger-grid': 'linear-gradient(rgba(40,111,104,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(40,111,104,.045) 1px, transparent 1px)',
            },
        },
    },

    plugins: [forms],
};
