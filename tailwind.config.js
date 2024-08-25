/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                'bittersweet': {
                    '50': '#fef4f2',
                    '100': '#ffe5e1',
                    '200': '#ffd0c9',
                    '300': '#feafa3',
                    '400': '#fb7d6a',
                    '500': '#f35740',
                    '600': '#e03b22',
                    '700': '#bd2e18',
                    '800': '#9c2918',
                    '900': '#81281b',
                    '950': '#461109',
                },
            },
            fontFamily: {
                sans: ['Graphik', 'Nunito', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
