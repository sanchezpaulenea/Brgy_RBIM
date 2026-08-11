# Implementation Plan – Authentication (Increment 1)

This plan covers the **Authentication** part of the User Management Module using the architecture diagram provided. The database schema is taken **as-is** from `brgy_rbim.sql` — no migrations will be created or modified.

---

## Architecture Clarification

You are correct — we are using **MVC + Service + Repository**, not a flat Controller-Service-Repository. Based on your diagram, each request flows through these layers in order:

```
Controller → Form Request (validate) → Policy (authorize) → Service (business logic)
           → Interface (contract) → Repository (DB access) → Model → Database
```

Two **Service Providers** wire everything together on boot:
- **RepositoryServiceProvider** — binds each Interface to its concrete Repository.
- **AuthServiceProvider** — registers Model → Policy pairs.

---

## Database (No Changes)

The schema from `brgy_rbim.sql` is used exactly as defined. Key tables for authentication:

| Table | Purpose |
|---|---|
| `user` | Credentials (`username`, `password_hash`, `must_change_password`, `user_status_id`) |
| `user_status` | Lookup: Active, Disabled, Locked, Suspended (with `can_login` flag) |
| `user_role` | User ↔ Role pivot (`enable` flag, `assigned_by`) |
| `role` | Roles: Super Admin, Admin, Encoder, Guest |
| `role_permission` | Role ↔ Permission pivot |
| `permission` | All granular permissions (e.g. `user.create`, `user.changepassword`) |
| `user_log` | Login/logout event log with `login_status_id`, `ip_address`, `device` |
| `login_status` | Lookup: Success, Invalid Password, Invalid Username, Invalid Credentials, Account Locked |
| `system_setting` | Password policy settings (`max_login_attempts`, `account_lockout_minutes`, `session_timeout_minutes`) |
| `sessions` | Laravel's database session driver (already in default migration) |

> [!NOTE]
> The `user` table uses `username` (not `email`) as the login credential. The field `password_hash` stores the bcrypt hash.

---

## Session Strategy

- `SESSION_DRIVER=database` → sessions stored in MySQL `sessions` table.
- Laravel Sanctum with **stateful cookie authentication** for the Vue.js SPA.
- On login, Laravel regenerates the session ID and writes to `sessions`.
- On logout, the session is invalidated and the `user_log.logout_time` is recorded.

---

## Default Roles (to seed)

| Role | Notes |
|---|---|
| Super Admin | All permissions |
| Admin | Settings + user management |
| Encoder | Personnel CRUD |
| Guest | Change own password only |

> [!NOTE]
> The SQL already seeds these roles and their `role_permission` assignments. The Laravel seeder will simply import/sync this data.

---

## Proposed Changes

### 1. Configuration

#### [MODIFY] [.env](file:///c:/Users/danie/ITProj2/Brgy_RBIM/.env)
- Change `SESSION_DRIVER=database`
- Update `APP_NAME` to the barangay system name.

#### [MODIFY] [bootstrap/app.php](file:///c:/Users/danie/ITProj2/Brgy_RBIM/bootstrap/app.php)
- Register `api: __DIR__.'/../routes/api.php'` route file.
- Add Sanctum's stateful middleware to the API group.

---

### 2. Models (in `app/Models/`)

All models map directly to the SQL schema — **no new columns, no schema changes**.

#### [MODIFY] `app/Models/UserManagement/User.php`
- Extend `Authenticatable`, use `HasApiTokens` (Sanctum).
- Map table `user`, primary key `user_id`, no timestamps autoincrement (use `created_at` only).
- Cast `must_change_password` to boolean.
- Relationships: `userStatus()`, `userRoles()`, `roles()`, `permissions()`.

#### [MODIFY] `app/Models/UserManagement/Role.php`
- Table `role`, PK `role_id`. Relationship: `permissions()` via `role_permission`.

#### [MODIFY] `app/Models/UserManagement/Permission.php`
- Table `permission`, PK `permission_id`.

#### [MODIFY] `app/Models/UserManagement/UserRole.php`
- Pivot table `user_role`. Relationship: `role()`, `user()`, `assignedBy()`.

#### [MODIFY] `app/Models/UserManagement/UserStatus.php`
- Table `user_status`. Cast `can_login` to boolean.

#### [MODIFY] `app/Models/Authentication/LoginStatus.php`
- Table `login_status`, PK `login_status_id`.

#### [MODIFY] `app/Models/Authentication/UserLog.php`
- Table `user_log`, PK `user_log_id`. Relationships: `user()`, `loginStatus()`.

---

### 3. Form Requests (in `app/Http/Requests/`)

#### [NEW] `app/Http/Requests/Authentication/LoginRequest.php`
- Rules: `username` required|string|max:45, `password` required|string.

#### [NEW] `app/Http/Requests/Authentication/ChangePasswordRequest.php`
- Rules: `current_password` required, `new_password` required|min:{from settings}|confirmed.

---

### 4. Policies (in `app/Policies/`)

#### [NEW] `app/Policies/Authentication/AuthPolicy.php`
- `changePassword(User $user)` — any authenticated user with `user.changepassword` permission.

---

### 5. Interfaces (in `app/Repositories/Interfaces/`)

#### [NEW] `app/Repositories/Interfaces/Authentication/AuthRepositoryInterface.php`
- Abstract methods:
  - `findByUsername(string $username): ?User`
  - `logLoginAttempt(int $userId, int $loginStatusId, string $ip, string $device, ?string $logoutTime): UserLog`
  - `updateLoginLog(int $userLogId, string $logoutTime): void`
  - `getSystemSetting(string $key): string`

---

### 6. Repositories (in `app/Repositories/Authentication/`)

#### [NEW] `app/Repositories/Authentication/AuthRepository.php`
- Implements `AuthRepositoryInterface`.
- Uses `User`, `UserLog`, `SystemSetting` models for all DB operations.

---

### 7. Services (in `app/Services/Authentication/`)

#### [NEW] `app/Services/Authentication/AuthService.php`
- **`login(string $username, string $password, Request $request): array`**
  - Fetch user by username → 404/log `Invalid Username`.
  - Check `user_status.can_login` → log `Account Locked`/`Suspended`.
  - Verify password hash → increment failed attempts, check lockout threshold from `system_setting`, log `Invalid Password` / `Invalid Credentials`.
  - On success: regenerate session, log `Success` to `user_log`, return user data.
  - Check `must_change_password` flag and include in response.
- **`logout(User $user, int $userLogId, Request $request): void`**
  - Update `user_log.logout_time`.
  - Invalidate Laravel session.
- **`changePassword(User $user, string $currentPassword, string $newPassword): void`**
  - Verify current password.
  - Hash and save new password.
  - Set `must_change_password = 0`.
  - Log action to `audit_log`.

---

### 8. Controllers (in `app/Http/Controllers/Authentication/`)

#### [MODIFY] `app/Http/Controllers/Authentication/LoginController.php`
- `POST /api/v1/auth/login` — injects `LoginRequest` (validation), calls `AuthService::login()`.

#### [MODIFY] `app/Http/Controllers/Authentication/LogoutController.php`
- `POST /api/v1/auth/logout` — guarded by `auth:sanctum`, calls `AuthService::logout()`.

#### [MODIFY] `app/Http/Controllers/Authentication/PasswordController.php`
- `POST /api/v1/auth/password/change` — guarded, injects `ChangePasswordRequest`, applies `AuthPolicy::changePassword`, calls `AuthService::changePassword()`.

#### [NEW] `app/Http/Controllers/Authentication/MeController.php`
- `GET /api/v1/auth/me` — returns authenticated user's profile, roles, and permissions.

---

### 9. Routes

#### [MODIFY] [routes/api.php](file:///c:/Users/danie/ITProj2/Brgy_RBIM/routes/api.php)
```php
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login',           [LoginController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout',            [LogoutController::class, 'logout']);
            Route::post('password/change',   [PasswordController::class, 'change']);
            Route::get('me',                 [MeController::class, 'me']);
        });
    });
});
```

---

### 10. Service Providers (in `app/Providers/`)

#### [NEW] `app/Providers/RepositoryServiceProvider.php`
- Bind `AuthRepositoryInterface::class` → `AuthRepository::class`.
- Register in `bootstrap/app.php`.

#### [MODIFY] `app/Providers/AppServiceProvider.php`
- Register Policies (Model → Policy) in `boot()`.

---

### 11. Database Seeders

#### [MODIFY] `database/seeders/DatabaseSeeder.php`
- Call seeders for: `UserStatusSeeder`, `LoginStatusSeeder`, `RoleSeeder`, `PermissionSeeder`, `RolePermissionSeeder`, `SystemSettingSeeder`.
- These seeders replicate the SQL seed data so `php artisan migrate:fresh --seed` gives a working state.

---

## Verification Plan

### Automated Tests (PHPUnit Feature Tests)
```bash
php artisan test --filter=AuthenticationTest
```
Test coverage:
- ✅ Successful login returns 200 with user, roles, permissions.
- ✅ Login with wrong username returns 401 and logs `Invalid Username`.
- ✅ Login with wrong password returns 401, increments failed attempts, logs `Invalid Password`.
- ✅ Locked account (`user_status.can_login = 0`) returns 403 and logs `Account Locked`.
- ✅ `must_change_password = 1` flag is included in login response.
- ✅ Logout destroys session and updates `user_log.logout_time`.
- ✅ `/me` endpoint returns authenticated user with roles and permissions.
- ✅ Password change with wrong current password returns 422.
- ✅ Password change success updates hash and resets `must_change_password`.
