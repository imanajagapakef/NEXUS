# NEXUS — Multi-Tenant Business Operations Platform

Laravel 11 + PHP 8.3 + MySQL 8.x + Eloquent + Sanctum (API-ready)

## Stack
- PHP 8.3.33 (C:\php83)
- MySQL 8.0.46 @ 3307 (MariaDB 10.4 @ 3306 preserved)
- Laravel 11.56.1
- InnoDB, UUID CHAR(36), composite FK tenant isolation

## Quick Start
```bash
C:\php83\php.exe artisan migrate --force
C:\php83\php.exe artisan db:seed
C:\php83\php.exe artisan test
```

## Architecture
- Organization = tenant boundary
- Membership (user, organization, role, status invited/active/inactive)
- Role -> Permission (9 expense perms distinct, permission string only)
- Composite FK: (membership_id, organization_id) + (project_id, organization_id)
- ON DELETE: RESTRICT (business/audit), CASCADE (project_memberships, notifications)

## Workflows
- 05.1 Auth (single retry node)
- 05.2 Project/Task
- 05.3 Expense (PENDING->APPROVED->PAID, manual complete)
- 05.4 Reporting/Audit
- 05.5 Notification

## Git
- main + develop (pushed to imanajagapakef/NEXUS)
- Conventional commits

## Diagram
See Diagram/ + NEXUS_AGENT_SOURCE_OF_TRUTH.md
