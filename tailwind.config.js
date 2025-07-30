/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './templates/**/*.html.twig',
        './assets/**/*.js',
        './assets/components/**/*.vue'
    ],
    safelist: [
        'bg-yellow-400',
        'bg-red-500',
        'bg-amber-500',
        'bg-orange-600',
        'bg-cyan-300',
        'bg-lime-400',
        'bg-yellow-900',
        'bg-pink-950'
    ],
    theme: {
        extend: {
            spacing: {
                'screen-minus-64': 'calc(100vw - 16rem)',
            },
            boxShadow: {
                'dark': '0 4px 6px -1px rgba(0, 0, 0, 0.6)',
            }
        },
    },
    plugins: [],
}

