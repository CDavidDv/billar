# Billar — Design System

> Stack: Tailwind CSS v3 · Vue 3 · Inertia.js
> Tema: Oscuro. Ambientación de billar nocturno. Colores profundos, acentos verdes (paño de billar) y dorados.

---

## Paleta de Colores

### Base (fondo / superficie)
```
bg-neutral-950   #0a0a0a   Fondo raíz (body)
bg-neutral-900   #171717   Sidebar, nav
bg-neutral-800   #262626   Cards, modals, paneles
bg-neutral-700   #404040   Input backgrounds, hover states
bg-neutral-600   #525252   Bordes sutiles, dividers
```

### Acento Principal — Verde Billar
```
green-700        #15803d   Acento principal (botones primarios, links activos)
green-600        #16a34a   Hover estado primario
green-500        #22c55e   Indicador "mesa disponible", éxito
green-400        #4ade80   Texto acento sobre fondos oscuros
green-900        #14532d   Badge backgrounds, chips sutiles
```

### Acento Secundario — Dorado
```
amber-500        #f59e0b   Alertas, warnings, precio/dinero highlights
amber-400        #fbbf24   Hover dorado
amber-900        #78350f   Badge warning background
```

### Estados Semánticos
```
Estado          Color Tailwind     Uso
────────────────────────────────────────────
Disponible      green-500          Mesa libre, stock OK
Ocupada         red-500            Mesa en uso
Mantenimiento   neutral-500        Mesa inactiva
Alerta stock    amber-500          Inventario bajo
Error           red-600            Errores de formulario
Info            blue-500           Información neutral
```

### Texto
```
text-white           Títulos, contenido principal
text-neutral-300     Texto secundario, labels
text-neutral-400     Placeholders, texto deshabilitado
text-neutral-500     Texto muy sutil, timestamps
text-green-400       Links activos, énfasis acento
text-amber-400       Cantidades en dinero, precios
```

---

## Tipografía

```
Font family: Inter (Google Fonts) — cargar en app.blade.php
Fallback: system-ui, sans-serif

Escala:
text-xs      12px   Timestamps, badges, notas
text-sm      14px   Labels, tabla data, inputs
text-base    16px   Texto cuerpo, botones
text-lg      18px   Subtítulos de sección
text-xl      20px   Títulos de card
text-2xl     24px   Page headers
text-3xl     30px   Dashboard KPIs grandes
text-4xl     36px   Timer de mesa (prominente)

Pesos:
font-normal   400   Texto cuerpo
font-medium   500   Labels, nav items
font-semibold 600   Títulos de card, botones
font-bold     700   Page headers, KPIs
```

---

## Componentes Base

### Botones

```html
<!-- Primario (acción principal) -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600
               text-white font-semibold text-sm rounded-lg transition-colors duration-150
               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
               focus:ring-offset-neutral-900 disabled:opacity-50 disabled:cursor-not-allowed">

<!-- Secundario (acción alternativa) -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-700 hover:bg-neutral-600
               text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600
               transition-colors duration-150 focus:outline-none focus:ring-2
               focus:ring-neutral-500 focus:ring-offset-2 focus:ring-offset-neutral-900">

<!-- Peligro (eliminar, cancelar) -->
<button class="inline-flex items-center gap-2 px-4 py-2 bg-red-700 hover:bg-red-600
               text-white font-semibold text-sm rounded-lg transition-colors duration-150">

<!-- Ghost (solo borde) -->
<button class="inline-flex items-center gap-2 px-4 py-2 border border-neutral-600
               hover:border-neutral-500 hover:bg-neutral-800 text-neutral-300
               font-semibold text-sm rounded-lg transition-colors duration-150">

<!-- Tamaños adicionales -->
px-3 py-1.5 text-xs   → sm
px-5 py-2.5 text-base → lg
px-6 py-3   text-base → xl (solo CTA prominentes)
```

### Cards

```html
<!-- Card base -->
<div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">

<!-- Card con header -->
<div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
  <div class="px-5 py-4 border-b border-neutral-700 flex items-center justify-between">
    <h3 class="text-white font-semibold text-lg">Título</h3>
  </div>
  <div class="p-5"> <!-- contenido --> </div>
</div>

<!-- Card mesa (billar) — estado disponible -->
<div class="bg-neutral-800 rounded-xl border border-green-700/50 p-4 cursor-pointer
            hover:border-green-600 hover:bg-neutral-750 transition-all duration-150
            ring-1 ring-green-700/20">

<!-- Card mesa — estado ocupada -->
<div class="bg-neutral-800 rounded-xl border border-red-700/50 p-4 cursor-pointer
            hover:border-red-600 transition-all duration-150 ring-1 ring-red-700/20">
```

### Inputs

```html
<!-- Input base -->
<input class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2
              text-white placeholder-neutral-400 text-sm
              focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent
              transition-colors duration-150">

<!-- Label -->
<label class="block text-sm font-medium text-neutral-300 mb-1.5">

<!-- Error message -->
<p class="mt-1 text-xs text-red-400">Mensaje de error</p>

<!-- Select -->
<select class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2
               text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500
               focus:border-transparent">

<!-- Textarea -->
<textarea class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2
                 text-white placeholder-neutral-400 text-sm resize-none
                 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
```

### Badges / Pills

```html
<!-- Verde (disponible, activo) -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
             bg-green-900 text-green-400">Disponible</span>

<!-- Rojo (ocupada, error) -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
             bg-red-900/50 text-red-400">Ocupada</span>

<!-- Amarillo (alerta, pendiente) -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
             bg-amber-900/50 text-amber-400">Alerta</span>

<!-- Gris (inactivo, mantenimiento) -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
             bg-neutral-700 text-neutral-400">Inactivo</span>
```

### Tablas

```html
<div class="overflow-hidden rounded-xl border border-neutral-700">
  <table class="w-full text-sm">
    <thead class="bg-neutral-900">
      <tr>
        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">
          Columna
        </th>
      </tr>
    </thead>
    <tbody class="divide-y divide-neutral-700 bg-neutral-800">
      <tr class="hover:bg-neutral-750 transition-colors duration-100">
        <td class="px-4 py-3 text-neutral-200">Dato</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Modales

```html
<!-- Overlay -->
<div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <!-- Modal container -->
  <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
      <h2 class="text-white font-semibold text-lg">Título</h2>
      <button class="text-neutral-400 hover:text-white transition-colors">✕</button>
    </div>
    <!-- Body -->
    <div class="px-6 py-5"> <!-- contenido --> </div>
    <!-- Footer -->
    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-neutral-700">
      <!-- botones -->
    </div>
  </div>
</div>
```

### Alertas / Notificaciones

```html
<!-- Info -->
<div class="flex items-start gap-3 p-4 bg-blue-900/30 border border-blue-700/50 rounded-lg">
  <span class="text-blue-400 text-lg">ℹ</span>
  <p class="text-blue-300 text-sm">Mensaje informativo</p>
</div>

<!-- Éxito -->
<div class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
  <span class="text-green-400 text-lg">✓</span>
  <p class="text-green-300 text-sm">Operación exitosa</p>
</div>

<!-- Warning -->
<div class="flex items-start gap-3 p-4 bg-amber-900/30 border border-amber-700/50 rounded-lg">
  <span class="text-amber-400 text-lg">⚠</span>
  <p class="text-amber-300 text-sm">Precaución</p>
</div>

<!-- Error -->
<div class="flex items-start gap-3 p-4 bg-red-900/30 border border-red-700/50 rounded-lg">
  <span class="text-red-400 text-lg">✕</span>
  <p class="text-red-300 text-sm">Error</p>
</div>
```

---

## Layout del Sistema

### Estructura general (AppLayout)

```
┌──────────────────────────────────────────────────────────────┐
│ SIDEBAR (w-64, bg-neutral-900)    MAIN CONTENT               │
│ ┌────────────────────┐            (flex-1, bg-neutral-950)   │
│ │ Logo / Nombre app  │  ┌─────────────────────────────────┐  │
│ │                    │  │ TOPBAR (h-14, bg-neutral-900)   │  │
│ │ ─────────────────  │  │ Page title + user menu          │  │
│ │ Nav items:         │  └─────────────────────────────────┘  │
│ │ > Mesas (cajero)   │  ┌─────────────────────────────────┐  │
│ │ > Caguamas         │  │                                 │  │
│ │ > Mapa             │  │   PAGE CONTENT                  │  │
│ │ > Productos        │  │   (p-6)                         │  │
│ │ > Inventario       │  │                                 │  │
│ │ > Dashboard        │  └─────────────────────────────────┘  │
│ │ > Pantallas        │                                        │
│ │ > Admin ▾          │                                        │
│ │   · Usuarios       │                                        │
│ │   · Mesas          │                                        │
│ │   · Config         │                                        │
│ │   · Mapa editor    │                                        │
│ │                    │                                        │
│ │ ─────────────────  │                                        │
│ │ [User] [Cerrar]    │                                        │
│ └────────────────────┘                                        │
└──────────────────────────────────────────────────────────────┘
```

### Nav items por rol

```
Admin:    Todo
Cajero:   Mesas, Mapa, Caguamas, Pedidos del día
Bartender: Pedidos activos, Caguamas, Productos (solo ver)
```

---

## KPI Cards (Dashboard)

```html
<!-- KPI grande (ventas del día) -->
<div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
  <p class="text-neutral-400 text-sm font-medium">Ventas hoy</p>
  <p class="text-3xl font-bold text-white mt-1">$2,450</p>
  <p class="text-xs text-green-400 mt-1">↑ 12% vs ayer</p>
</div>
```

---

## Estados de Mesa (componente central)

```
Disponible:   border-green-700/50 + ring-green-700/20 + badge verde
Ocupada:      border-red-700/50   + ring-red-700/20   + badge rojo + timer
Mantenimiento: border-neutral-600  + opacity-60        + badge gris
```

---

## Tailwind Config Extendido

```javascript
// tailwind.config.js
module.exports = {
  content: ['./resources/**/*.{vue,js,php,blade.php}'],
  theme: {
    extend: {
      colors: {
        neutral: {
          750: '#303030',  // entre 700 y 800 — hover states
          850: '#1f1f1f',  // entre 800 y 900
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      animation: {
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ]
}
```

---

## Íconos

Usar **Heroicons** (compatible con Vue 3):
```bash
npm install @heroicons/vue
```

```javascript
// Uso en Vue
import { ClockIcon, TableCellsIcon, BeerIcon } from '@heroicons/vue/24/outline'
```

---

## Responsive

Sistema diseñado principalmente para **uso en tablet/PC** (POS). No se prioriza mobile.

```
Breakpoints usados:
sm:   640px  — Raramente usado
md:   768px  — Tablet portrait
lg:   1024px — Tablet landscape / laptop
xl:   1280px — Desktop (pantalla principal cajero)
```

---

## Convenciones Vue

```
Pages/           → Vistas Inertia (una por ruta)
Components/      → Componentes reutilizables
Components/UI/   → Botones, inputs, modales base
Components/Charts/ → Wrappers de ApexCharts
Layouts/         → AppLayout.vue, GuestLayout.vue, KioskLayout.vue
```
