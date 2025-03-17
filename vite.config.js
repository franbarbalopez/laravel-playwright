import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'resources/js/laravel-playwright.ts'),
      name: 'LaravelPlaywright',
      fileName: () => 'laravel-playwright.js',
      formats: ['es'],
    },
    outDir: 'dist',
    emptyOutDir: true,
    minify: true,
    rollupOptions: {
      external: ['@playwright/test'],
      output: {
        globals: {
          '@playwright/test': 'playwright.test'
        }
      }
    }
  },
});