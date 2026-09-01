# NEXUS — Security Audit (MVP)

## Tenant Isolation
- [x] Composite UNIQUE (membership_id, org) + (project_id, org)
- [x] Composite FK 9 entries, ON DELETE matrix locked (RESTRICT except project_memberships CASCADE + notifications CASCADE)
- [x] Cross-tenant test PASS (Expense cross-org insert rejected via FK)

## Authz
- [x] Permission string only (no role.name branching)
- [x] 9 expense perms distinct (create/read/update/delete/submit/review/approve/reject/complete)
- [x] ExpensePolicy checks: isActive + hasPermission + sameOrg + state + assignment
- [x] OrganizationContext request-scoped singleton

## Expense
- [x] State machine PENDING->APPROVED->PAID, PENDING->REJECTED, forbidden transitions blocked
- [x] Manual APPROVED->PAID (two ops, two audits, two notifications)
- [x] Audit 5 actions transactional (CREATE/REVIEW/APPROVE/COMPLETE/REJECT)
- [x] Notification transactional, recipient same org

## DB
- [x] UUID CHAR(36) PK + FK, no BIGINT
- [x] ENUMs 5 types
- [x] Migrations 16 Ran, MySQL 8.0.46 @ 3307

## Tests
- [x] 9 tests PASS (TenantIsolation, ExpenseLifecycle, AuditNotification, Example)

## Known Pending (not MVP)
- API Client (Sanctum ready, not implemented)
- Invite flow (status invited preserved, no endpoint)
- Frontend
