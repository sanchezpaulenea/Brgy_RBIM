## Project Architecture & Authorization Rules

Every feature module in this project must strictly follow this data flow pattern:
Controller → Form Request → Policy ($this->authorize()) → Service → Interface → Repository → Model → Database

### Core Conventions:
- **Policies:** Place all policy files inside `app/Policies` and register them in `AuthServiceProvider`.
- **Repositories:** Bind all repository interfaces to concrete repositories within `RepositoryServiceProvider`.
- **Authorization:** Do not use route-level permission middleware as the primary authorization gate. You must use Policy classes.

### Scope:
These architectural rules apply to all existing and future modules, including:
- User Management
- Session Management
- Lookup Tables
- Settings
- Logs
