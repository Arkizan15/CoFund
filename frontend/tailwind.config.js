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
        // ChatBubble design system
        bubble: {
          primary: '#22C55E',
          secondary: '#3B82F6',
          tertiary: '#A855F7',
          surface: '#F3F4F6',
          sent: '#22C55E',
          received: '#F3F4F6',
        },
        chat: {
          success: '#22C55E',
          warning: '#F59E0B',
          error: '#EF4444',
          info: '#3B82F6',
        },
      },
      borderRadius: {
        bubble: '20px',
        'bubble-sm': '14px',
      },
      animation: {
        'bounce-in': 'bounce-in 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55)',
        'pop-in': 'pop-in 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)',
        'slide-up': 'slide-up 0.4s ease-out',
        'fade-in': 'fade-in 0.3s ease-out',
        'scale-up': 'scale-up 0.3s ease-out',
        'check-bounce': 'check-bounce 0.6s ease-out',
        'confetti-fall': 'confetti-fall 1s ease-out forwards',
        'pulse-ring': 'pulse-ring 1.5s ease-out infinite',
      },
      keyframes: {
        'bounce-in': {
          '0%': { transform: 'scale(0.3)', opacity: '0' },
          '50%': { transform: 'scale(1.05)' },
          '70%': { transform: 'scale(0.9)' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
        'pop-in': {
          '0%': { transform: 'scale(0) rotate(-10deg)', opacity: '0' },
          '60%': { transform: 'scale(1.2) rotate(3deg)' },
          '100%': { transform: 'scale(1) rotate(0deg)', opacity: '1' },
        },
        'slide-up': {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'scale-up': {
          '0%': { transform: 'scale(0.95)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
        'check-bounce': {
          '0%': { transform: 'scale(0) rotate(-45deg)', opacity: '0' },
          '50%': { transform: 'scale(1.3) rotate(0deg)' },
          '100%': { transform: 'scale(1) rotate(0deg)', opacity: '1' },
        },
        'confetti-fall': {
          '0%': { transform: 'translateY(-20px) rotate(0deg) scale(0)', opacity: '1' },
          '50%': { transform: 'translateY(10px) rotate(180deg) scale(1.2)' },
          '100%': { transform: 'translateY(40px) rotate(360deg) scale(0)', opacity: '0' },
        },
        'pulse-ring': {
          '0%': { transform: 'scale(0.8)', opacity: '1' },
          '50%': { opacity: '0.5' },
          '100%': { transform: 'scale(1.3)', opacity: '0' },
        },
      },
    },
  },
  plugins: [],
}
