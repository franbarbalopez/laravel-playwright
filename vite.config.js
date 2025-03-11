import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'resources/js/laravel-playwright.ts'),
      name: 'LaravelPlaywright',
      fileName: (format) => `laravel-playwright.${format}.js`,
      formats: ['es', 'umd'],
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