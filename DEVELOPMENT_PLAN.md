# Multi-Vendor Marketplace — Development Plan (V1.0)

## Overview & Vision
This plan guides the systematic transformation of `online_shop` into a production-grade multi-vendor e-commerce platform for Uzbekistan. It is structured into clearly defined phases prioritizing data security, transactional integrity, operational scalability, and UX.

---

## Phase Matrix & Milestones

### Phase 0: Baseline & Safe Checkpoint (COMPLETED)
- Verified working tree and test suite baseline (142 tests passing).
- Created safe recovery point git tag `safety-checkpoint-master-prompt`.
- Executed comprehensive audit across Web, API, Database, Docker/Render, and Flutter apps.
- Created `MARKETPLACE_AUDIT.md`.

### Phase 1: Critical Security Hardening (COMPLETED)
- **Tenant Isolation:** Removed unsafe `Seller::first()` fallback in `SellerDashboardController.php`. Scoped store access strictly to authenticated, verified memberships.
- **Warehouse Ownership Validation:** Prevented cross-seller warehouse tampering by enforcing `Warehouse::where('seller_id', $seller->id)`.
- **Privilege Escalation Protection:** Restricted role changes in `AdminDashboardController.php`:
  - Only `superadmin` can grant or modify `admin` / `superadmin` roles.
  - Standard admins cannot demote or block superadministrators.
  - The last active superadministrator is guarded against deletion or demotion.
- **Authentication Failsafe Hardening:**
  - Demo password fallback (`admin123`) strictly restricted to `APP_DEMO_MODE=true` or local/testing environments.
  - Deactivated accounts are never reactivated during failed login attempts.
- **Product Moderation Enforcement:** Suspended stores blocked from creating products; new listings set to `pending` unless the seller has verified active status.

### Phase 2: Deployment & Storage Persistence (COMPLETED)
- **Persistent Image Storage:** Added automated persistent symlinking in `docker/entrypoint.sh` from `/var/www/html/storage/data/images/products` and `/var/www/html/storage/data/uploads` into `/var/www/html/public/` so user-uploaded product images survive container redeployments on Render.
- **Background Worker & Scheduler:**
  - Added `[program:laravel-worker]` (`php artisan queue:work`) to `docker/supervisord.conf`.
  - Added `[program:laravel-scheduler]` (`php artisan schedule:run`) to `docker/supervisord.conf`.
  - Registered `inventory:release-expired` every 5 minutes in `backend/routes/console.php`.

### Phase 3: Core Commerce & Shipping Refinement (IN PROGRESS)
- Standardized `trade_mode` across migrations, models, and Blade forms (`wholesale`, `retail`, `both`).
- Unified shipping fee calculations: standard 30,000 UZS base delivery per seller suborder with automatic free shipping threshold at 1,000,000 UZS.
- Muddatli to'lov (Nasiya installment plans for 3, 6, 12 months) fully integrated from product page through multi-vendor checkout and order detail views.

### Phase 4: Four-Panel Experience
- **Customer Panel:** Streamlined registration, search, cart, checkout, Nasiya selector, and order tracking.
- **Seller Center:** Store profile tab, inventory balances, product creation with multi-tier pricing, suborder fulfillment, and ledger balances.
- **Courier Panel:** Assigned shipments, tracking events, status updates, and delivery verification.
- **Admin/Superadmin Panel:** Complete moderation of products, sellers, refunds, audit logs, and user roles.

### Phase 5: Mobile Apps & API Compatibility
- Maintained REST API `/api/v1` compatibility for `apps/customer_flutter` and `apps/courier_flutter`.
- Verified Riverpod state management flows and token authentication.

### Phase 6: Final Quality Assurance & Production Checklist
- Continuous 100% test pass rate across all unit and feature tests.
- Zero data loss guarantees and non-destructive database migrations.
