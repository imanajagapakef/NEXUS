# NEXUS — Multi-Tenant Business Operations Platform

Laravel 11 + PHP 8.3 + MySQL 8.x + Eloquent + Sanctum (API-ready)

## Stack
- PHP 8.3.33 (`C:\php83\php.exe`)
- MySQL 8.0.46 @ `127.0.0.1:3307` (MariaDB 10.4 @ 3306 preserved via XAMPP)
- Laravel 11.56.1
- InnoDB, UUID `CHAR(36)`, composite FK tenant isolation
- Sanctum installed as architecture-ready dependency — **API implementation remains PENDING BY DESIGN**

## Quick Start
```bash
C:\php83\php.exe artisan migrate --force
C:\php83\php.exe artisan db:seed
C:\php83\php.exe artisan test
```

Verify database (MySQL 8.x on 3307, not phpMyAdmin default 3306):
```bash
C:\mysql80\bin\mysql.exe -h 127.0.0.1 -P 3307 -u root -e "SHOW TABLES;" nexus
```

phpMyAdmin: `http://localhost/phpmyadmin` → server dropdown `NEXUS MySQL 8.0` (configured in `C:\xampp\phpMyAdmin\config.inc.php`).

## Architecture
- **Organization** = tenant boundary
- **Membership** (`user_id`, `organization_id`, `role_id`, `status` invited/active/inactive)
- **Role → Permission** (9 expense permissions distinct, permission-string authorization only)
- Composite FK: `(membership_id, organization_id)` + `(project_id, organization_id)`
- ON DELETE: `RESTRICT` (business/audit), `CASCADE` (`project_memberships`, `notifications`)
- `roles.name` = `VARCHAR(50) UNIQUE` (seeded values, not ENUM)

## Workflows
- 05.1 Auth (single retry node)
- 05.2 Project/Task
- 05.3 Expense (`PENDING → APPROVED → PAID`, manual `Complete`, `PENDING → REJECTED`)
- 05.4 Reporting/Audit (org-scoped)
- 05.5 Notification

## Git
- `main` + `develop` (pushed to `imanajagapakef/NEXUS`)
- Conventional commits

## Diagram
See `Diagram/` + `NEXUS_AGENT_SOURCE_OF_TRUTH.md`

## API Status
**PENDING BY DESIGN** — Sanctum is installed as an architecture-ready dependency. No REST API endpoints are implemented in the MVP. The `API Client` use case is preserved as a design requirement for a future phase.

---

# AI-Assisted Development

NEXUS was developed using an **AI-assisted software engineering workflow**. AI coding agents were used as **implementation and verification assistants**, while the system architecture, source-of-truth specification, design constraints, approval gates, and final engineering decisions were defined and reviewed by the developer.

> NEXUS was developed using an AI-assisted software engineering workflow. AI coding agents were used as implementation and verification assistants, while the system architecture, source-of-truth specification, design constraints, approval gates, and final engineering decisions were defined and reviewed by the developer.

AI assisted with:

- reading and understanding the Source of Truth (`NEXUS_AGENT_SOURCE_OF_TRUTH.md` + 11 diagrams)
- analyzing requirements, actors, and cross-diagram relationships
- designing the implementation plan from the BUILD SPECIFICATION and locked decisions
- scaffolding and implementing the Laravel application (migrations, Eloquent models, middleware, policies, services, workflows, and tests)
- debugging and root-cause analysis
- running phased verification and checkpoints
- performing security reviews and consistency audits
- assisting with documentation and final repository audit

Workflow:

```
Requirement → Source of Truth → Architecture/Diagrams → AI-assisted Implementation → Checkpoint Verification → Testing → Security Audit → Final Review
```

### AI Tools & Engineering Workflow

Development used a **repository-aware AI CLI agent workflow** with terminal and tool execution:

- AI coding agent / CLI agent operating directly on the repository (read, inspect, plan, implement, verify, cross-check)
- Repository-aware context (Source of Truth + `/Diagram/` as authoritative inputs)
- Terminal and tool execution for `composer`, `php artisan`, `mysql`, and `git` verification steps
- Automated verification and checkpoint workflow (migrations, `SHOW CREATE TABLE`, `migrate:status`, `artisan test`, `route:list`, `git diff --check`)
- Incremental, phase-gated implementation (Environment → Git → Scaffold → DB → Models → Tenant Context → Middleware → Auth → Policies → Workflows → Tests → Audit)

AI was not used to autonomously define business rules or override locked design decisions. All tool outputs were reviewed against the Source of Truth.

### Human-in-the-Loop

Development used explicit human-in-the-loop controls:

- **Source of Truth as contract** — `NEXUS_AGENT_SOURCE_OF_TRUTH.md` and `/Diagram/` are authoritative; implementation must follow them, not the other way around
- **Locked decisions require approval** — AI must not change locked stack, tenant model, composite FK, permission matrix, or state machines without explicit approval
- **Blockers are STOP conditions** — implementation blockers are reported and halt the phase instead of being silently worked around
- **Checkpoints before phase transitions** — each phase is verified (`migrate:status`, `SHOW CREATE TABLE`, `artisan test`, `git status`) before the next begins
- **Database/schema changes verified against specification** — `CHAR(36)` UUID, `ENUM` values, `UNIQUE` candidate keys, and `ON DELETE` matrix are checked via `SHOW CREATE TABLE` and `information_schema`
- **Security-sensitive behavior verified via tests and audit** — tenant isolation, permission-string authorization, expense state machine, and transactional audit/notification are covered by automated tests and `SECURITY_AUDIT.md`
- **Final implementation reviewed by the developer** — AI outputs are reviewed and the developer retains final engineering authority

## Project Engineering Highlights

Verified implementation highlights (see `SECURITY_AUDIT.md` and `tests/`):

- Multi-tenant architecture — `Organization` as tenant boundary
- Membership-based authorization — `User → Membership → Organization → Role → Permission`
- Permission-string authorization — 9 distinct expense permissions (`expense.create/read/update/delete/submit/review/approve/reject/complete`), no `role.name` branching
- Composite tenant-scoped foreign keys — `(membership_id, organization_id)` and `(project_id, organization_id)`
- UUID `CHAR(36)` primary and foreign keys (`HasUuids`, `uuid()->primary()`, no `BIGINT`)
- Expense state machine — `PENDING → APPROVED → PAID` (manual `Complete`), `PENDING → REJECTED`; forbidden transitions blocked
- Transactional expense mutation — `lockForUpdate()` + mutation + audit + notification in one DB transaction
- Audit logging — 5 locked actions (`CREATE/REVIEW/APPROVE/COMPLETE/REJECT_EXPENSE`) with org-scoped actor
- Notification workflow — 5-step expense chain, org-scoped recipient, `CASCADE` for notifications
- Tenant isolation — composite FK + `OrganizationContext` + `Policy` + `TenantGuard` + cross-tenant tests
- Security audit — checklist in `SECURITY_AUDIT.md`
- Automated tests — 12 tests (tenant isolation, expense lifecycle, auth, audit)
- Laravel 11 + PHP 8.3 + MySQL 8.x + Eloquent
- Sanctum / API-ready architecture — API remains `PENDING BY DESIGN`

## Environment Notes

- `C:\php83\php.exe` — PHP 8.3.33 (side-by-side with XAMPP 8.2.12)
- `C:\mysql80\` — MySQL 8.0.46 on `127.0.0.1:3307` (service `MySQL80Nexus` or `start-nexus-mysql.bat`)
- `C:\xampp\mysql\` — MariaDB 10.4.32 on `3306` preserved
- Always use `C:\php83\php.exe artisan ...` (not bare `php`) to avoid the XAMPP 8.2.12 platform check
