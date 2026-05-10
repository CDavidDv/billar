# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Proyecto

Sistema POS para negocio de billar en México. Gestión de mesas, bar con recetas de coctelería, control especial de caguamas fraccionadas (feature crítico), inventario, estadísticas y pantallas TV.

**Negocio:** 6 mesas billar (común) + 1 cuarto privado + futbolito + máquinas. Roles: Admin, Cajero, Bartender. MXN.

**Progreso actual:** Ver `PROGRESO.md` (tabla con estado ✅/⏳ por módulo). El siguiente paso siempre es el primer ítem `⏳ Pendiente`.

> **REGLA:** Actualizar `PROGRESO.md` al completar cada tarea — cambiar `⏳ Pendiente` → `✅ Completo` y agregar nota si aplica. Hacer esto antes de terminar la sesión o al completar cada módulo/sub-tarea.

**Plan completo:** `C:\Users\carlo\.claude\plans\sorted-wishing-wall.md`

**Design system:** `DESIGN_SYSTEM.md` — leer antes de crear cualquier componente Vue.

---

## Stack

- **Backend:** Laravel 11 / PHP 8.2 — `app/`, `routes/`, `database/`
- **Frontend:** Vue 3 + Inertia.js — `resources/js/`
- **Estilos:** Tailwind CSS v3 (tema oscuro, verde billar + dorado)
- **Roles:** Spatie Laravel Permission v6.25
- **Excel:** Maatwebsite/Laravel-Excel v3.1
- **Mapa visual:** Vue Konva + Konva.js
- **Gráficas:** vue3-apexcharts + apexcharts
- **Íconos:** @heroicons/vue/24/outline
- **DB:** MySQL, base de datos `billar`, usuario `root`, sin password (XAMPP local)
- **URL local:** `http://localhost/billar/public`

---

## Comandos

```bash
# Desarrollo (servidor PHP + Vite HMR simultáneos)
composer run dev

# Solo frontend
npm run dev

# Build producción
npm run build

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed   # reset completo

# Tests
composer run test
php artisan test --filter NombreTest   # test específico

# Linting PHP
./vendor/bin/pint

# Artisan útiles
php artisan make:model NombreModelo -mcs   # model + migration + controller + seeder
php artisan make:controller NombreController --resource
php artisan route:list --name=tabla   # buscar rutas
```

**MySQL local (XAMPP):**
```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root billar
```

---

## Arquitectura Frontend

### Flujo Inertia
`routes/web.php` → Controller → `Inertia::render('Pages/Ruta.vue', $props)` → Vue recibe props como `defineProps`.

No hay API REST separada. Todo pasa por Inertia. Para datos en tiempo real se usa polling con `setInterval` + `router.reload({ only: ['prop'] })`.

### Convención de rutas Vue
```
resources/js/
  Pages/              ← Vistas Inertia (1 por ruta)
    Tables/Index.vue
    Admin/Products/Index.vue
    Display/Kiosk.vue
  Components/
    UI/               ← Componentes base (NavItem, Button, Modal, etc.)
    Charts/           ← Wrappers ApexCharts
  Layouts/
    AppLayout.vue     ← Layout principal (sidebar dark + topbar)
    KioskLayout.vue   ← Pantallas TV (fullscreen, sin navbar)
    GuestLayout.vue   ← Login/register
```

### AppLayout.vue
- Sidebar w-64 con navegación por roles. Sección "Operaciones" visible a todos; sección "Administración" solo si `$page.props.auth.user.roles?.includes('admin')`.
- Prop `title` para topbar. Slot `#header-actions` para botones contextuales.
- Los roles del usuario llegan via `HandleInertiaRequests` — **agregar roles al share()** cuando se implemente Spatie.

### KioskLayout.vue
Para `/display` — fullscreen negro sin chrome. El componente hace polling cada 30s para actualizar contenido.

---

## Arquitectura Backend

### Servicios (app/Services/)
La lógica de negocio va en Services, no en Controllers ni Models:
- `BeerPortionService` — lógica crítica de fraccionamiento de caguamas
- `InventoryService` — movimientos de stock (siempre pasar por aquí, nunca modificar directamente)
- `SessionBillingService` — cálculo de cobro por tiempo de mesa
- `RecipeCostService` — costo real de recetas vs precio de venta
- `StatisticsService` — queries de reportes para dashboard

### Modelos planeados (aún no creados)
```
GameTable         → game_tables (type: billar_comun|billar_privado|futbolito|maquina)
TableSession      → table_sessions (timer, cobro)
Order             → orders (cuenta de mesa o barra)
OrderItem         → order_items
OrderItemModifier → order_item_modifiers
Product           → products (is_beer_product flag)
ProductCategory   → product_categories
ProductRecipe     → product_recipes
RecipeIngredient  → recipe_ingredients (amount_ml, amount_oz, amount_g)
ProductModifier   → product_modifiers (grupos: "nivel picante")
ProductModifierOption → product_modifier_options (con/sin costo extra)
MicheladaRecipe   → michelada_recipes (total_capacity_ml=800, ingredient_volume_ml=200)
Caguama           → caguamas (total_volume_ml=1200, remaining_volume_ml)
BeerPour          → beer_pours
Inventory         → inventory
InventoryMovement → inventory_movements
ScreenContent     → screen_contents
FloorPlanConfig   → floor_plan_config
AppConfiguration  → app_configurations (key/value para precios configurables)
```

### Lógica de caguamas (CRÍTICA)
Una caguama = 1200 mL. Michelada consume `recipe.beer_volume_ml` (default 600 mL = 800 tarro - 200 ingredientes). Al servir: `remaining -= beer_volume_ml`. Alerta si `remaining < min_beer_volume`. Nunca modificar `remaining` directamente — solo via `BeerPortionService`.

### Roles Spatie
Tres roles: `admin`, `cajero`, `bartender`. Registrar en `RolesSeeder`. Middleware de ruta: `->middleware('role:admin')`. En Vue: checar `$page.props.auth.user.roles`.

**Pendiente:** Agregar `roles` al share de `HandleInertiaRequests`:
```php
'auth' => [
    'user' => $request->user()?->load('roles'),
],
```

---

## Design System (resumen)

Tema oscuro. Nunca usar fondos blancos ni grises claros.

| Token | Clase Tailwind | Uso |
|-------|---------------|-----|
| Fondo raíz | `bg-neutral-950` | body |
| Superficie card | `bg-neutral-800` | cards, modals |
| Acento primario | `bg-green-700` | botones, nav activo |
| Dinero/alertas | `text-amber-400` | precios, warnings |
| Mesa disponible | `border-green-700/50` | card mesa libre |
| Mesa ocupada | `border-red-700/50` | card mesa en uso |

Font: **Inter** (Google Fonts, cargada en `resources/views/app.blade.php`). Colores extra: `neutral-750` (#303030) y `neutral-850` (#1f1f1f) definidos en `tailwind.config.js`.

---

## Deploy Hostinger

- Sin procesos persistentes → cron `* * * * * php artisan schedule:run`
- Build local → subir `public/build/` vía git o FTP
- No WebSocket server → polling para tiempo real
- `php artisan config:cache && route:cache && view:cache` antes de deploy

---

## Notas importantes

- **No usar Jetstream.** El proyecto usa Breeze (más simple).
- **Excel:** `maatwebsite/excel` ^3.1 requiere `ext-gd` y `ext-zip` habilitadas en `php.ini`.
- **Impresión tickets:** `window.print()` con CSS `@media print` — sin driver especial.
- **Pantallas TV:** No son smart. Un dispositivo con Chrome en modo kiosk conectado vía HDMI splitter abre `/display`.
- Precios configurables vía tabla `app_configurations` — nunca hardcodear `$50` o `$100` en código.
