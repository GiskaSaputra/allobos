import forms from '@tailwindcss/forms';

const token = (name) => `rgb(var(--${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
        './app/Enums/*.php',
    ],

    // Varian tombol dan lencana disusun dinamis dari props komponen dan enum.
    safelist: [
        ...['primary', 'accent', 'secondary', 'ghost', 'danger', 'whatsapp', 'glass', 'icon', 'sm', 'lg', 'xl', 'full'].map((v) => `btn-${v}`),
        ...['neutral', 'success', 'warning', 'danger', 'info', 'brand'].map((v) => `badge-${v}`),
    ],

    theme: {
        extend: {
            colors: {
                surface: {
                    DEFAULT: token('surface'),
                    alt: token('surface-alt'),
                    raised: token('surface-raised'),
                },
                fg: {
                    DEFAULT: token('fg'),
                    body: token('fg-body'),
                    muted: token('fg-muted'),
                },
                line: token('line'),
                brand: { DEFAULT: token('brand'), fg: token('brand-fg') },
                accent: { DEFAULT: token('accent'), fg: token('accent-fg'), text: token('accent-text') },
                success: token('success'),
                warning: token('warning'),
                danger: token('danger'),
                info: token('info'),
                'status-fg': token('status-fg'),
                ink: {
                    DEFAULT: token('ink'),
                    raised: token('ink-raised'),
                    fg: token('ink-fg'),
                    muted: token('ink-muted'),
                },
            },
            fontFamily: {
                sans: ['Ubuntu', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['Montserrat Variable', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            borderRadius: {
                card: '28px',
                panel: '40px',
            },
            boxShadow: {
                card: '0 1px 2px rgb(20 26 12 / 0.06), 0 8px 24px -12px rgb(20 26 12 / 0.18)',
                float: '0 24px 60px -20px rgb(20 26 12 / 0.35)',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            zIndex: { nav: '40', drawer: '50', modal: '60', flash: '70' },
        },
    },

    plugins: [forms],
};
