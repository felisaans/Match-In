/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './*.php',
    './auth/**/*.php',
    './pages/**/*.php',
    './includes/**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // --- Keluarga inti cyan -> blue -> violet (semua hue 175°-255°, cool, konsisten) ---
        'pastel-blue': '#80ECF2',      // hue 185 - cyan terang
        'pastel-sky': '#59B5F6',       // hue 205 - biru langit (primary)
        'pastel-blue-mid': '#5D94E6',  // hue 217 - biru sedang
        'pastel-deep': '#3467B5',      // hue 211 - biru tua (anchor teks/aksen gelap)
        'pastel-purple': '#9B8AE6',    // hue 247 - didinginkan dari magenta (#DB89DD) ke violet-biru
        'pastel-mint': '#6FE3C9',      // hue 165 - cyan-hijau, didaftarkan resmi (dulu liar 3 versi beda)
        'pastel-pink': '#C99BE8',      // hue 272 - lavender-pink, didaftarkan resmi & didinginkan (dulu magenta neon)

        // --- Netral & background ---
        'pastel-bg': '#F2F6F7',
        surface: '#F2F6F7',
        muted: '#61708C',
        primary: '#59B5F6',
        accent: '#9B8AE6',

        // --- Status, "didinginkan" (campuran biru, saturasi diturunkan) ---
        'status-success': '#3FA88A',       // hijau-cyan, bukan emerald default
        'status-success-bg': '#E3F6F1',
        'status-success-text': '#1F5E4D',
        'status-warning': '#C9A227',       // kuning ditarik turun saturasinya + sentuhan abu-biru
        'status-warning-bg': '#F7F2E2',
        'status-warning-text': '#6B5712',
        'status-error': '#C16B8A',         // merah didinginkan ke arah mauve/dusty rose, bukan rose terang
        'status-error-bg': '#F8EBF0',
        'status-error-text': '#7A2F47',

        // --- Dark mode tokens ---
        'dark-bg': '#0F1729',
        'dark-surface': '#162038',
        'dark-surface-2': '#1C2A47',
        'dark-border': '#2A3B5C',
        'dark-text': '#E2E8F4',
        'dark-muted': '#8EA0C2',
      },
      fontFamily: {
        sans: ['Quicksand', 'Poppins', 'Plus Jakarta Sans', 'Segoe UI', 'system-ui', 'sans-serif'],
        display: ['Quicksand', 'Poppins', 'sans-serif'],
      },
      borderRadius: {
        '2xl': '16px',
        '3xl': '24px',
      },
      boxShadow: {
        soft: '0 10px 30px rgba(52, 103, 181, 0.10)',
        card: '0 10px 30px rgba(52, 103, 181, 0.12)',
        lift: '0 14px 40px rgba(52, 103, 181, 0.14)',
        'soft-dark': '0 10px 30px rgba(0, 0, 0, 0.35)',
        'card-dark': '0 10px 30px rgba(0, 0, 0, 0.4)',
        'lift-dark': '0 14px 40px rgba(0, 0, 0, 0.5)',
      },
      animation: {
        'fade-in': 'fadeIn 0.4s ease-out forwards',
        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
        'bounce-soft': 'bounceSoft 0.6s ease-in-out infinite',
        'pulse-ring': 'pulseRing 1.5s ease-out infinite',
        'float-slow': 'floatSlow 8s ease-in-out infinite',
        'float-slower': 'floatSlower 12s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          from: { opacity: '0', transform: 'translateY(8px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        fadeInUp: {
          from: { opacity: '0', transform: 'translateY(16px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        bounceSoft: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-4px)' },
        },
        pulseRing: {
          '0%': { boxShadow: '0 0 0 0 rgba(52, 103, 181, 0.25)' },
          '70%': { boxShadow: '0 0 0 14px rgba(52, 103, 181, 0)' },
          '100%': { boxShadow: '0 0 0 0 rgba(52, 103, 181, 0)' },
        },
        floatSlow: {
          '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
          '50%': { transform: 'translate(10px, -20px) scale(1.05)' },
        },
        floatSlower: {
          '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
          '50%': { transform: 'translate(-15px, 20px) scale(0.95)' },
        },
      },
    },
  },
  plugins: [],
};
