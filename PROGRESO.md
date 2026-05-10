# Billar — Progreso de Desarrollo

> Última actualización: 2026-05-07 (sesión 5)
> Stack: Laravel 11 · Vue 3 · Inertia.js · Tailwind CSS · MySQL · Hostinger Shared Hosting
> Directorio: `C:\xampp\htdocs\billar`

---

## Estado General

| Fase | Módulo | Estado | Notas |
|------|--------|--------|-------|
| 0 | Setup proyecto Laravel 11 | ✅ Completo | |
| 0 | Instalar Breeze (Inertia/Vue) | ✅ Completo | Vue 3 + Inertia.js |
| 0 | Instalar Spatie Permission | ✅ Completo | v6.25 (PHP 8.2) |
| 0 | Instalar Maatwebsite Excel | ✅ Completo | v3.1 con phpspreadsheet |
| 0 | Instalar Vue Konva | ✅ Completo | + Konva.js |
| 0 | Instalar ApexCharts | ✅ Completo | vue3-apexcharts |
| 0 | Configurar .env + DB | ✅ Completo | MySQL billar, locale es_MX |
| 0 | Design system Tailwind | ✅ Completo | Inter font, tema oscuro, tokens |
| 0 | AppLayout.vue + NavItem.vue | ✅ Completo | Sidebar con roles, topbar |
| 0 | KioskLayout.vue | ✅ Completo | Para pantallas TV |
| 0 | Migrations base + Seeders (roles) | ✅ Completo | RolesSeeder + GameTableSeeder, admin@billar.local |
| 1 | Gestión de mesas y sesiones | ✅ Completo | CRUD mesas, sesiones con timer, cuenta, cobro |
| 2 | Catálogo + Recetas + Modificadores | ✅ Completo | CRUD productos/categorías, RecipeBuilder, modificadores, RecipeCostService |
| 3 | Control de cervezas (caguamas) | ✅ Completo | Migrations, models, BeerPortionService, CaguamaController, Vue page |
| 4 | Inventario | ✅ Completo | Migrations, models, InventoryService, Admin/InventoryController, Vue page |
| 5 | Dashboard y estadísticas | ✅ Completo | StatisticsService, DashboardController, Dashboard/Index.vue, 3 chart components |
| 6 | Import/Export Excel | ✅ Completo | ProductsImport, SalesExport, InventoryExport, Admin/ExcelController, Vue page |
| 7 | Gestión de pantallas (kiosk) | ✅ Completo | ScreenContent, DisplayController, Kiosk.vue, Admin/Screens.vue |
| 8 | Administración general | ✅ Completo | UserController, ConfigController, AppConfiguration, Admin/Users, Admin/Config |
| 9 | Mapa visual del establecimiento | ✅ Completo | FloorPlanConfig, FloorPlanController, Editor Konva, FloorPlan/Index (cajero) |
| 10 | Menú digital QR | ✅ Completo | MenuController, Menu/Index.vue, pedidos desde QR |
| 11 | Solicitud de canciones | ✅ Completo | SongRequest, SongRequestController, Menu/Songs.vue |
| 12 | Interfaz de inicio mejorada | ✅ Completo | Welcome.vue personalizada, registro deshabilitado |
| 13 | Sistema de Sucursales | ✅ Completo | Branch model, user_branches, selector en header, filtros por branch |

**Leyenda:** ✅ Completo · 🔄 En progreso · ⏳ Pendiente · ❌ Bloqueado · 🔮 Futuro

---

## Fase 0 — Setup del Proyecto

### Comandos ejecutados
```bash
# Crear proyecto Laravel 11
composer create-project laravel/laravel C:/xampp/htdocs/billar_temp --prefer-dist
# (luego mover archivos a billar/)

# Pendientes:
php artisan breeze:install vue --inertia
npm install
composer require spatie/laravel-permission
composer require maatwebsite/excel
npm install vue-konva konva
npm install vue3-apexcharts apexcharts
```

### Archivos clave creados en esta fase
- `DESIGN_SYSTEM.md` — Design tokens y componentes base
- `database/migrations/` — Todas las migraciones del sistema
- `database/seeders/RolesSeeder.php` — Roles y permisos iniciales
- `resources/js/` — Estructura de componentes Vue

---

## Módulo 1 — Mesas y Sesiones

### Archivos a crear
- `app/Models/GameTable.php`
- `app/Models/TableSession.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Services/SessionBillingService.php`
- `app/Http/Controllers/TableSessionController.php`
- `resources/js/Pages/Tables/Index.vue` — Panel cajero
- `resources/js/Pages/Tables/Session.vue` — Vista de sesión activa
- `resources/js/Pages/Admin/Tables/Index.vue` — CRUD mesas

### Estado: ✅ Completo

**Completado 2026-05-05:** migrations, models, SessionBillingService, TableController, Admin/GameTableController, 3 Vue pages, 10 rutas.

---

## Módulo 2 — Catálogo + Recetas + Modificadores

### Archivos a crear
- `app/Models/ProductCategory.php`
- `app/Models/Product.php`
- `app/Models/ProductRecipe.php`
- `app/Models/RecipeIngredient.php`
- `app/Models/ProductModifier.php`
- `app/Models/ProductModifierOption.php`
- `app/Services/RecipeCostService.php`
- `resources/js/Pages/Admin/Products/Index.vue`
- `resources/js/Pages/Admin/Products/RecipeBuilder.vue`

### Estado: ✅ Completo

**Completado 2026-05-05:** migrations (product_categories, products, product_recipes, recipe_ingredients, product_modifiers, product_modifier_options), 6 models, RecipeCostService, ProductController + ProductCategoryController, 7 rutas admin, 2 Vue pages (Index + RecipeBuilder), component CSS utilities en app.css, ProductCatalogSeeder con datos de ejemplo.

---

## Módulo 3 — Control de Cervezas (Caguamas)

### Archivos a crear
- `app/Models/MicheladaRecipe.php`
- `app/Models/Caguama.php`
- `app/Models/BeerPour.php`
- `app/Services/BeerPortionService.php`
- `resources/js/Pages/Caguamas/Index.vue`

### Estado: ⏳ Pendiente

---

## Módulo 4 — Inventario

### Archivos a crear
- `app/Models/Inventory.php`
- `app/Models/InventoryMovement.php`
- `app/Services/InventoryService.php`
- `resources/js/Pages/Admin/Inventory/Index.vue`

### Estado: ⏳ Pendiente

---

## Módulo 5 — Dashboard y Estadísticas

### Archivos a crear
- `app/Http/Controllers/DashboardController.php`
- `app/Services/StatisticsService.php`
- `resources/js/Pages/Dashboard/Index.vue`
- `resources/js/Components/Charts/SalesChart.vue`
- `resources/js/Components/Charts/TopProductsChart.vue`
- `resources/js/Components/Charts/HeatmapChart.vue`

### Estado: ⏳ Pendiente

---

## Módulo 6 — Import/Export Excel

### Archivos a crear
- `app/Imports/ProductsImport.php`
- `app/Exports/SalesExport.php`
- `app/Exports/InventoryExport.php`
- `resources/js/Pages/Admin/Import.vue`

### Estado: ⏳ Pendiente

---

## Módulo 7 — Gestión de Pantallas

### Archivos a crear
- `app/Models/ScreenContent.php`
- `app/Http/Controllers/DisplayController.php`
- `resources/js/Pages/Display/Kiosk.vue`
- `resources/js/Pages/Admin/Screens.vue`

### Estado: ⏳ Pendiente

---

## Módulo 8 — Administración General

### Archivos a crear
- `resources/js/Pages/Admin/Users/Index.vue`
- `resources/js/Pages/Admin/Config/Index.vue`
- `app/Models/AppConfiguration.php`

### Estado: ⏳ Pendiente

---

## Módulo 9 — Mapa Visual (Vue Konva)

### Archivos a crear
- `resources/js/Pages/FloorPlan/Index.vue` — Vista cajero (real-time)
- `resources/js/Pages/Admin/FloorPlan/Editor.vue` — Setup único del layout
- `app/Http/Controllers/FloorPlanController.php`
- `app/Models/FloorPlanConfig.php`

### Estado: ⏳ Pendiente

---

## Notas para Continuar en Nueva Sesión

### Contexto del negocio
- 6 mesas billar (común) + 1 cuarto privado + futbolito + máquinas
- Roles: Admin, Cajero, Bartender
- Control de caguamas = feature más crítico
- Precios: $50/hr billar, $100 cuarto privado
- Deploy destino: Hostinger shared hosting

### Cómo retomar
1. Leer este archivo (`PROGRESO.md`)
2. Leer `DESIGN_SYSTEM.md` para estilos
3. Leer `C:\Users\carlo\.claude\plans\sorted-wishing-wall.md` para plan completo
4. Identificar primer ítem con estado `⏳ Pendiente` en Fase 0
5. Continuar desde ahí

### Variables de entorno requeridas (.env)
```
DB_DATABASE=billar
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost/billar/public
```
