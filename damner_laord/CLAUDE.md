# DamnerLaor Admin

Admin back office for **DamnerLaor (ដំណើរល្អ)**, a Cambodian booking platform for bus tickets, hotels and bus + hotel packages. Admins manage merchants, transport, hotels, bookings, payments, refunds, payouts, promotions and platform settings. A customer app and a merchant app talk to the same backend through `/api/v1`.

- **Spec (source of truth for screens and business rules):** `DamnerLaor_Admin_Dashboard_UIUX_v1.2.pdf` in this folder (gitignored, local only). Screen numbers like `04.3` refer to it.
- **Architecture and full rules:** [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md). Read it before creating a module, entity or page.
- **Folder rules:** [Modules/CLAUDE.md](Modules/CLAUDE.md) (backend), [resources/js/CLAUDE.md](resources/js/CLAUDE.md) (frontend).

## Stack

Laravel 13 · PHP 8.4 · nwidart/laravel-modules · Fortify (auth, 2FA, passkeys) · Inertia v3 · Vue 3.5 + TypeScript · Tailwind v4 · shadcn-vue (base UI) · PrimeVue **4.x** (DataTable, DatePicker, Chart only) · Wayfinder (typed routes) · MySQL locally, SQLite in tests.

## Commands

Run everything from this folder (`damner_laord/`), never from the parent.

| Task                     | Command                                                        |
| ------------------------ | -------------------------------------------------------------- |
| Dev server               | `composer dev` (site: http://damnerlaor.test)                  |
| Build                    | `npm run build`                                                |
| Types                    | `npx vue-tsc --noEmit`                                         |
| JS lint + format         | `npx vp check` (fix: `npx vp check --fix`)                     |
| PHP format               | `vendor/bin/pint`                                              |
| PHP static analysis      | `vendor/bin/phpstan analyse`                                   |
| Tests                    | `php artisan test`                                             |
| New module               | `php artisan module:make <Name>` then `composer dump-autoload` |
| Regenerate routes for TS | `php artisan wayfinder:generate --with-form`                   |

- **Package manager is npm.** `package-lock.json` is the lock file. Don't use yarn or pnpm.
- **Node 24 LTS.** Node 25 breaks `yarn install` and the `install:features` script.

## Rules that always apply

1. **Follow the spec.** Screens, labels, statuses, roles and limits come from the PDF. If code and spec disagree, ask. Don't guess.
2. **Follow the module structure** in `docs/ARCHITECTURE.md` (the `universe` layout; `Modules/Merchant` is the reference). Controllers stay thin. Page data comes from `Get…DataAction`s, writes go in `{Verb}{Entity}Action`s, create/edit/confirm screens are InertiaUI modal routes.
3. **Money is integer cents.** Never use float or `decimal` arithmetic for amounts. `App\Support\Money` arrives with the shared foundation.
4. **Every admin write is audited** and checked against a Policy (5 roles: Super Admin, Operations, Finance, Support, Marketing).
5. **Second approver ("maker-checker") rules live in the Action,** not only in the UI. This covers refunds over $100 and all payout batches.
6. **Modules never write another module's tables.** Use events for writes and a `Contracts/*Lookup` class for reads.
7. **TypeScript everywhere.** No `any`. Types for every page prop live in the module's `resources/js/types`.
8. **Reuse before you build.** Check `resources/js/components/common/` and `components/ui/` first.
9. **Never commit secrets.** PayWay keys live in `.env` only. The `.env` file and the spec PDFs are gitignored.
10. **Don't edit generated code:** `resources/js/routes`, `resources/js/actions`, `resources/js/wayfinder`. Edit `components/ui/*` (shadcn) only to fix real bugs.

## Before saying a task is done

Run and pass: `npm run build`, `npx vue-tsc --noEmit`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse`, `php artisan test`. New behaviour needs a Feature test. Say plainly if something wasn't run, or failed.

## Current state (update as it changes)

- **Built:** Dashboard, all 4 tabs (sample data from `app/Services/Dashboard/*`), the brand theme and logo, the login page (login-02 layout with cover image), and the admin sidebar with module badges.
- **Shared UI:** `DamnerLaorTable` (list pages, row "⋯" menu), `DamnerFormModal` + `DamnerFormField` (modal forms), `components/common/*`, charts.
- **Merchant module (done, the reference module):** 03.1 list + 03.2 approval screens, invite, edit, status lifecycle (approve / request changes / reject / suspend / reactivate) as modals, checklist verification, internal notes, sidebar badge, seeder (42 merchants) and 39 tests. Copy its structure for new modules. Audit log and real roles are still TODO (policy = "any admin" for now). Export button and payout bank account are not built yet.
- **Structure follows `universe`:** `routes/dashboard.php` (prefix `/dashboard`), `Dashboard/V1` controllers, actions, requests and pages, `Get…DataAction` reads. Module pages render as `module::Dashboard/V1/…` via `resources/js/lib/resolvePage.ts`.
- **Modals:** InertiaUI Modal v3 (universe's momentum-modal doesn't support Inertia v3). Create/edit/confirm screens are `Inertia::modal(...)->baseRoute(...)` routes using `DamnerFormModal`. `app.blade.php` syncs `SsrState` so direct modal URLs work.
- **Planned module renames:** `User` → `Admin`, `Payments` → `Payment`. Add a new `Setting` module. (`Partners` → `Merchant` is done.)
- **Known blocker:** the 4 empty `Modules/Transport/resources/js/pages/dashboard/Transport/*.vue` files break `npm run build` until they get content.
- **Next:** shared foundation (Money, audit log, roles & permissions), then the Admin and Setting modules, then Transport.
