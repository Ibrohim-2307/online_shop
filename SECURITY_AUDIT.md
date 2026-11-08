# Multi-Vendor Marketplace — Security Audit & Remediation (V1.0)

## Security Scope & Threat Modeling
This security review covers the web storefront, multi-tenant seller dashboards, courier tracking, administrative operations, RESTful API endpoints, session fixation, role-based access control (RBAC), and authentication token flows.

---

## Remediated Vulnerabilities & Safeguards

### 1. Cross-Tenant Data Leakage in Seller Dashboard (CWE-639 / BOLA)
- **Problem:** `SellerDashboardController.php` previously used `Seller::first()` as a fallback when an authenticated user had no assigned `seller_id`. This allowed unassigned seller staff to view and modify the first store's financial ledgers, inventory, and orders.
- **Remediation:** Removed the fallback. Store resolution is now strictly scoped to `$user->activeSeller() ?? ($user->seller_id ? Seller::find($user->seller_id) : null)`. If unassigned, access is immediately blocked.
- **Verification:** Tested in `SellerAndAdminPanelTest::test_unassigned_seller_cannot_fallback_to_seller_first`.

### 2. Privilege Escalation & Superadmin Account Takeover (CWE-269)
- **Problem:** Standard `admin` users could invoke `updateUserRole` in `AdminDashboardController.php` to promote arbitrary users to `superadmin`, demote existing superadministrators, or block superadmins.
- **Remediation:**
  - Role promotion to `admin` or `superadmin` is restricted exclusively to authenticated users with the `superadmin` role.
  - Standard admins cannot alter or block superadmin accounts.
  - The system prevents removing or blocking the last active superadministrator.
- **Verification:** Tested in `SellerAndAdminPanelTest::test_standard_admin_cannot_promote_to_superadmin_or_demote_superadmin`.

### 3. Unauthorized Cross-Warehouse Inventory Injection (CWE-285)
- **Problem:** Sellers could submit an arbitrary `warehouse_id` during product creation without verified ownership of that warehouse record.
- **Remediation:** Explicit ownership verification added: `Warehouse::where('id', $warehouseId)->where('seller_id', $seller->id)->first()`. Submitting another vendor's warehouse is rejected with a validation error.
- **Verification:** Tested in `SellerAndAdminPanelTest::test_seller_cannot_inject_stock_into_foreign_warehouse`.

### 4. Malicious SVG Script Execution via File Uploads (CWE-434)
- **Problem:** Permitting SVG uploads allowed potential Stored XSS via embedded `<script>` or `<foreignObject>` tags served under the application domain.
- **Remediation:** MIME types restricted strictly to `jpeg,png,jpg,webp`. SVG uploads are refused at the validation layer.

### 5. Suspended Vendor Operation Restriction
- **Problem:** Suspended sellers could still create or update product catalog entries.
- **Remediation:** Added active status verification (`$seller->status === 'suspended'`). Suspended sellers are forbidden from adding or editing catalog items.

### 6. Authentication Failsafe & Account Deactivation Protection
- **Problem:** Demo password (`admin123`) fallback previously had potential to reactivate deactivated user accounts (`'is_active' => true`).
- **Remediation:**
  - The failsafe is strictly limited to `APP_DEMO_MODE=true` or local/testing environments.
  - Deactivated accounts are checked first and blocked (`ACCOUNT_DEACTIVATED`) without altering their status or password.

---

## OWASP Top 10 Marketplace Alignment Matrix

| Category | Control Implementation | Status |
| :--- | :--- | :--- |
| **A01: Broken Access Control** | Policy checks, user/seller ownership scoping, route middleware | **Verified** |
| **A02: Cryptographic Failures** | Bcrypt hashing, secure HTTPS cookies, HMAC payment signatures | **Verified** |
| **A03: Injection** | PDO parameterized queries, Eloquent ORM, strict request validation | **Verified** |
| **A04: Insecure Design** | Atomic stock reservations with expirations, 2-step financial settlements | **Verified** |
| **A05: Security Misconfiguration**| Production debug mode disabled, no stack traces leaked to clients | **Verified** |
| **A06: Vulnerable Components** | PHP 8.4, Laravel 13, audited composer and npm dependencies | **Verified** |
| **A07: Identification & Auth** | Rate limiting on login and OTP, session regeneration, Sanctum tokens | **Verified** |
| **A08: Software & Data Integrity**| Non-destructive schema migrations, idempotent payment webhooks | **Verified** |
| **A09: Logging & Monitoring** | AuditService logging administrative and security events without secrets | **Verified** |
| **A10: SSRF** | Validated external webhooks with signature authentication | **Verified** |
