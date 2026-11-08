# Multi-Vendor Marketplace — Complete Codebase Audit Report (V1.0)
**Date:** 2026-11-07  
**Target Application:** Multi-Vendor Retail & Wholesale Marketplace (`online_shop`)  
**Repository:** https://github.com/Ibrohim-2307/online_shop  
**Target Production:** https://online-shop-l3td.onrender.com  
**Environment Baseline:** PHP 8.4, Laravel 13, SQLite / MySQL, Docker, Nginx, Flutter Apps  

---

## 1. Executive Summary

This audit evaluated all layers of the marketplace platform: Web storefront & admin/seller panels, REST API (`/api/v1`), Database integrity & migrations, Flutter Customer & Courier mobile apps, Background processing/Supervisord, and Docker/Render production infrastructure.

The core commerce logic (multi-vendor cart, suborders, atomic inventory reservations, price tiers, and payment adapters) is well structured, with 142 automated feature tests already in place. However, critical vulnerabilities and operational risks were identified that require systematic remediation:
1. **P0 (Security & Data Isolation):** Cross-tenant data fallback in `SellerDashboardController.php` using `Seller::first()`; unrestricted admin role promotion in `AdminDashboardController.php` where standard admins can promote users to `superadmin` or demote the last superadmin; unauthenticated fallback re-activation of demo accounts.
2. **P0 (Infrastructure & Data Loss Risk):** Uploaded product images saved to ephemeral `public/images/products` instead of persistent storage mount (`/var/www/html/storage/data`), risking image loss on Render container redeployments; missing Queue Worker and Scheduler in `docker/supervisord.conf`, preventing automated release of expired inventory reservations and asynchronous background jobs.
3. **P1 (Authentication & Multi-Factor Consistency):** Admin MFA enforced only in API login (`Api/V1/AuthController.php`) but bypassed in Web login (`Web/AuthController.php`).
4. **P1 (Warehouse & Product Moderation Authorization):** Missing warehouse ownership validation when sellers supply custom `warehouse_id`; suspended sellers still able to access operations in some endpoints.
5. **P2 (Logistics & Delivery Calculation):** Configurable shipping rates, multi-region zone rules, and delivery estimation rules need standardization between web checkout and mobile API.

---

## 2. Classified Audit Findings

### [P0 - CRITICAL] Issue 1: Cross-Tenant Data Fallback in Seller Dashboard
- **Relevant Files:** `backend/app/Http/Controllers/Web/SellerDashboardController.php` (lines 38-41, 123)
- **Root Cause:**
  ```php
  $sellerId = $user->seller_id;
  $seller = $sellerId ? Seller::find($sellerId) : null;
  if (!$seller) {
      $seller = Seller::first(); // Critical Cross-Tenant Fallback!
  }
  ```
  And in `storeProduct`:
  ```php
  $seller = Seller::find($user->seller_id) ?: Seller::first();
  ```
- **Affected Users:** All sellers and marketplace vendors.
- **Business Impact:** Any authenticated user with a seller staff role whose store is unassigned or deleted is silently granted full viewing and modification access to the first store in the database (`Seller::first()`), exposing sales, ledger, balance, and customer suborders.
- **Reproduction Steps:** Log in with a user possessing `seller_operator` role but having `seller_id = null`. Access `/seller`. The user sees Seller #1's proprietary financials and inventory.
- **Recommended Fix:** Remove `Seller::first()` fallback completely. Use verified membership `$user->activeSeller() ?? ($user->seller_id ? Seller::find($user->seller_id) : null)`. If null, abort with 403 or redirect to an unassigned store page.
- **Risk:** Low risk; strictly secures tenant boundaries.
- **Required Tests:** Feature test confirming unassigned seller member gets 403 / redirect and cannot access Seller #1.

---

### [P0 - CRITICAL] Issue 2: Ephemeral Product Image Uploads on Container Filesystem
- **Relevant Files:**
  - `backend/app/Http/Controllers/Web/SellerDashboardController.php` (lines 145-152)
  - `backend/app/Http/Controllers/Web/ProfileController.php` (lines 460-470)
  - `render.yaml`
  - `docker/entrypoint.sh`
- **Root Cause:**
  Both controllers write user-uploaded product images directly into `public_path('images/products')`. In `render.yaml`, only `/var/www/html/storage/data` is mounted to the persistent disk `marketplace-data`. The container directory `/var/www/html/public` is ephemeral.
- **Affected Users:** Sellers and customers.
- **Business Impact:** Whenever Render rebuilds or restarts the container (e.g. after git push or maintenance), newly uploaded product images vanish, causing 404 broken images in the live storefront.
- **Reproduction Steps:** Upload a product image on Render, trigger a service redeployment. Image path returns 404.
- **Recommended Fix:** Store uploaded images inside `/var/www/html/storage/data/uploads` or configure Laravel's public disk with a symlink from `/var/www/html/public/uploads` -> `/var/www/html/storage/data/uploads`. Ensure `entrypoint.sh` initializes and links this persistent storage directory on boot.
- **Risk:** Low. Existing seeded static images in `/images/products/` (e.g. `sement.jpg`, `quvur.jpg`) remain untouched.

---

### [P0 - CRITICAL] Issue 3: Missing Background Queue Worker & Scheduler in Supervisord
- **Relevant Files:** `docker/supervisord.conf`, `backend/routes/console.php`
- **Root Cause:**
  `supervisord.conf` only spawns `php-fpm` and `nginx`. No `php artisan queue:work` or `schedule:work` is defined. The command `inventory:release-expired` is not registered in Laravel's scheduler.
- **Affected Users:** Customers purchasing scarce inventory, sellers, operations.
- **Business Impact:**
  1. Abandoned/unpaid orders hold stock reservations indefinitely, preventing other genuine buyers from ordering.
  2. Queued events, transactional outbox items, and background notifications never execute.
- **Recommended Fix:**
  1. Add `[program:laravel-worker]` (`php artisan queue:work --sleep=3 --tries=3 --max-time=3600`) to `docker/supervisord.conf`.
  2. Schedule `inventory:release-expired` every 5-10 minutes in `backend/routes/console.php`.
  3. Add lightweight background scheduler runner or cron process in Supervisor.
- **Risk:** Low; ensures asynchronous tasks run smoothly.

---

### [P0 - CRITICAL] Issue 4: Administrator Privilege Escalation & Superadmin Demotion
- **Relevant Files:** `backend/app/Http/Controllers/Web/AdminDashboardController.php` (lines 177-225)
- **Root Cause:**
  Any user with `admin` role can call `updateUserRole` and assign the `superadmin` role to anyone, or demote an existing `superadmin` down to `customer`. Furthermore, the last active superadmin can be accidentally demoted or removed.
- **Affected Users:** Superadministrators and marketplace operators.
- **Business Impact:** Compromised or rogue standard admin can seize complete ownership of the marketplace or lock out legitimate owners.
- **Recommended Fix:**
  1. Only users with the `superadmin` role may assign or modify `admin` or `superadmin` roles.
  2. Guard against removing the last active `superadmin`.
  3. Standard `admin` users can only manage customer, seller, and courier roles.
- **Risk:** Low; adheres to standard RBAC hierarchy.

---

### [P1 - HIGH] Issue 5: Missing MFA Verification on Web Admin Login
- **Relevant Files:** `backend/app/Http/Controllers/Web/AuthController.php` (lines 75-77) vs `backend/app/Http/Controllers/Api/V1/AuthController.php` (lines 138-158)
- **Root Cause:**
  The API login requires multi-factor authentication (`admin_mfa` challenge token + OTP) for accounts with administrative privileges. Web login (`/login`) allows immediate direct session establishment for admins without MFA.
- **Affected Users:** Marketplace administrators and system security.
- **Business Impact:** An attacker obtaining admin credentials can bypass MFA simply by logging in through the web browser interface instead of the API.
- **Recommended Fix:** Unify MFA protection for administrative roles (`superadmin`, `admin`) across web sessions. Redirect privileged accounts to a secure web MFA verification step (`/auth/mfa/challenge`) before granting administrative access.
- **Risk:** Medium; must ensure test accounts in testing/demo mode are properly accommodated.

---

### [P1 - HIGH] Issue 6: Warehouse Ownership Validation in Seller Catalog Management
- **Relevant Files:**
  - `backend/app/Http/Controllers/Web/SellerDashboardController.php` (lines 128-135)
  - `backend/app/Http/Controllers/Web/ProfileController.php` (lines 296-302)
- **Root Cause:**
  When a seller specifies a `warehouse_id`, the system did not strictly verify that `Warehouse::where('id', $warehouseId)->where('seller_id', $seller->id)->exists()`.
- **Affected Users:** Sellers.
- **Business Impact:** Malicious seller could inject inventory balances into another seller's warehouse record.
- **Recommended Fix:** Explicitly scope warehouse resolution: `Warehouse::where('seller_id', $seller->id)->findOrFail($warehouseId)`.

---

### [P2 - MEDIUM] Issue 7: Standardization of Wholesale Trade Modes (`trade_mode`)
- **Relevant Files:**
  - `backend/database/migrations/2026_10_19_180100_create_products_table.php` (`enum('wholesale', 'retail', 'both')`)
  - `backend/app/Http/Controllers/Web/ProfileController.php` (`in:wholesale_only,retail_only,both`)
  - `backend/resources/views/storefront/catalog.blade.php`
- **Root Cause:**
  Different forms used `wholesale` vs `wholesale_only`, or `retail` vs `retail_only`, causing intermittent validation drops or mismatched filtering in catalog search.
- **Recommended Fix:** Canonicalize enum values throughout migrations, validation rules, and Blade dropdowns to: `'wholesale', 'retail', 'both'`.

---

### [P2 - MEDIUM] Issue 8: Shipping Tariff and Zone Rules
- **Relevant Files:** `backend/app/Services/Checkout/CheckoutService.php`, `backend/resources/views/storefront/checkout.blade.php`
- **Root Cause:**
  Shipping is currently calculated with a default flat rate per seller suborder (e.g. 15 000 UZS).
- **Recommended Fix:**
  Provide zone-based shipping calculations (Tashkent City vs Regions vs Remote areas) with free-shipping thresholds (e.g., orders above 500 000 UZS get free standard shipping).

---

## 3. Technology Stack & Verification Matrix

| Component | Current Implementation | Audit Finding | Status |
| :--- | :--- | :--- | :--- |
| **PHP / Laravel** | PHP 8.4, Laravel 13.37.0 | Clean, standard, modern service pattern | **Approved** |
| **Authentication** | Session (Web) + Sanctum (API) | Safe password hashing; MFA needed on Web admin | **Remediating** |
| **Cart & Multi-Vendor** | Suborder separation per seller | Resilient; stock calculated correctly per variant | **Approved** |
| **Installment (Nasiya)** | 3, 6, 12 months calculator | Fully interactive on product & checkout; committed stock | **Approved** |
| **Payments** | Click, Payme, Nasiya, COD | Idempotent callbacks, signature checks in place | **Approved** |
| **File Storage** | Direct public directory writes | Needs persistent volume symlink on Render | **Remediating** |
| **Supervisord / Workers** | Web & FPM only | Missing Queue Worker & Scheduler | **Remediating** |
| **Database Migrations** | 100% Non-destructive | No `db:wipe`, preserved legacy data | **Approved** |
| **Automated Tests** | 142 Feature/Unit Tests | 942 assertions passing (100% green) | **Approved** |

---

## 4. Phase-by-Phase Remediation Roadmap

1. **Phase 1 (Security Hardening):**
   - Eliminate `Seller::first()` fallback in `SellerDashboardController.php`.
   - Restrict role modification in `AdminDashboardController.php` to superadmin only; protect superadmin role from demotion.
   - Enforce warehouse ownership checks.
   - Unify demo credentials fallback strictly under `APP_DEMO_MODE=true` or local environment.
2. **Phase 2 (Infrastructure & Storage Persistence):**
   - Update `docker/entrypoint.sh` and storage configuration to link `/var/www/html/storage/data/uploads` into public access.
   - Add queue worker and schedule runner to `docker/supervisord.conf`.
   - Register `inventory:release-expired` in `backend/routes/console.php`.
3. **Phase 3 (Core Commerce & Shipping Rules):**
   - Add dynamic region/zone delivery fee rules and free-shipping thresholds in `CheckoutService.php`.
   - Align `trade_mode` across all catalog queries and product creation forms.
4. **Phase 4 (Four-Panel Experience & Mobile Integration):**
   - Maintain full compatibility with Flutter Customer & Courier apps.
   - Enhance mobile responsiveness across all 4 panels.
5. **Phase 5 (Documentation & Production Verification):**
   - Maintain all required documentation: `ARCHITECTURE.md`, `SECURITY_AUDIT.md`, `DATABASE_SCHEMA.md`, `DEVELOPMENT_PLAN.md`, `CHANGELOG.md`.
