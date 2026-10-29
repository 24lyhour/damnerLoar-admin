# Frontend rules

Applies to `resources/js/` and every `Modules/*/resources/js/`. Full reference: [../../docs/ARCHITECTURE.md](../../docs/ARCHITECTURE.md).

## Where things go

| What                            | Where                                                                                                                                 |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| shadcn-vue primitives           | `components/ui/` (generated; edit only to fix real bugs)                                                                              |
| Shared app components           | `components/common/`: PageHeader, PageTabs, SectionCard, KpiCard, KpiGrid, DataTable, StatusBadge, BarList, ActivityFeed, ProgressBar |
| List-page table                 | `components/DamnerLaorTable/`: import everything from `@/components/DamnerLaorTable` (see below)                                      |
| Charts                          | `components/charts/`: LineChart, BarChart, DoughnutChart (PrimeVue Chart; colours from `useChartTheme`)                               |
| Shared types                    | `types/` (exported through `types/index.ts`)                                                                                          |
| App-wide config                 | `config/`: navigation, brand, status → label/tone maps                                                                                |
| Helpers                         | `lib/format.ts` (currency, number, percent, dates), `lib/utils.ts` (`cn`)                                                             |
| Module pages                    | `Modules/<Name>/resources/js/pages/Dashboard/<Entity>/<Page>.vue`                                                                     |
| Module components / types / zod | `Modules/<Name>/resources/js/{components,types,validations}/`                                                                         |
| Generated (never edit)          | `routes/`, `actions/`, `wayfinder/` (Wayfinder output)                                                                                |

## Must

- **TypeScript:** `<script setup lang="ts">`, typed `defineProps`, no `any`. Page props match the backend Resource exactly.
- **Routes:** import typed routes from `@/routes/...` (Wayfinder). Never hard-code URLs.
- **Reuse:** check `components/common` before writing a new card, table, badge or header. If you build something two modules could use, put it in `common/`.
- **List pages** (Merchants, Bookings, Hotels…): `DamnerLaorStatusTabs` + `DamnerLaorTableToolbar` (+ `DamnerLaorTableSelectFilter`) + `DamnerLaorTable`, wired with `useTableQuery()`. Paging, sorting and filtering happen on the server; the controller returns `Resource::collection($paginator)` plus a `filters` prop echoing the query.
- **Small tables inside cards** (dashboards, detail pages): `common/DataTable`. Both use typed `columns` and `#cell-<key>="{ row }"` slots.
- **Statuses:** use `StatusBadge` with a tone from the backend enum or a `config/*` map. Pending is grey; never orange.
- **Money and numbers:** use `lib/format.ts`, add `tabular-nums` and right-align numbers. Booking references use `font-mono`.
- **Forms:** Inertia `<Form>` or `useForm`, with shadcn `Field` components and `FieldError`; zod schemas mirror the Laravel Request rules.
- **Theme:** use the theme tokens (`bg-primary`, `text-muted-foreground`, `bg-chart-1`…). No hex colours in components.
- **Layout:** works at phone width; KPI grids use the responsive classes from `KpiGrid`.

## Must not

- No PrimeVue 5 (it needs a commercial licence key). Stay on 4.x.
- No new UI library without asking.
- No `localStorage` for data that matters; only small per-viewer conveniences.
- Don't put business rules in Vue (maker-checker, limits, money maths). Show what the backend sends.

## Checklist before done

`npm run build` · `npx vue-tsc --noEmit` · `npx vp check` (on the files you touched)
