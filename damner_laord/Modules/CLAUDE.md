# Module rules (backend)

Applies to everything under `Modules/`. Full reference: [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md). Layout follows the `universe` project; **`Modules/Merchant` is the reference module, so copy it.**

## Adding an entity to a module

Create these, in this order, and only what the feature needs:

1. Migration (`uuid` column, soft deletes, money columns as `unsignedBigInteger` cents, `restrict` foreign keys)
2. Model + Factory (with useful states) + Enum(s)
3. Policy (map to the role matrix, spec p.43), registered with `Gate::policy` in the module ServiceProvider
4. Request(s): `Http/Requests/Dashboard/V1/{Entity}Request/{Verb}{Entity}Request`
5. Read Actions: `Actions/Dashboard/V1/{Entity}Action/Get{Entity}{Index,Show,Edit,Create}DataAction`
6. Write Actions: `Actions/Dashboard/V1/{Entity}Action/{Verb}{Entity}Action`, inside `DB::transaction`, audited
7. Resource(s): `Http/Resources/` (admin) and `Http/Resources/Api/{Customer,Merchant}/V1/` (apps)
8. Thin controller: `Http/Controllers/Dashboard/V1/{Entity}Controller`
9. Routes in `routes/dashboard.php` (prefix `/dashboard`, names `{module}.{entities}.{action}`)
10. Vue pages in `resources/js/pages/Dashboard/V1/{Entity}/`, types in `resources/js/types/`
11. Tests: Feature (HTTP, roles, modals) and Unit (Action rules)

## Modals (InertiaUI Modal)

- Create / edit / confirm screens: `GET` route → `Inertia::modal('{module}::Dashboard/V1/{Entity}/{Page}', $data)->baseRoute(...)`; `POST`/`PATCH` route does the write and returns `back()` with `Inertia::flash('toast', …)`.
- One confirm page + one `confirm` action for a family of status changes (see `MerchantReviewController@confirm` + `Review.vue`).
- Test a modal route by asserting the base page component and `props._inertiaui_modal.component` (see `MerchantModalTest`).

## Must

- **Controllers:** authorize, validate (through the Request), call one Action, respond. Nothing else.
- **Actions:** `public function execute(...)`, typed arguments and return type, no `request()` or `auth()` inside. Pass the acting admin in explicitly.
- **Read Actions:** eager-load what the Resource uses. Don't let a Resource trigger extra queries. Return every filter key (null when unused) so Vue gets an object, not `[]`.
- **Enums:** string-backed, with `label()`, `labelKh()`, `tone()` (`success|error|neutral|brand`) and `options()` (`App\Concerns\EnumOptions`).
- **Money:** `unsignedBigInteger` cents in the database, `Money` in PHP, `{ cents, formatted }` in Resources.
- **Events:** past-tense names, `ShouldDispatchAfterCommit`. Listeners that send SMS, push or email, or call PayWay, are queued.
- **Other modules:** read through their `Contracts/*Lookup`; change them through events. Never `use Modules\Other\Models\...` for queries.

## Must not

- No lowercase namespace folders.
- No Services that copy an Action.
- No empty scaffold folders, Blade views or per-module `vite.config.js` / `package.json` (the root Vite build handles module pages).
- No empty `.vue` page files: the page resolver loads every module page, so an empty one breaks `npm run build`.
- No `float` or `decimal` money maths.
- No `onDelete('cascade')` across modules.

## Checklist before done

`vendor/bin/pint --test` · `vendor/bin/phpstan analyse` · `php artisan test --filter=<Module>` · `npm run build` · `npx vue-tsc --noEmit`

When a module gets its first real code, add `Modules/<Name>/app/` and `Modules/<Name>/database/` to `paths` in `phpstan.neon`, otherwise PHPStan never checks it. Module tests run automatically (`phpunit.xml` → `Modules` suite).
