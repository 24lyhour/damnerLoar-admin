# DamnerLaor architecture

One Laravel app split into **domain modules** (nwidart/laravel-modules). Each module owns its tables, its business rules, its admin pages and its mobile API. Shared building blocks live outside the modules.

The structure is adapted from the `universe` project (`Modules/Hotel`), with fixes listed in [Lessons from universe](#lessons-from-universe).

---

## 1. Modules

| #   | Module        | Owns (spec screens)                                                               | Reads from                 | Sprint |
| --- | ------------- | --------------------------------------------------------------------------------- | -------------------------- | ------ |
| 1   | **Admin**     | Admin users, roles & permissions, audit log (11.2–11.4)                           | none                       | 1      |
| 2   | **Setting**   | Platform rules: holds, fees, commission, cancellation tiers, refund limits (11.1) | Admin                      | 1      |
| 3   | **Merchant**  | Merchant list, approval checklist, contract, payout bank (03)                     | Admin                      | 2      |
| 4   | **Transport** | Routes, trips, vehicles, seat layouts, manifest (04)                              | Merchant                   | 2      |
| 5   | **Customer**  | App users, saved travellers, flags (07)                                           | none                       | 3      |
| 6   | **Booking**   | Bookings, booking parts, round trips, holds, tickets (06)                         | Transport, Hotel, Customer | 3      |
| 7   | **Payment**   | Money in: ABA PayWay, payment methods, webhooks, reconciliation (09.1–09.2)       | Booking                    | 3      |
| 8   | **Finance**   | Money out: refunds (maker-checker), payouts, statements, reports (09.3–09.6)      | Payment, Merchant, Booking | 3–5    |
| 9   | **Hotel**     | Hotels, room types, photos, availability & prices (05)                            | Merchant                   | 4      |
| 10  | **Support**   | Tickets, chat thread, internal notes (08)                                         | Booking, Customer          | 4      |
| 11  | **Marketing** | Promo codes, push notifications (10)                                              | Customer, Booking          | 5      |

**Dashboard is not a module.** It lives in `app/` (`DashboardController`, `app/Services/Dashboard/*`) and only reads other modules' data through their Lookup contracts.

Module names are **singular PascalCase**. Pending renames: `User` → `Admin`, `Payments` → `Payment` (`Partners` → `Merchant` is done).

---

## 2. Inside a module (universe layout)

Same structure as the `universe` project (`Modules/Hotel`): **Dashboard/V1** for admin code, **Api/V1/{Customer,Merchant}** for the mobile apps, one Action class per use case. The reference module is **Merchant**; copy it.

```
Modules/Merchant/
├── app/
│   ├── Models/                         Merchant, MerchantDocument, MerchantNote
│   ├── Enums/                          MerchantStatus, MerchantType, MerchantDocument{Type,Status}
│   ├── Actions/Dashboard/V1/
│   │   ├── MerchantAction/             READS:  GetMerchant{Index,Show,Edit,Create}DataAction
│   │   │                               WRITES: InviteMerchantAction, UpdateMerchantAction,
│   │   │                                       Approve/Reject/Suspend/Reactivate…, TransitionMerchantStatusAction
│   │   ├── MerchantDocumentAction/     VerifyMerchantDocumentAction
│   │   └── MerchantNoteAction/         AddMerchantNoteAction
│   ├── Http/
│   │   ├── Controllers/Dashboard/V1/   MerchantController, MerchantReviewController, …   (thin)
│   │   ├── Controllers/Api/V1/{Customer,Merchant}/                                         (mobile apps)
│   │   ├── Requests/Dashboard/V1/MerchantRequest/   Index/Invite/Update/…Request
│   │   └── Resources/                  MerchantListResource, MerchantDetailResource, …
│   ├── Policies/  Events/  Listeners/  Notifications/  Contracts/  Providers/
├── database/{migrations,factories,seeders}/
├── routes/
│   ├── dashboard.php                   admin pages + modals, prefix /dashboard (RouteServiceProvider::mapDashboardRoutes)
│   ├── web.php                         public web routes (usually empty)
│   └── api.php → api/customer/v1.php, api/merchant/v1.php
├── resources/js/
│   ├── pages/Dashboard/V1/Merchant/    Index, Show (pages) · Invite, Edit, Review (modals)
│   ├── components/Dashboards/v1/       MerchantForm (fields), MerchantDashboardComponent/*
│   ├── types/index.ts                  props of every page, mirrors the Resources
│   └── validations/                    zod schemas (when a form needs client checks)
└── tests/Feature/                      one file per screen/flow
```

**Don't create empty folders.** Add `Events/`, `Contracts/` and so on only when something goes in them.

### Modals (InertiaUI Modal, universe's momentum-modal for Inertia v3)

- **Create / edit / confirm screens are server routes rendered as modals:** `GET` returns `Inertia::modal('merchant::Dashboard/V1/Merchant/Edit', $data)->baseRoute('merchant.merchants.show', $merchant)`, and the matching `POST`/`PATCH` does the work and returns `back()` with a flash toast.
- **Opening:** `<ModalLink :href="edit({ merchant }).url">` for buttons, or `RowAction { href, modal: true }` in `DamnerLaorRowActions`. A direct visit to the URL shows the base page with the modal on top.
- **The page component** wraps its fields in `DamnerFormModal` (universe's `ModalForm`): right-hand slideover, persistent (only X / Cancel / save closes it), Inertia `<Form>` posting to a Wayfinder `.form()` route.
- **Status confirmations** use one page (`Review.vue`) and one controller action (`confirm`) for every status change, like universe's `confirmDelete` + `Delete.vue`.

### Responsibilities

| Layer            | Does                                                                                     | Never does                                             |
| ---------------- | ---------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Controller       | Authorize, take the validated Request, call one Action, return a page / modal / redirect | Business logic, queries, `$request->validate()` inline |
| Request          | Validation rules, `authorize()` through the Policy                                       | Database writes                                        |
| `Get…DataAction` | Build a page's props: filters, search, sort, paginate, stats, eager loading              | Writes                                                 |
| Write Action     | One write use case inside `DB::transaction`, audit log, dispatch events                  | Return HTTP responses, read `request()`                |
| Resource         | Shapes a model for Vue or the API (camelCase keys, Money as cents + formatted)           | Queries (relations must already be loaded)             |
| Enum             | `label()`, `labelKh()`, `tone()`, static `options()`                                     | Database access                                        |
| Policy           | Role and ownership checks                                                                | Business rules (those go in the Action)                |
| Event / Listener | Tell other modules something happened; slow work runs queued                             | Change another module's tables directly                |

### One feature end to end ("Suspend merchant")

```
GET  /dashboard/merchants/{uuid}/suspend      merchant.merchants.confirm-suspend
  → MerchantReviewController@confirm → Inertia::modal('merchant::Dashboard/V1/Merchant/Review') (slideover)
POST /dashboard/merchants/{uuid}/suspend      merchant.merchants.suspend
  → MerchantReasonRequest (reason; MerchantPolicy::suspend)
  → SuspendMerchantAction → TransitionMerchantStatusAction
      DB::transaction → status = suspended → event MerchantStatusChanged (after commit)
  ← back() + flash toast; the modal closes
```

---

## 3. Naming

| Thing               | Pattern                                  | Example                                                                     |
| ------------------- | ---------------------------------------- | --------------------------------------------------------------------------- |
| Model               | singular                                 | `Merchant`, `BusRoute` (avoid Laravel clashes: `Route`, `Request`, `Event`) |
| Table               | plural snake case                        | `merchant_documents`                                                        |
| Read (page props)   | `Get{Entity}{Page}DataAction`            | `GetMerchantIndexDataAction`                                                |
| Write               | `{Verb}{Entity}Action`                   | `SuspendMerchantAction`                                                     |
| Validation          | `{Verb}{Entity}Request`                  | `InviteMerchantRequest`                                                     |
| Enum                | `{Entity}{Thing}`                        | `MerchantStatus`                                                            |
| Event               | past tense                               | `MerchantStatusChanged`                                                     |
| Route name          | `{module}.{entities}.{action}`           | `merchant.merchants.suspend`, modal: `merchant.merchants.confirm-suspend`   |
| URL                 | `/dashboard/{entities}` kebab case       | `/dashboard/merchants/{uuid}/edit`                                          |
| Inertia page        | `{module}::Dashboard/V1/{Entity}/{Page}` | `merchant::Dashboard/V1/Merchant/Show`                                      |
| Vue component       | PascalCase, entity first                 | `MerchantChecklistCard.vue`                                                 |
| Primary key in URLs | `uuid`                                   | `/dashboard/merchants/9f1c…`                                                |

**Watch the `/dashboard` prefix:** the Dashboard's own tabs use `/dashboard/bookings`, `/dashboard/finance` and `/dashboard/trips`. When building the Booking or Finance module, give their lists a different path (for example `/dashboard/booking-list`) or move the Dashboard tabs, so routes don't collide.

---

## 4. Rules between modules

1. **No module writes another module's tables.** To change something elsewhere, dispatch an event and let that module's listener call its own Action.
2. **Cross-module reads go through a contract,** for example `Transport\Contracts\TripLookup`, bound in the module's ServiceProvider. Don't import another module's Models for queries.
3. **Foreign keys across modules are allowed** (`bookings.trip_id`), but `onDelete` is always `restrict`. Use soft deletes and archiving instead.
4. **Dependencies flow one way,** following the "Reads from" column in the module table. No cycles: if two modules need each other, one of them talks through events.

---

## 5. Shared foundation (`app/`)

| Piece               | Where                                                           | Purpose                                                                            |
| ------------------- | --------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| `Money`             | `app/Support/Money.php`                                         | Integer cents, `add`, `subtract`, `percent`, `format()`; Eloquent cast `MoneyCast` |
| Audit log           | `Modules/Admin` + `App\Concerns\Auditable`                      | Every admin write: who, what, before → after, IP. Read-only, kept 2 years          |
| Roles & permissions | `spatie/laravel-permission`, seeded from the spec matrix (p.43) | Full / Edit / View / none per area                                                 |
| Merchant scope      | `App\Concerns\BelongsToMerchant`                                | The merchant app API only sees its own merchant's rows                             |
| `HasUuid`           | `App\Concerns\HasUuid`                                          | uuid route keys                                                                    |
| Enum helpers        | `App\Concerns\EnumOptions`                                      | Shared `options()` / `values()`                                                    |
| Dashboard           | `app/Services/Dashboard/*`                                      | Aggregates across modules (read only)                                              |

### Hard business rules (from the spec)

- **Money:** integer cents; commission is 12% for bus and 15% for hotels unless a merchant override applies; a $1 service fee per booking.
- **Holds:** 10 min for a bus seat, 15 min for a hotel room, 5 min for KHQR expiry. Booking closes 30 min before departure. Seats are protected by a unique constraint on (trip, seat) plus row locking.
- **Refunds:** policy refunds of $100 or less are approved automatically. Manual refunds, and anything over $100, need a second approver who is not the first approver.
- **Payouts:** weekly on Tuesday; every batch needs a second approver; suspended merchants are put on hold.
- **PayWay webhooks:** check the signature; process each transaction once (keyed on the PayWay transaction id); handle them in a queued job.
- **Security:** 2FA for every admin, 30 min idle timeout, re-authentication before money actions, audit log on every write.

---

## 6. Frontend

- **Shared UI:**
    - `resources/js/components/ui/`: shadcn-vue primitives.
    - `resources/js/components/common/`: `PageHeader`, `PageTabs`, `SectionCard`, `KpiCard`, `KpiGrid`, `DataTable` (small card tables), `StatusBadge`, `BarList`, `ActivityFeed`, `ProgressBar`.
    - `resources/js/components/DamnerLaorTable/`: list pages. `DamnerLaorTable`, `DamnerLaorStatusTabs`, `DamnerLaorTableToolbar`, `DamnerLaorTableSelectFilter`, `DamnerLaorRowActions` ("⋯" menu), `useTableQuery()` (search / filters / sort / paging in the URL, server-side).
    - `resources/js/components/DamnerFormModal/`: `DamnerFormModal` (InertiaUI modal + Inertia `<Form>`, right slideover, persistent) and `DamnerFormField`.
    - `resources/js/components/charts/`: `LineChart`, `BarChart`, `DoughnutChart`.
- **Module UI:** `Modules/<Name>/resources/js/{pages/Dashboard/V1,components/Dashboards/v1,types,validations}`.
- **Page resolution:** `merchant::Dashboard/V1/Merchant/Index` resolves to `Modules/Merchant/resources/js/pages/Dashboard/V1/Merchant/Index.vue` (`lib/resolvePage.ts` on the client, `App\Support\InertiaPage` for `@vite`). Plain names resolve to `resources/js/pages/`.
- **Modals:** InertiaUI Modal v3 is set up once in `app.ts` (`renderApp` + `putConfig`: slideover from the right, persistent). `app.blade.php` syncs Inertia's `SsrState` so a modal URL opened directly shows its base page with the modal on top.
- **Imports:** module files import shared code with `@/…` and module code with relative paths. The type-check can't detect a broken `.vue` path (`vue-shims.d.ts` accepts any), so check imports after moving files.
- **Status display:** each backend enum provides `label` and `tone`. The Vue side never hard-codes labels or colours. Tones are `success` (green), `error` (red), `neutral` (grey, for anything pending; **never orange**) and `brand` (blue, in progress).
- **Brand:** blue `#174A8B`, white `#FFFFFF`, light grey `#F3F6FA`, Inter font, Noto Sans Khmer for Khmer text, booking references in monospace.

---

## 7. Testing

- **Feature test per screen:** a guest is redirected, a role without permission gets 403, the allowed role sees the right Inertia component and props.
- **Modal routes:** assert the base page component and `props._inertiaui_modal.component` / `.props` (see `Modules/Merchant/tests/Feature/MerchantModalTest.php`), plus the 403 for users who may not open it.
- **Unit test per Action** with real business rules: maker-checker, money maths, hold expiry, status transitions.
- **Factories** for every model, with states (`->suspended()`, `->delayed()`).
- **Database:** SQLite in memory (`phpunit.xml`), `RefreshDatabase`. The base `TestCase` calls `withoutVite()`, so tests don't depend on a build.
- **Where tests live:** app tests in `tests/`, module tests in `Modules/<Name>/tests/` (the `Modules` suite in `phpunit.xml` runs them). Frontend helpers: Vitest, `npm test`.

---

## Lessons from universe

Keep: Actions per use case (including `Get…DataAction` reads), `Dashboard/V1` vs `Api/V1/{Customer,Merchant}` split, server-rendered modals, rich enums with `labelKh()`, separate route files, Request classes per entity, uuid keys plus soft delete and trash, zod validation, module-local Vue pages.

Don't repeat:

- **Services that duplicate Actions:** pick Actions; use Services only for logic several Actions share, or for contract implementations.
- **Logic in controllers** (inline `validate()` and updates).
- **Empty `Contracts/` or `Repositories/` folders.**
- **Lowercase namespace folders** (`api/customer/v1`): breaks PSR-4 autoloading on Linux.
- **`decimal:2` money:** use integer cents.
- **momentum-modal:** it only supports Inertia v1/v2. This project runs Inertia v3, so it uses **InertiaUI Modal** (same `Inertia::modal()->baseRoute()` idea).
