# Changelog

All notable changes to the `online_shop` multi-vendor marketplace platform are documented in this file.

## [1.2.0] - 2026-11-08

### Security
- **Tenant Isolation:** Removed unsafe `Seller::first()` fallback in `SellerDashboardController.php`. Scoped store resolution to authenticated active memberships.
- **Privilege Escalation Prevention:** Enforced superadmin-only permission checks for assigning `admin` and `superadmin` roles in `AdminDashboardController.php`. Protected superadmins from being demoted or blocked by standard admins, and safeguarded the last active superadmin.
- **Warehouse Ownership Validation:** Prevented cross-seller warehouse tampering when creating products by scoping warehouse resolution to the seller's verified ID.
- **Suspended Store Enforcement:** Blocked suspended vendors from submitting products or changing store items.
- **Authentication Hardening:** Restricted `admin123` fallback strictly to demo mode (`APP_DEMO_MODE=true`) or local environments. Prevented deactivated accounts from being re-enabled during failed login attempts.
- **File Upload Security:** Disallowed SVG uploads in seller controllers to protect against stored XSS attacks.

### Infrastructure & Operations
- **Persistent Image Storage:** Added automated persistent symlink creation in `docker/entrypoint.sh` linking persistent storage disk paths (`/var/www/html/storage/data/images/products` and `/uploads`) into the public directory so user uploads survive Render container redeployments.
- **Supervisord Worker Configuration:** Added `laravel-worker` (`php artisan queue:work`) and `laravel-scheduler` (`php artisan schedule:run`) to `docker/supervisord.conf`.
- **Scheduled Commands:** Registered automatic release of expired inventory reservations (`inventory:release-expired`) every 5 minutes in `backend/routes/console.php`.

### E-Commerce & Checkout
- **Muddatli To'lov (Nasiya):** Fully interactive installment plans for 3, 6, and 12 months with 0% down payment, dynamic recalculation, committed inventory reservations, and active contract visibility on order confirmation and order tracking pages.
- **Shipping Rules:** Standardized delivery fee calculations with free-shipping threshold at 1,000,000 UZS.

### Testing
- Added 3 new security feature tests in `SellerAndAdminPanelTest.php`.
- Test suite passing at 100%: **145 tests, 957 assertions**.
