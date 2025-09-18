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
```

### 4. Install the Flux-Pro package
Licence required
```
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
