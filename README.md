# lawoo



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

### ⚙️ 4. Initialize Lawoo modules

```bash
php artisan lawoo:init
```

This command will:

•	Create the modules/ directory in your Laravel project 

•	Copy all Lawoo modules (e.g. Core, Demo) from vendor/ to your project root

•	Set up autoloading and prepare the system for use

### 🛠️ 5. Run database migrations

```bash
php artisan migrate
php artisan lawoo:check
```
