import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
  plugins: [tailwindcss()],

  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,          // generates dist/manifest.json — used by functions.php for cache-busting
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'src/scripts/app.js'),
        // CSS entry — emits separate file tracked in manifest as 'src/styles/app.css'
        'src/styles/app.css': resolve(__dirname, 'src/styles/app.css'),
      },
    },
    // Target modern browsers — reduces bundle size
    target: ['es2020', 'chrome90', 'firefox88', 'safari14'],
  },

  // Dev server (not used on shared hosting — local only)
  server: {
    port: 3000,
  },
});
