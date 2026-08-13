# Isolated Stage 0 solver evidence

This directory is evidence for S0-01. It is not an application and must never be used as the production or legacy project root.

- `composer.json` resolves only the Laravel 13 / Filament 5 / Livewire 4 core graph with Composer platform PHP 8.3.0.
- `composer.lock` contains 108 packages. It was generated with scripts and plugins disabled. The first normal solver run stopped on missing `ext-intl`; the lock was then generated while ignoring only that already-recorded local platform gap.
- `package.json` mirrors the official Laravel 13 frontend constraints for the isolated target theme.
- `package-lock.json` contains the exact target-only Vite/Tailwind graph. No `node_modules` installation and no build were performed.

Revalidation on target staging must be performed without ignore flags:

```powershell
composer check-platform-reqs --lock --no-dev
npm ci --ignore-scripts
npm audit
```

The target may proceed only when `ext-intl` and every other required extension are present in both PHP CLI and the dedicated FPM pool. Legacy `composer.lock`, `package-lock.json`, `webpack.mix.js`, `public/mix-manifest.json` and public bundles remain immutable.
