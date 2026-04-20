# SVM Kassensystem

Ein modernes Kassensystem für den Einzelhandel, entwickelt mit Laravel, Vue.js und Filament Admin.

## 📋 Funktionen

- **POS (Point of Sale) Interface**
  - Schnelle Produktauswahl nach Kategorien
  - Warenkorb-Verwaltung mit Mengen
  - Druckfunktion für Bestellscheine
  - Admin Login für Produktverwaltung

- **Produktverwaltung (Filament Admin)**
  - Kategorie-Verwaltung
  - Produkt CRUD-Operations
  - CSV Import/Export
  - Benutzerfreundliche Oberfläche

## 🛠️ Technologie-Stack

### Backend
- **Laravel 13** - PHP Framework
- **PHP 8.4** - Runtime
- **Laravel Fortify** - Authentifizierung

### Frontend
- **Vue.js 3** - SPA Framework
- **Inertia.js 3** - Server-SPA Integration
- **Tailwind CSS 4** - Styling
- **Alpine.js** - Reaktivität

### Admin Panel
- **Filament 5** - Admin Dashboard

### Entwicklung
- **Pest PHP** - Testing Framework
- **Hotwire** - Interaktionen ohne Page Reload
- **Laravel Sail** - Docker Entwicklungsumgebung

## 🚀 Installation

### Anforderungen
- PHP >= 8.4
- Composer
- Node.js & NPM

### Setup

1. **Abhängigkeiten installieren**
```bash
composer install
npm install
```

2. **Umweltvariablen konfigurieren**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Datenbank migrieren**
```bash
php artisan migrate
```

4. **Frontend aufbauen**
```bash
npm run build
```

5. **Starten**
```bash
composer run dev
```

## 📁 Verzeichnisstruktur

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── CategoryController.php
│   │   │   │   └── ProductController.php
│   │   │   └── PosController.php
│   │   └── Middleware/
│   └── Models/
│       ├── Category.php
│       └── Product.php
├── database/
│   ├── migrations/
│   │   ├── 2026_04_20_150606_create_categories_table.php
│   │   └── 2026_04_20_150606_create_products_table.php
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── layouts/
│   │   │   ├── PosLayout.vue
│   │   │   ├── AppLayout.vue
│   │   │   └── AuthLayout.vue
│   │   └── pages/
│   │       ├── Pos.vue
│   │       └── admin/
│   └── views/
├── routes/
│   └── web.php
└── tests/
```

## 🔐 Sicherheitsmerkmale

- Authentifizierung via Laravel Fortify
- Token-basierte Sitzungen
- Middleware-geschützte Admin-Routen

## 📝 Lizenz

Dieses Projekt steht unter der MIT-Lizenz.
