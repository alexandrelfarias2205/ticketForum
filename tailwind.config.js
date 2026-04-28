import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display': ['2.5rem', { lineHeight: '3rem', letterSpacing: '-0.02em', fontWeight: '700' }],
                'h1': ['2rem', { lineHeight: '2.5rem', letterSpacing: '-0.02em', fontWeight: '700' }],
                'h2': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.01em', fontWeight: '600' }],
                'h3': ['1.25rem', { lineHeight: '1.75rem', fontWeight: '600' }],
                'h4': ['1.125rem', { lineHeight: '1.5rem', fontWeight: '600' }],
                'h5': ['1rem', { lineHeight: '1.5rem', fontWeight: '600' }],
                'h6': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '600' }],
            },
            colors: {
                // Primary brand — azul-violeta gradient anchors
                brand: {
                    50:  '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
                accent: {
                    50:  '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                // Semantic
                success: {
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                },
                warning: {
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                },
                danger: {
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                },
                info: {
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                },
                surface: {
                    900: '#0b0d14',
                    800: '#0f1117',
                    700: '#151823',
                    600: '#1c2030',
                    500: '#262b3d',
                    400: '#3a3f55',
                },
            },
            boxShadow: {
                'glow-brand': '0 8px 24px -8px rgba(99, 102, 241, 0.45), 0 0 0 1px rgba(99, 102, 241, 0.15)',
                'glow-accent': '0 8px 24px -8px rgba(139, 92, 246, 0.45)',
                'glow-danger': '0 8px 24px -8px rgba(239, 68, 68, 0.45)',
                'soft': '0 4px 16px -4px rgba(0, 0, 0, 0.35)',
                'glass': '0 8px 32px -8px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.06)',
            },
            borderRadius: {
                'xl2': '1.25rem',
            },
            backgroundImage: {
                'gradient-brand': 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                'gradient-brand-soft': 'linear-gradient(135deg, rgba(99,102,241,0.18) 0%, rgba(139,92,246,0.18) 100%)',
                'gradient-surface': 'linear-gradient(135deg, #0f1117 0%, #151823 50%, #1e1b4b 100%)',
                'gradient-hero': 'radial-gradient(ellipse at top left, rgba(139,92,246,0.18), transparent 55%), radial-gradient(ellipse at bottom right, rgba(99,102,241,0.16), transparent 55%), linear-gradient(135deg, #0b0d14 0%, #151823 100%)',
            },
            animation: {
                'fade-in': 'fadeIn 0.25s ease-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'pulse-soft': 'pulseSoft 2.4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.6' },
                },
            },
        },
    },

    plugins: [forms],
};
