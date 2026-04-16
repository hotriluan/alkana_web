/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './**/*.php',
    './src/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Legacy single-value tokens (backward compat)
        'alkana-navy':   '#1A3A5C',
        'alkana-dark':   '#1A1A2E',
        'alkana-muted':  '#9CA3AF',
        'alkana-light':  '#FAF8F5',
        'alkana-border': '#E5E7EB',
        // Brand purple scale — Alkana Identity
        'alkana-purple': {
          50:  'var(--color-alkana-purple-50)',   // #F3EAFC
          100: 'var(--color-alkana-purple-100)',  // #E8D5F5
          200: 'var(--color-alkana-purple-200)',  // #D4AEE9
          300: 'var(--color-alkana-purple-300)',  // #B87EDD — BRAND LIGHT
          400: 'var(--color-alkana-purple-400)',  // #9F5ACC
          500: 'var(--color-alkana-purple-500)',  // #8236BC — BRAND MEDIUM
          600: 'var(--color-alkana-purple-600)',  // #6E2BAE
          700: 'var(--color-alkana-purple-700)',  // #67219D — BRAND PRIMARY
          800: 'var(--color-alkana-purple-800)',  // #5A1A8F
          900: 'var(--color-alkana-purple-900)',  // #4C0682 — BRAND DARK
        },
        // Surface palette
        surface: {
          white: 'var(--color-surface-white)',
          warm:  'var(--color-surface-warm)',
          cool:  'var(--color-surface-cool)',
          dark:  'var(--color-surface-dark)',
        },
        // Gold accent
        gold: {
          300: 'var(--color-gold-300)',
          400: 'var(--color-gold-400)',
          500: 'var(--color-gold-500)',
        },
      },
      fontFamily: {
        heading: ['Montserrat', 'sans-serif'],
        body:    ['Inter', 'sans-serif'],
      },
      borderRadius: {
        xs:   'var(--radius-xs)',
        sm:   'var(--radius-sm)',
        btn:  'var(--radius-btn)',
        card: 'var(--radius-card)',
        lg:   'var(--radius-lg)',
        xl:   'var(--radius-xl)',
      },
      boxShadow: {
        xs:     'var(--shadow-xs)',
        sm:     'var(--shadow-sm)',
        md:     'var(--shadow-md)',
        lg:     'var(--shadow-lg)',
        xl:     'var(--shadow-xl)',
        '2xl':  'var(--shadow-2xl)',
        purple: 'var(--shadow-purple)',
        // Legacy
        card:        'var(--shadow-sm)',
        'card-hover':'var(--shadow-md)',
      },
      transitionTimingFunction: {
        'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
        'spring':   'cubic-bezier(0.175, 0.885, 0.32, 1.275)',
        'smooth':   'cubic-bezier(0.4, 0, 0.2, 1)',
      },
      transitionDuration: {
        fast:   '150ms',
        normal: '300ms',
        slow:   '500ms',
        slide:  '700ms',
      },
    },
  },
  plugins: [],
};
