import globals from "globals";
import pluginJs from "@eslint/js";
import tseslint from "typescript-eslint";


/** @type {import('eslint').Linter.Config[]} */
export default [
  {
    ignores: [
      "**/node_modules/*",
      "**/vendor/*",
      "**/dist/*",
      "**/vite.config.js",
    ],
  },
  {
    files: ["**/*.{js,mjs,cjs,ts}"],
  },
  {languageOptions: { globals: globals.browser }},
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
];