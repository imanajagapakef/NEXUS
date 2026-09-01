# NEXUS --- DIAGRAM SOURCE OF TRUTH & AGENT BUILD GUIDELINES

> Dokumen ini adalah kontrak kerja untuk AI/CLI agent yang
> mengembangkan, memperbaiki, atau merevisi project **NEXUS**.
>
> **Aturan utama:** agent WAJIB menjadikan diagram sumber di project
> sebagai source of truth sebelum mengubah arsitektur, database,
> workflow, authorization, atau fitur. Jangan mengarang business rule.

------------------------------------------------------------------------

## 1. SOURCE OF TRUTH

Artefak desain yang tersedia:

-   `NEXUS - Use Case Diagram.svg.txt`
-   `NEXUS - Domain Model_v7.svg.txt`
-   `NEXUS - ERD_v2.svg.txt`
-   `NEXUS - PostgreSQL Database Schema_v4.svg.txt`
-   `NEXUS - Activity Diagram Utama_v8.svg.txt`
-   `NEXUS - Activity 05.1_v12.svg.txt`
-   `NEXUS - Activity 05.2_v2.svg.txt`
-   `NEXUS - Activity 05.3_v2.svg.txt`
-   `NEXUS - Activity 05.4.svg.txt`
-   `NEXUS - Activity 05.5.svg.txt`
-   `NEXUS - Sequence Diagram.svg.txt`

### Prioritas referensi

Jika ada perbedaan:

1.  PostgreSQL Database Schema untuk persistence.
2.  ERD untuk relasi dan tenant boundary.
3.  Domain Model untuk konsep domain dan cardinality.
4.  Use Case Diagram untuk scope dan actor.
5.  Activity Diagram Utama untuk alur tingkat tinggi.
6.  Activity 05.1--05.5 untuk workflow detail.
7.  Sequence Diagram untuk urutan interaksi dan persistence.
8.  Kode existing untuk detail implementasi teknis selama tidak
    bertentangan dengan desain.

Kode existing bukan alasan otomatis untuk mengubah desain. Jika kode dan
desain konflik, identifikasi konflik dan jangan menyelesaikannya secara
diam-diam.

------------------------------------------------------------------------

# 2. ATURAN KERJA AGENT

## Sebelum coding

WAJIB:

1.  Inspect repository.
2.  Baca instruksi project yang relevan.
3.  Identifikasi modul terdampak.
4.  Baca diagram yang berkaitan dengan task.
5.  Pahami actor, organization, membership, role, permission, entity,
    status, workflow, authorization, audit, notification.
6.  Cek dampak terhadap database, API, authorization, UI, audit,
    notification, dan tenant isolation.

Jangan langsung coding hanya berdasarkan nama task.

## Jangan mengarang fitur

Jangan menambahkan entity, status, role, permission, workflow, endpoint,
tabel, field, business rule, atau notification baru kecuali sudah ada
pada source of truth atau diminta eksplisit oleh user.

Jika kebutuhan ambigu, tandai sebagai ambiguity. Jangan menebak.

## Jangan menghapus business rule

Jangan melemahkan:

-   organization isolation;
-   membership validation;
-   role/permission validation;
-   tenant-safe foreign key;
-   audit logging;
-   expense approval workflow;
-   notification lifecycle;
-   status transition.

------------------------------------------------------------------------

# 3. DOMAIN MODEL

Entity utama:

``` text
User
Organization
Membership
Role
Permission
Project
Task
Expense
Report
AuditLog
Notification
```

## User

``` text
userId
name
email
passwordHash
```

Database:

``` text
user_id UUID PK
name VARCHAR(100) NOT NULL
email VARCHAR(255) NOT NULL UNIQUE
password_hash VARCHAR(255) NOT NULL
created_at TIMESTAMPTZ NOT NULL
updated_at TIMESTAMPTZ NOT NULL
```

## Organization

``` text
organization_id UUID PK
name VARCHAR(100) NOT NULL
created_at TIMESTAMPTZ NOT NULL
updated_at TIMESTAMPTZ NOT NULL
UNIQUE(name)
```

Organization adalah tenant boundary utama.

## Membership

Membership menghubungkan User, Organization, dan Role.

``` text
membership_id UUID PK
user_id UUID FK NOT NULL
organization_id UUID FK NOT NULL
role_id UUID FK NOT NULL
joined_at TIMESTAMPTZ NOT NULL
status membership_status NOT NULL
created_at TIMESTAMPTZ NOT NULL
```

Constraint:

``` text
UNIQUE(user_id, organization_id)
UNIQUE(membership_id, organization_id)
```

Status:

``` text
invited
active
inactive
```

Membership tidak boleh digunakan lintas organization.

------------------------------------------------------------------------

# 4. ROLE & PERMISSION

Role yang dimodelkan:

``` text
Owner
Admin
Manager
Staff
Viewer
```

Permission terhubung ke role melalui:

``` text
role_permissions
```

Authorization harus mengikuti:

``` text
authenticated user
    ↓
selected organization
    ↓
membership
    ↓
membership status
    ↓
membership role
    ↓
role permissions
    ↓
requested operation
```

Authentication != authorization.

------------------------------------------------------------------------

# 5. TENANT ISOLATION

NEXUS adalah **Multi-Tenant Business Operations Platform**.

Organization-scoped data wajib menggunakan organization context.

Tenant-sensitive membership reference harus menggunakan:

``` text
(membership_id, organization_id)
```

Jangan mengandalkan `membership_id` saja ketika reference tersebut harus
aman terhadap cross-tenant access.

Project child references menggunakan:

``` text
(project_id, organization_id)
```

Tujuannya mencegah resource Organization A mereferensikan resource
Organization B.

------------------------------------------------------------------------

# 6. PROJECT & TASK

## Project

``` text
project_id UUID PK
organization_id UUID FK NOT NULL
name VARCHAR(100) NOT NULL
description TEXT
status project_status NOT NULL
start_date DATE
end_date DATE
created_at TIMESTAMPTZ NOT NULL
updated_at TIMESTAMPTZ NOT NULL
```

Status:

``` text
active
completed
archived
```

Constraint:

``` text
UNIQUE(organization_id, name)
UNIQUE(project_id, organization_id)
```

## Project Membership

M:N:

``` text
Project ↔ Membership
```

Table:

``` text
project_memberships
```

Fields:

``` text
project_id
membership_id
organization_id
```

## Task

``` text
task_id UUID PK
project_id UUID FK NOT NULL
organization_id UUID FK NOT NULL
assignee_membership_id UUID FK NULLABLE
title VARCHAR(200) NOT NULL
description TEXT
status task_status NOT NULL
priority task_priority NOT NULL
due_date DATE
created_at TIMESTAMPTZ NOT NULL
updated_at TIMESTAMPTZ NOT NULL
```

Status:

``` text
open
in_progress
done
cancelled
```

Priority:

``` text
low
medium
high
```

References tenant-safe:

``` text
(project_id, organization_id)
(assignee_membership_id, organization_id)
```

------------------------------------------------------------------------

# 7. EXPENSE WORKFLOW

Expense:

``` text
expense_id
organization_id
creator_membership_id
reviewer_membership_id
approver_membership_id
description
amount
status
created_at
```

Status:

``` text
pending
approved
rejected
paid
```

Workflow:

``` text
Create expense
→ Enter description and amount
→ Submit expense
→ Validate expense data
→ Valid expense?
```

Invalid:

``` text
Display validation error
```

Valid:

``` text
Set status to PENDING
→ Record audit event
→ Forward expense for approval
→ Review expense
→ Expense accepted for approval?
```

Reviewer accepts:

``` text
Set reviewer membership
→ Record audit event
→ Forward to approver
```

Reviewer rejects:

``` text
Set status to REJECTED
→ Record audit event
→ Create rejection notification
```

Approver:

``` text
Review expense
→ Approve or reject expense
→ Expense approved?
```

Approved:

``` text
Set status to APPROVED
→ Record audit event
→ Create approval notification
→ Complete expense
→ Set status to PAID
→ Record audit event
→ Create completion notification
```

Rejected:

``` text
Set status to REJECTED
→ Record audit event
→ Create rejection notification
```

State machine:

``` text
PENDING
 ├──→ APPROVED
 │      └──→ PAID
 └──→ REJECTED
```

Jangan membuat PENDING → PAID tanpa approval sebagai workflow default.

------------------------------------------------------------------------

# 8. SEQUENCE DIAGRAM --- EXPENSE

Participants:

``` text
Member
Reviewer
Approver
NEXUS System
Database
```

Urutan utama:

``` text
1. Create Expense(description, amount)
2. Validate expense data
3. Display validation error [data tidak valid]
4. INSERT expense (status = PENDING)
5. INSERT audit_log (CREATE_EXPENSE)
6. Expense created (PENDING)
7. Submit Expense
8. UPDATE expense (forwarded for approval)
9. INSERT notification (target = Reviewer)
10. Notify: expense pending review

11. Review Expense
12. Evaluate acceptance for approval
13. UPDATE expense (reviewer_membership_id)
14. INSERT audit_log (REVIEW_EXPENSE)
15. INSERT notification (target = Approver)
16. Notify: expense ready for approval

17. UPDATE expense (status = REJECTED)
18. INSERT audit_log (REJECT_EXPENSE)
19. INSERT notification (target = Member)
20. Notify: expense rejected

21. Approve or Reject Expense
22. UPDATE expense (status = APPROVED)
23. INSERT audit_log (APPROVE_EXPENSE)
24. INSERT notification (target = Member)
25. Notify: expense approved

26. Complete Expense (process payment)
27. UPDATE expense (status = PAID)
28. INSERT audit_log (COMPLETE_EXPENSE)
29. INSERT notification (target = Member)
30. Notify: payment completed

31. UPDATE expense (status = REJECTED)
32. INSERT audit_log (REJECT_EXPENSE)
33. INSERT notification (target = Member)
34. Notify: expense rejected
```

Jika expense workflow diubah, cek kembali Activity 05.3, Sequence
Diagram, schema Expense, AuditLog, dan Notification.

------------------------------------------------------------------------

# 9. ACTIVITY 05.1 --- AUTHENTICATION & ORGANIZATION ACCESS

Judul:

``` text
Activity Diagram 5.1. | Authentication & Organization Access
```

Swimlane:

``` text
User
NEXUS
```

Flow:

``` text
START
→ Open NEXUS
→ Enter email and password
→ Validate credentials
→ Credentials valid?
```

Yes:

``` text
Create authenticated session
→ Load user's memberships
→ Membership available?
```

Yes:

``` text
Select organization
→ Validate selected membership
→ Membership active?
```

Yes:

``` text
Load membership role
→ Load role permissions
→ Access permitted?
```

Yes:

``` text
Grant organization access
→ Open organization workspace
→ END
```

Access denied:

``` text
Display permission denied
→ END
```

Inactive:

``` text
Display inactive membership
→ END
```

No membership:

``` text
Display no organization available
→ END
```

Invalid credentials:

``` text
Display invalid credentials
→ Retry login?
```

Yes:

``` text
kembali ke node yang sama:
Enter email and password
```

No:

``` text
END
```

### Larangan Activity 05.1

Jangan menambahkan:

``` text
Retry authentication?
```

Jangan membuat duplicate node login untuk loop.

------------------------------------------------------------------------

# 10. ACTIVITY 05.2 --- PROJECT & TASK MANAGEMENT

Actors:

``` text
User
Project Manager
Project Manager / Member
NEXUS
```

Entry:

``` text
Open project workspace
→ View projects
→ Select action?
```

## Project

``` text
Manage project
→ Create or update project
→ Select project member
→ Validate organization membership
→ Validate authorization
→ Authorized?
```

Authorized:

``` text
Save project changes
→ Record audit event
```

Unauthorized:

``` text
Deny operation
```

## Task

``` text
Manage task
→ Open project
→ View project tasks
→ Select task action?
→ Create or update task
→ Validate project membership
→ Validate authorization
→ Authorized?
```

Authorized:

``` text
Save task changes
→ Record audit event
```

Unauthorized:

``` text
Deny operation
```

## Assignment

``` text
Assign task
→ Select task action?
→ Validate assignee membership
→ Valid assignee?
```

Valid:

``` text
Save task assignment
→ Create notification
→ Record audit event
```

Invalid:

``` text
Reject assignment
```

## Task status

``` text
Update task status
→ Validate authorization
→ Authorized?
```

Authorized:

``` text
Save task status
→ Record audit event
```

Unauthorized:

``` text
Deny operation
```

------------------------------------------------------------------------

# 11. ACTIVITY 05.4 --- REPORTING & AUDIT

Actors:

``` text
Authorized User
NEXUS
```

Entry:

``` text
Open administration area
→ Choose Reporting or Audit
```

## Reporting

``` text
Select report type
→ Select reporting period
→ Request report
→ Validate organization context
→ Validate authorization
→ Authorized?
```

Authorized:

``` text
Generate report
→ Store report metadata
→ Record audit event
→ View generated report
```

Unauthorized:

``` text
Deny report access
```

## Audit

``` text
Request audit logs
→ Validate organization context
→ Validate authorization
→ Authorized?
```

Authorized:

``` text
Retrieve organization audit logs
→ View audit logs
```

Unauthorized:

``` text
Deny audit access
```

Audit logs selalu organization-scoped.

------------------------------------------------------------------------

# 12. ACTIVITY 05.5 --- NOTIFICATION LIFECYCLE

Swimlane:

``` text
NEXUS
User
```

Workflow:

``` text
Detect notification-triggering event
→ Notification required?
```

No:

``` text
Continue current workflow
```

Yes:

``` text
Create notification
→ Associate notification with membership
→ Receive notification
→ Open notification
→ Mark notification as read
→ Continue operation
```

Notification harus terkait membership dan organization yang benar.

------------------------------------------------------------------------

# 13. ACTIVITY DIAGRAM UTAMA

Backbone:

``` text
Authentication
→ Organization & Access
→ Select operation
→ System Operation
→ Post-Operation Processing
```

System Operation:

``` text
Organization Management
Project & Task Management
Expense Management
Reporting
Audit
```

Post-Operation Processing:

``` text
Audit Logging
Notification
```

Authentication:

``` text
User membuka aplikasi NEXUS
→ Input email dan password
→ Kredensial valid?
→ Membuat sesi autentikasi / menampilkan pesan kesalahan
```

Organization & Access:

``` text
Memilih Organization
→ Memvalidasi membership user
→ Membership aktif?
→ Memuat Role & Permission
→ Authorized?
```

Jika tidak authorized:

``` text
Menampilkan akses ditolak
```

------------------------------------------------------------------------

# 14. USE CASE BOUNDARY

Actors:

``` text
Owner
Admin
Manager
Staff
Viewer
Organization Member
API Client
```

Use case groups:

## Organization & Access

``` text
Authenticate
Switch Organization
Create Organization
Manage Members
Manage Roles & Permissions
```

## Project & Task

``` text
Manage Projects
View Projects
Manage Tasks
View Tasks
Assign Tasks
```

## Expense

``` text
Create Expense
Submit Expense
Review Expense
Approve Expense
Reject Expense
Complete Expense
```

## Reporting & Audit

``` text
Generate Reports
View Reports
View Audit Logs
```

## Notifications

``` text
View Notifications
```

## API

``` text
Consume REST API
```

Jangan memperluas use-case boundary tanpa instruksi eksplisit.

------------------------------------------------------------------------

# 15. DATABASE CONTRACT

PostgreSQL.

Enum:

``` text
membership_status:
invited
active
inactive

project_status:
active
completed
archived

task_status:
open
in_progress
done
cancelled

task_priority:
low
medium
high

expense_status:
pending
approved
rejected
paid
```

Tables:

``` text
users
roles
permissions
role_permissions
organizations
memberships
projects
project_memberships
tasks
expenses
reports
audit_logs
notifications
```

Jangan membuat tabel duplikat hanya untuk mengakomodasi implementasi.

------------------------------------------------------------------------

# 16. COMPOSITE TENANT-SAFE REFERENCES

WAJIB menjaga:

``` text
membership_id + organization_id
```

untuk reference membership yang tenant-sensitive.

Contoh:

``` text
tasks.assignee_membership_id
expenses.creator_membership_id
expenses.reviewer_membership_id
expenses.approver_membership_id
audit_logs.membership_id
notifications.membership_id
project_memberships.membership_id
```

Project references:

``` text
(project_id, organization_id)
```

Tujuan utamanya adalah mencegah cross-organization reference.

Contoh yang wajib ditolak:

``` text
Organization A Project
+
Organization B Membership
```

dan:

``` text
Organization A Expense
+
Organization B Reviewer
```

------------------------------------------------------------------------

# 17. SECURITY CONTRACT

## Authentication

Memastikan identitas user.

## Authorization

Memastikan user boleh melakukan operation.

Authorization wajib mempertimbangkan:

``` text
User
→ Membership
→ Organization
→ Membership status
→ Role
→ Permission
→ Operation
```

Frontend visibility bukan security control.

Backend harus tetap melakukan authorization walaupun tombol/menu
disembunyikan di UI.

------------------------------------------------------------------------

# 18. AUDIT CONTRACT

AuditLog:

``` text
audit_log_id
organization_id
membership_id
action
entity_type
entity_id
timestamp
```

Audit event yang sudah dimodelkan antara lain:

``` text
CREATE_EXPENSE
REVIEW_EXPENSE
APPROVE_EXPENSE
COMPLETE_EXPENSE
REJECT_EXPENSE
```

Jangan mengubah action name tanpa mengecek semua workflow yang
menggunakannya.

------------------------------------------------------------------------

# 19. NOTIFICATION CONTRACT

Contoh expense notification:

``` text
Expense submitted
→ Reviewer

Reviewer accepts
→ Approver

Reviewer rejects
→ Member

Approver approves
→ Member

Approver rejects
→ Member

Payment completed
→ Member
```

Recipient harus berada pada organization yang sama.

------------------------------------------------------------------------

# 20. TRACEABILITY

Setiap implementasi harus dapat ditelusuri:

``` text
Feature
→ Use Case
→ Activity
→ Domain Model
→ Database / ERD
→ Sequence
→ Implementation
```

Jika fitur tidak memiliki dasar pada artefak desain, jangan mengarang
desainnya.

------------------------------------------------------------------------

# 21. PERUBAHAN DATABASE

Sebelum mengubah database:

1.  Cek PostgreSQL Schema.
2.  Cek ERD.
3.  Cek Domain Model.
4.  Cek Activity yang memakai entity tersebut.
5.  Cek Sequence Diagram bila tersedia.
6.  Tentukan dampak field, constraint, FK, enum, index, relation, dan
    tenant boundary.
7.  Cek seluruh workflow yang terdampak.

Dilarang menghapus `organization_id` dari entity organization-scoped
hanya untuk menyederhanakan schema.

Dilarang menghapus composite tenant-safe FK tanpa perubahan desain yang
disengaja dan tervalidasi.

------------------------------------------------------------------------

# 22. PERUBAHAN API

Sebelum membuat endpoint:

1.  Identifikasi use case.
2.  Identifikasi actor.
3.  Identifikasi organization context.
4.  Identifikasi permission.
5.  Identifikasi entity.
6.  Identifikasi validation.
7.  Identifikasi allowed state transition.
8.  Identifikasi audit.
9.  Identifikasi notification.

API tidak boleh melewati authorization.

------------------------------------------------------------------------

# 23. PERUBAHAN UI

UI bukan sumber business logic.

Frontend boleh melakukan:

-   display;
-   basic input validation;
-   navigation;
-   conditional visibility.

Backend tetap wajib memvalidasi:

``` text
authentication
organization membership
membership status
authorization
tenant boundary
state transition
```

Jangan menggunakan hidden button sebagai security mechanism.

------------------------------------------------------------------------

# 24. KONFLIK ANTAR ARTEFAK

Jika ada konflik:

1.  Jangan menyembunyikan konflik.
2.  Identifikasi semua artefak yang terdampak.
3.  Jelaskan perbedaannya.
4.  Jangan memilih secara sembarangan.
5.  Jangan mengubah diagram atau database secara diam-diam.
6.  Jika keputusan membutuhkan perubahan desain, minta keputusan user.

Contoh:

``` text
Activity:
Reviewer rejects → REJECTED

Implementation:
Reviewer rejects → PENDING
```

Ini adalah design/implementation conflict dan harus ditangani secara
eksplisit.

------------------------------------------------------------------------

# 25. JANGAN MEMPERBAIKI DIAGRAM SECARA DIAM-DIAM

Jika diagram tampak kurang ideal tetapi masih konsisten:

``` text
Jangan ubah.
```

Jika task memang meminta revisi diagram:

``` text
ubah source diagram
→ cek diagram terkait
→ cek domain
→ cek ERD
→ cek schema
→ cek sequence
→ pastikan seluruh artefak konsisten
```

------------------------------------------------------------------------

# 26. CROSS-DIAGRAM CHECK

## Authentication

``` text
Use Case
Activity Utama
Activity 05.1
Database
Domain Model
```

## Project

``` text
Use Case
Activity 05.2
Domain Model
ERD
Database
Audit
Notification
```

## Expense

``` text
Use Case
Activity 05.3
Sequence Diagram
Domain Model
ERD
Database
Activity 05.5
```

## Reporting

``` text
Use Case
Activity 05.4
Domain Model
ERD
Database
Audit
```

## Audit

``` text
Use Case
Activity Utama
Activity 05.4
Database
```

## Notification

``` text
Use Case
Activity Utama
Activity 05.5
Activity 05.2
Activity 05.3
Database
```

------------------------------------------------------------------------

# 27. AGENT EXECUTION PROTOCOL

## Phase 1 --- Inspect

``` text
Inspect repository
Read project instructions
Locate relevant source files
Locate relevant diagrams
```

## Phase 2 --- Understand

``` text
Understand domain
Understand tenant boundary
Understand authorization
Understand workflow
Understand persistence
```

## Phase 3 --- Plan

Catat:

``` text
Files to modify
Files to create
Database impact
API impact
Authorization impact
Audit impact
Notification impact
Test impact
```

## Phase 4 --- Implement

Gunakan:

``` text
smallest correct change
```

Jangan refactor unrelated code.

## Phase 5 --- Verify

Gunakan command yang tersedia di project, minimal bila relevan:

``` text
lint
typecheck
tests
build
migration/schema validation
```

## Phase 6 --- Review

Pastikan:

``` text
implementation
↕
workflow
↕
diagram
↕
database
```

tetap konsisten.

------------------------------------------------------------------------

# 28. DEFINITION OF DONE

-   [ ] Workflow sesuai diagram.
-   [ ] Use case tidak melebar.
-   [ ] Organization context benar.
-   [ ] Membership validation benar.
-   [ ] Authorization benar.
-   [ ] Tenant isolation aman.
-   [ ] Database konsisten dengan ERD/schema.
-   [ ] Composite tenant-safe references tetap benar.
-   [ ] Status transition valid.
-   [ ] Audit event sesuai desain.
-   [ ] Notification sesuai desain.
-   [ ] API konsisten dengan use case.
-   [ ] Error path ditangani.
-   [ ] Existing functionality tidak rusak.
-   [ ] Typecheck berhasil.
-   [ ] Lint berhasil.
-   [ ] Test relevan berhasil.
-   [ ] Build berhasil jika tersedia.
-   [ ] Diff hanya berisi perubahan yang diperlukan.

------------------------------------------------------------------------

# 29. RESPONSE FORMAT UNTUK TASK KOMPLEKS

Gunakan:

``` text
## Understanding
<pemahaman task>

## Relevant Sources
<diagram/file yang menjadi dasar>

## Impact
- Database:
- API:
- Authorization:
- Audit:
- Notification:
- UI:

## Plan
1.
2.
3.

## Risks / Conflicts
<jika ada>

## Implementation
<perubahan>

## Verification
<hasil lint/typecheck/test/build>
```

Untuk task sederhana, jangan membuat laporan berlebihan.

------------------------------------------------------------------------

# 30. CORE INVARIANTS

Agent WAJIB menjaga:

1.  User authenticated sebelum organization workspace.
2.  User harus memiliki membership pada organization.
3.  Membership harus active untuk organization access.
4.  Role berasal dari membership organization tersebut.
5.  Permission berasal dari role tersebut.
6.  Resource organization-scoped hanya boleh diakses dalam tenant yang
    benar.
7.  Membership downstream harus tenant-safe.
8.  Operation sensitif wajib melewati authorization.
9.  Expense tidak boleh menjadi PAID tanpa approval workflow.
10. Event yang diwajibkan diagram harus diaudit.
11. Event yang diwajibkan diagram harus menghasilkan notification.
12. Status database harus konsisten dengan activity dan sequence.

------------------------------------------------------------------------

# 31. FINAL DIRECTIVE

**Jangan ke mana-mana dari desain NEXUS.**

Urutan kerja:

``` text
Baca source of truth
        ↓
Pahami workflow
        ↓
Pahami tenant boundary
        ↓
Pahami authorization
        ↓
Pahami database
        ↓
Rencanakan perubahan
        ↓
Implementasikan
        ↓
Verifikasi
        ↓
Cek konsistensi seluruh diagram
```

Jika informasi tidak tersedia:

``` text
DO NOT GUESS.
```

Jika terdapat konflik:

``` text
DO NOT HIDE THE CONFLICT.
```

Jika perubahan tidak diperlukan:

``` text
DO NOT CHANGE IT.
```

Jika task selesai:

``` text
DO NOT EXPAND THE SCOPE.
```

NEXUS harus diperlakukan sebagai satu sistem yang konsisten antara **Use
Case, Domain Model, ERD, PostgreSQL Schema, Activity Diagram, Sequence
Diagram, API, dan implementasi**.
