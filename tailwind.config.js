/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
    './assets/components/**/*.vue'
  ],
  theme: {
    extend: {
      spacing: {
        'screen-minus-64': 'calc(100vw - 16rem)',
      },
    },
  },
  plugins: [],
}

