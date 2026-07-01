import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        container: {
            center: true,
            padding: { DEFAULT: '1rem', lg: '2rem' },
        },
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: 'rgb(var(--brand-50) / <alpha-value>)',
                    100: 'rgb(var(--brand-100) / <alpha-value>)',
                    200: 'rgb(var(--brand-200) / <alpha-value>)',
                    300: 'rgb(var(--brand-300) / <alpha-value>)',
                    400: 'rgb(var(--brand-400) / <alpha-value>)',
                    500: 'rgb(var(--brand-500) / <alpha-value>)',
                    600: 'rgb(var(--brand-600) / <alpha-value>)',
                    700: 'rgb(var(--brand-700) / <alpha-value>)',
                    800: 'rgb(var(--brand-800) / <alpha-value>)',
                    900: 'rgb(var(--brand-900) / <alpha-value>)',
                    950: 'rgb(var(--brand-950) / <alpha-value>)',
                },
                rsud: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    900: '#0c4a6e',
                },
                accent: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                },
            },
            boxShadow: {
                soft: '0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 4px 14px rgba(15, 23, 42, 0.06)',
                premium: '0 10px 30px -10px rgba(2, 132, 199, 0.25)',
            },
            backgroundImage: {
                'hero-grid': "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'><path d='M0 .5H40M.5 0V40' fill='none' stroke='rgba(255,255,255,0.06)' stroke-width='1'/></svg>\")",
                'soft-radial':
                    'radial-gradient(circle at top right, rgba(56,189,248,0.14), transparent 40%), radial-gradient(circle at bottom left, rgba(2,132,199,0.10), transparent 40%)',
            },
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pulse-slow': {
                    '0%,100%': { opacity: '1' },
                    '50%': { opacity: '0.6' },
                },
            },
            animation: {
                'fade-in-up': 'fade-in-up 600ms ease-out both',
                'pulse-slow': 'pulse-slow 3s ease-in-out infinite',
            },
        },
    },
    plugins: [forms, typography],
};
