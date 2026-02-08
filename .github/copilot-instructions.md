# Copilot / Agent instructions — Inspektorat App

Overview
- Laravel 11 PHP 8.2 application with Blade front-end, Yajra DataTables (server-side), and Vite/Tailwind for assets.
- Core app logic is service-driven (app/Services) and models use custom primary keys (e.g., `id_user`, `id_petugas`).

Quick setup (Windows / Laragon)
- Copy `.env.example` -> `.env`, configure DB connection.
- composer install && npm install
- Run migrations: `php artisan migrate` (or `composer run post-create-project-cmd` on fresh project)
- Dev: `composer run dev` (runs `php artisan serve`, queue listener, pail, and `npm run dev`) or individually: `php artisan serve` + `npm run dev`.
- Tests: `php artisan test` or `vendor/bin/phpunit`.
- Code style: `vendor/bin/pint` (Laravel Pint) for formatting.

Key conventions & patterns (be concrete)
- Controllers follow this pattern:
  - index/create/edit return Blade views under `resources/views/administration/...`.
  - list endpoints return DataTables JSON (e.g., `PetugasDesaController::list()` uses `Petugas::getFilters()` and `Yajra DataTables`).
  - store/update use `TransactionService` to handle validation, file uploads and DB transactions.
    - Example: app/Services/TransactionService::store(Request, Model, validationRules, callable $customLogic, fileFields, oldFiles)
    - Custom logic callback is the place to attach side-effects (e.g., create user account via `UserAccountService`).
- Use `ResponseService` (app/Services/ResponseService.php) for structured JSON responses (`success`, `validationError`, `error`) and constants for status codes.
- File uploads use `FileUploadService` and are stored under `public/uploads/<dir>`; controllers pass a file-fields map like `['foto_petugas' => 'petugas/foto']`.
- Log actions via `LogActivityService::log(...)` for audit trail (many controllers call it before/after operations).
- Models commonly define `$primaryKey` (e.g., `id_kecamatan`, `id_user`) and use attribute casting; watch for non-default keys when querying or writing tests.
- Status fields use literal values `'active'` / `'inactive'` throughout.

Database / Models
- Many models include helper methods returning Collections (e.g., `Petugas::getFilters()` returns a `Collection`, not a Builder). DataTables is used with both Builders and Collections — check the model implementation.
- When adding new model relations, follow existing `->leftJoin()` and `->select()` conventions used in `getRelationship()` helpers.

Routing & RBAC
- Routes are grouped under `routes/web.php` with prefixes and name groups (e.g., `administrator.master.petugas.desa.*`). Mirror those conventions when adding endpoints.
- RBAC is implemented with `Role`, `Permission`, `UserRole` tables; user role assignment is handled in `UserAccountService`.

Tests & Debugging
- Unit/feature tests live in `tests/Unit` and `tests/Feature`. Use `php artisan test`.
- Use `php artisan tinker` and query helper methods (e.g., `Petugas::getFilters([...])`) when reproducing issues.

Common pitfalls / tips
- Be mindful of non-standard primary keys (`id_user`, `id_petugas`): `find($id)` still works, but adding migrations/test fixtures must use correct column names.
- Use existing services for operations rather than re-implementing logic: `TransactionService`, `FileUploadService`, `UserAccountService`, `LogActivityService`, `ResponseService`.
- File uploads are moved directly to `public/uploads`; ensure `public/uploads` is writeable in dev/prod.
- When adding a new master resource, follow the naming and route grouping conventions in `routes/web.php` and reuse DataTables for list endpoints.

If anything is unclear or you'd like more examples (e.g., a template controller or test), tell me which area and I will add a short code snippet or PR-ready template. ✅
