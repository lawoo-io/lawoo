# lawoo beta-0.0.1



## License

This project is licensed under the [Business Source License 1.1](LICENSE).  
Private and non-commercial use is permitted.  
Commercial use is only allowed with explicit permission.

There is currently no planned license change.

## 🚀 Installation

Lawoo is a modular framework for Laravel-based projects.
This guide walks you through the full setup including Laravel, Composer configuration, and module initialization.

---

### ✅ Requirements

- PHP **>= 8.1**
- Composer **>= 2.0**
- Node.js & npm (optional for frontend assets)
- Git

---

### 🔧 1. Create a new Laravel project

```bash
composer create-project laravel/laravel lawoo-test
cd lawoo-test
```
### 🔧 2. Register the Lawoo repository

```bach
composer config repositories.lawoo vcs https://github.com/lawoo-io/lawoo.git
composer config minimum-stability dev
composer config prefer-stable true
```

### 📦 3. Install the Lawoo package

```bash
composer require lawoo-io/lawoo:dev-main
composer require davidhsianturi/blade-bootstrap-icons
```

### 4. Install the Flux-Pro package
Licence required
```bash
composer config repositories.flux-pro composer https://composer.fluxui.dev
composer require livewire/flux-pro
```

### 5. Configure .env

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lawoo-test
DB_USERNAME=postgres
DB_PASSWORD=12345

...

CACHE_STORE=redis
```

### ⚙️ 6. Initialize Lawoo modules

```bash
php artisan lawoo:init
```

This command will:

•	Create the modules/ directory in your Laravel project 

•	Set up autoloading and prepare the system for use

### 7. Migrate tables
```shell
php artisan migrate --seed
```

### 8. Scann all Modules
```shell
php artisan lawoo:check
```

### 9. Install Base Module
```shell
php artisan lawoo:install Web
```

### 10. Change vite.config.js (only for production)
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fg from 'fast-glob'

// Dynamisch alle Assets einsammeln
const resourceInputs = fg.sync([
    'resources/js/**/*.js',
    'resources/css/**/*.css',
    'resources/views/websites/**/assets/css/*.*',
    'resources/views/websites/**/assets/js/*.*',
    'resources/views/websites/**/assets/svg/*.*',
    'resources/images/**/*.{png,jpg,jpeg,svg,gif,webp}'
])


export default defineConfig({
    plugins: [
        laravel({
            input: resourceInputs,
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### 11. Public Storage link
```shell
php artisan storage:link
```
