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

Module names are **singular PascalCase**. Pending renames: `Partners` → `Merchant`, `User` → `Admin`, `Payments` → `Payment`.

---

## 2. Inside a module

Organised **by layer, then by entity**. The example is Transport, whose entities are BusRoute, Trip, Vehicle and SeatLayout.

```
Modules/Transport/
├── app/
│   ├── Models/                 BusRoute, Trip, Vehicle, SeatLayout, Seat
│   ├── Enums/                  TripStatus, RouteStatus, SeatType
│   ├── Actions/                WRITES, one use case per class
│   │   ├── Trip/               CreateTripAction, DelayTripAction, CancelTripAction
│   │   └── SeatLayout/         SaveSeatLayoutAction
│   ├── Queries/                READS: filters, search, paging, stats
│   │   └── Trip/               TripListQuery, TripStatsQuery, TripManifestQuery
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Dashboard/      TripController …                   (admin, Inertia)
│   │   │   └── Api/V1/
│   │   │       ├── Customer/   TripSearchController …             (customer app)
│   │   │       └── Merchant/   BoardingScanController …           (merchant app)
│   │   ├── Requests/Dashboard/Trip/   StoreTripRequest, DelayTripRequest
│   │   └── Resources/
│   │       ├── Dashboard/      TripResource
│   │       └── Api/V1/{Customer,Merchant}/
│   ├── Policies/               TripPolicy
│   ├── Events/                 TripDelayed, TripCancelled
│   ├── Listeners/              SendTripDelaySms (queued)
│   ├── Contracts/              TripLookup        (public read API for other modules)
│   ├── Services/               TripLookupService (implements the contract)
│   └── Providers/
├── database/{migrations,factories,seeders}/
├── routes/
│   ├── dashboard.php           admin pages
│   ├── api/customer/v1.php
│   └── api/merchant/v1.php
├── resources/js/
│   ├── pages/Dashboard/Trip/   Index.vue, Show.vue
│   ├── components/             TransportTabs.vue, SeatMap.vue, ManifestTable.vue
│   ├── types/index.ts
│   └── validations/            zod schemas
└── tests/
    ├── Feature/Dashboard/      TripTest
    └── Unit/Actions/           CancelTripActionTest
```

**Don't create empty folders.** Add `Events/`, `Contracts/` and so on only when something goes in them.

### Responsibilities

| Layer            | Does                                                                                             | Never does                                             |
| ---------------- | ------------------------------------------------------------------------------------------------ | ------------------------------------------------------ |
| Controller       | Authorize, take the validated Request, call one Action or Query, return a page, redirect or JSON | Business logic, queries, `$request->validate()` inline |
| Request          | Validation rules, `authorize()` through the Policy                                               | Database writes                                        |
| Action           | One write use case inside `DB::transaction`, audit log, dispatch events                          | Return HTTP responses, read `request()`                |
| Query            | Filters, search, sort, paginate, stats, eager loading                                            | Writes                                                 |
| Resource         | Shapes a model for Vue or the API (camelCase keys, Money as cents + formatted)                   | Queries (relations must already be loaded)             |
| Enum             | `label()`, `labelKh()`, `tone()`, static `options()`                                             | Database access                                        |
| Policy           | Role and ownership checks                                                                        | Business rules (those go in the Action)                |
| Event / Listener | Tell other modules something happened; slow work runs queued                                     | Change another module's tables directly                |

### One feature end to end ("Delay trip", spec 04.3)

```
POST /transport/trips/{trip}/delay                 name: transport.trips.delay
  → Dashboard\TripController@delay
      → DelayTripRequest      (minutes, reason; TripPolicy::delay)
      → DelayTripAction::execute($trip, $data)
          DB::transaction → update trip → audit log → event TripDelayed
  ← back() with flash "Trip delayed 20 min"
TripDelayed → Booking + Support listeners → queued SMS to passengers
```

### A list page

```php
public function index(IndexTripRequest $request, TripListQuery $list, TripStatsQuery $stats): Response
{
    return Inertia::render('transport::Dashboard/Trip/Index', [
        'trips'    => TripResource::collection($list->paginate($request->filters())),
        'stats'    => $stats->today(),
        'filters'  => $request->filters(),
        'statuses' => TripStatus::options(),
    ]);
}
```

---

## 3. Naming

| Thing               | Pattern                               | Example                                                                               |
| ------------------- | ------------------------------------- | ------------------------------------------------------------------------------------- |
| Model               | singular                              | `Trip`, `BusRoute` (avoid names that clash with Laravel: `Route`, `Request`, `Event`) |
| Table               | plural snake case                     | `seat_layouts`                                                                        |
| Write               | `{Verb}{Entity}Action`                | `CancelTripAction`                                                                    |
| Read                | `{Entity}{Purpose}Query`              | `TripManifestQuery`                                                                   |
| Validation          | `{Verb}{Entity}Request`               | `DelayTripRequest`                                                                    |
| Enum                | `{Entity}{Thing}`                     | `TripStatus`                                                                          |
| Event               | past tense                            | `TripCancelled`                                                                       |
| Route name          | `{module}.{entities}.{action}`        | `transport.trips.delay`                                                               |
| URL                 | `/{module}/{entities}` kebab case     | `/transport/seat-layouts`                                                             |
| Inertia page        | `{module}::Dashboard/{Entity}/{Page}` | `transport::Dashboard/Trip/Show`                                                      |
| Vue component       | PascalCase, entity first              | `TripStatusCard.vue`                                                                  |
| Primary key in URLs | `uuid`                                | `/transport/trips/9f1c…`                                                              |

**Versioning:** only the mobile APIs are versioned (`Api/V1`, `/api/v1/...`). Admin pages ship together with the backend, so there's no `V1` in dashboard folders.

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

- **Shared UI:** `resources/js/components/ui/` (shadcn-vue) and `resources/js/components/common/` (`PageHeader`, `PageTabs`, `SectionCard`, `KpiCard`, `KpiGrid`, `DataTable`, `StatusBadge`, `BarList`, `ActivityFeed`, `ProgressBar`), plus `components/charts/`.
- **Module UI:** `Modules/<Name>/resources/js/{pages,components,types,validations}`.
- **Page resolution:** `module::Path` resolves to `Modules/<Module>/resources/js/pages/<Path>.vue`. Plain names resolve to `resources/js/pages/`.
- **Status display:** each backend enum provides `label` and `tone`. The Vue side never hard-codes labels or colours. Tones are `success` (green), `error` (red), `neutral` (grey, for anything pending; **never orange**) and `brand` (blue, in progress).
- **Brand:** blue `#174A8B`, white `#FFFFFF`, light grey `#F3F6FA`, Inter font, Noto Sans Khmer for Khmer text, booking references in monospace.

---

## 7. Testing

- **Feature test per screen:** a guest is redirected, a role without permission gets 403, the allowed role sees the right Inertia component and props.
- **Unit test per Action** with real business rules: maker-checker, money maths, hold expiry, status transitions.
- **Factories** for every model, with states (`->suspended()`, `->delayed()`).
- **Database:** SQLite in memory (`phpunit.xml`), `RefreshDatabase`.

---

## Lessons from universe

Keep: Actions per use case, Dashboard vs `Api/V1/{Customer,Merchant}` split, rich enums with `labelKh()`, separate route files, Request classes per entity, uuid keys plus soft delete and trash, zod validation, module-local Vue pages.

Don't repeat:

- **Services that duplicate Actions:** pick Actions; use Services only for logic several Actions share, or for contract implementations.
- **Logic in controllers** (inline `validate()` and updates).
- **Empty `Contracts/` or `Repositories/` folders.**
- **Lowercase namespace folders** (`api/customer/v1`): breaks PSR-4 autoloading on Linux.
- **`decimal:2` money:** use integer cents.
- **`V1` on admin pages:** only APIs are versioned.
- **Index actions doing everything:** split into List and Stats Queries.
