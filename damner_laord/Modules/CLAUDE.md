# Module rules (backend)

Applies to everything under `Modules/`. Full reference: [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md).

## Adding an entity to a module

Create these, in this order, and only what the feature needs:

1. Migration (`uuid` column, soft deletes, money columns as `unsignedBigInteger` cents, `restrict` foreign keys)
2. Model + Factory (with useful states) + Enum(s)
3. Policy (map to the role matrix, spec p.43)
4. Request(s): `{Verb}{Entity}Request`
5. Action(s) for writes: `{Verb}{Entity}Action`, inside `DB::transaction`, audited
6. Query(ies) for reads: `{Entity}{Purpose}Query`
7. Resource(s): `Dashboard/` and/or `Api/V1/{Customer,Merchant}/`
8. Thin controller: `Http/Controllers/Dashboard/{Entity}Controller`
9. Routes in `routes/dashboard.php` (`{module}.{entities}.{action}`)
10. Vue page(s) in `resources/js/pages/Dashboard/{Entity}/`, types in `resources/js/types/`
11. Tests: Feature (HTTP + roles) and Unit (Action rules)

## Must

- **Controllers:** authorize, validate (through the Request), call one Action or Query, respond. Nothing else.
- **Actions:** `public function execute(...)`, typed arguments and return type, no `request()` or `auth()` inside. Pass the acting admin in explicitly.
- **Queries:** eager-load what the Resource uses. Don't let a Resource trigger extra queries.
- **Enums:** string-backed, with `label()`, `labelKh()`, `tone()` (`success|error|neutral|brand`) and `options()`.
- **Money:** `unsignedBigInteger` cents in the database, `Money` in PHP, `{ cents, formatted }` in Resources.
- **Events:** past-tense names. Listeners that send SMS, push or email, or call PayWay, are queued.
- **Other modules:** read through their `Contracts/*Lookup`; change them through events. Never `use Modules\Other\Models\...` for queries.

## Must not

- No `V1` in Dashboard folders (only `Api/V1`).
- No lowercase namespace folders.
- No Services that copy an Action.
- No empty scaffold folders, Blade views or per-module `vite.config.js` / `package.json` (the root Vite build handles module pages).
- No `float` or `decimal` money maths.
- No `onDelete('cascade')` across modules.

## Checklist before done

`vendor/bin/pint --test` · `vendor/bin/phpstan analyse` · `php artisan test --filter=<Module>` · `npm run build` · `npx vue-tsc --noEmit`

When a module gets its first real code, add `Modules/<Name>/app/` and `Modules/<Name>/database/` to `paths` in `phpstan.neon`, otherwise PHPStan never checks it. Module tests run automatically (`phpunit.xml` → `Modules` suite).
