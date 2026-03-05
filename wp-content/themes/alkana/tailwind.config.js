/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './**/*.php',
    './src/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        'alkana-orange': '#E8611A',
        'alkana-navy':   '#1A3A5C',
        'alkana-dark':   '#1A1A1A',
        'alkana-muted':  '#666666',
        'alkana-light':  '#F5F5F5',
        'alkana-border': '#E0E0E0',
      },
      fontFamily: {
        heading: ['Montserrat', 'sans-serif'],
        body:    ['Inter', 'sans-serif'],
      },
      borderRadius: {
        btn:  '5px',
        card: '8px',
      },
      boxShadow: {
        card: '0 2px 8px rgba(0,0,0,0.08)',
        'card-hover': '0 6px 20px rgba(0,0,0,0.12)',
      },
    },
  },
  plugins: [],
};
