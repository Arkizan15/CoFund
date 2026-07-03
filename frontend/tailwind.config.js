/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'system-ui', 'sans-serif'],
      },
      colors: {
        sage: {
          50: '#f4f7ed',
          100: '#e4ecce',
          200: '#cddfa3',
          300: '#b1cf6f',
          400: '#98be48',
          500: '#7ba334',
          600: '#5f8028',
          700: '#4a6521',
          800: '#3d511e',
          900: '#34451c',
        },
      },
    },
  },
  plugins: [],
}
