# 40Q Autonomy AI Hub

Admin hub and navigation shell for 40Q AI packages. It centralizes settings pages, menus, and shared helpers so individual AI packages stay lean.

## Features
- Top-level “40Q Autonomy AI” menu with Overview and Settings pages.
- Adds submenus for installed AI packages (e.g., SEO Assistant, Alt Text Copilot).
- Hides legacy settings menus from packages to keep navigation in one place.
- Publishes a configurable list of packages and shared env keys (`config/autonomy-ai.php`).

## Installation (monorepo)
1. Ensure the Composer path repo includes this package (already present in this project):
   ```json
   "repositories": [
     { "type": "path", "url": "packages/*/*", "options": { "symlink": true } }
   ],
   "require": {
     "40q/autonomy-ai-hub": "*"
   }
   ```
2. Install/refresh autoloaders:
   ```bash
   composer update 40q/autonomy-ai-hub
   ```
3. Activate **40Q Autonomy AI Hub** in wp-admin → Plugins (required by downstream AI packages).
4. No manual provider registration is needed. Acorn auto-discovers `FortyQ\AutonomyAiHub\AutonomyAiServiceProvider` via the package’s `composer.json` `extra.acorn.providers` entry.

## Configuration
- Publish config if you need per-site overrides:
  ```bash
  wp acorn vendor:publish --tag=autonomy-ai-config
  ```
- Key settings live in `config/autonomy-ai.php`:
  - `packages`: metadata and manage links for each AI package.
  - `settings.env_keys`: env variables surfaced in the hub Settings screen.
  - Menu title/position/capability.

## Package integration
- AI packages should:
  - Depend on `40q/autonomy-ai-hub`.
  - Detect the hub and register their settings page under the hub submenu.
  - Avoid standalone menus when the hub is active (the hub hides duplicates).

## Development
- Code lives at `packages/40q/autonomy-ai-hub`.
- Provider: `FortyQ\AutonomyAiHub\AutonomyAiServiceProvider`.
- Views: `resources/views`.
- Tests: `tests/`.
