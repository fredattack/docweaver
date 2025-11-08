/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './src/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                'artifacts-primary': {
                    50: '#f5f7ff',
                    100: '#ebf0ff',
                    200: '#d6e1ff',
                    300: '#b8c9ff',
                    400: '#8aa3ff',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
            },
            fontFamily: {
                sans: ['Figtree', 'system-ui', '-apple-system', 'sans-serif'],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'monospace'],
            },
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: 'none',
                        color: '#374151',
                        a: {
                            color: '#4f46e5',
                            '&:hover': {
                                color: '#4338ca',
                            },
                        },
                        code: {
                            backgroundColor: '#f3f4f6',
                            padding: '0.25rem 0.5rem',
                            borderRadius: '0.25rem',
                            fontWeight: '500',
                        },
                        'code::before': {
                            content: '""',
                        },
                        'code::after': {
                            content: '""',
                        },
                    },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
