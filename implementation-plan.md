Below is a **full phased implementation plan** for Lusail Football Academy, structured as:

**Phase A = Quick MVP**
**Phase B = Full Internal Recruitment Management System**
**Phase C = Parent/Player Portal & Automation**

This approach lets you deliver quickly, reduce risk, and upgrade the system step by step instead of trying to build everything at once.

---

# Lusail Football Academy

## Player Recruitment System Implementation Plan

### Laravel Web Application

## Project Goal

Build a Laravel web application to manage the full player recruitment workflow for Lusail Football Academy, starting from player registration, assessment, acceptance/waiting/rejection decision, document collection, QFA/FIFA submission tracking, and finally team joining.

The current Google Form collects player details such as player name, year/date of birth, country of birth, citizenship, arrival year in Qatar, playing position, school, previous club, parent name, parent number, and parent WhatsApp. These fields should become the foundation of the new candidate registration module.

---

# Overall Implementation Strategy

The system should be built in **three controlled phases**:

| Phase   | Purpose                                         | Recommended Timeline |
| ------- | ----------------------------------------------- | -------------------: |
| Phase A | Launch quickly with core candidate tracking     |            4–6 weeks |
| Phase B | Upgrade to full internal recruitment management | 4–6 additional weeks |
| Phase C | Add parent/player portal and automation         | 4–6 additional weeks |

Total if all phases are implemented: **12–18 weeks**

---

# Phase A — MVP Recruitment System

## Objective

Deliver a working system quickly so Lusail staff can stop relying only on Google Forms, Excel sheets, folders, and manual tracking.

Phase A focuses on the essential workflow:

**Register candidate → Review → Assess → Decide → Track documents/basic QFA/FIFA status**

---

## Phase A Scope

### A1. System Setup

Set up the Laravel project foundation.

Includes:

- Laravel latest version setup
- MySQL database setup
- Authentication
- Admin login
- Basic roles
- Dashboard layout
- Responsive admin interface
- File upload configuration
- Basic system settings

Recommended technical stack:

- Laravel
- Blade + Livewire
- Tailwind CSS
- MySQL
- Laravel authentication
- Laravel permissions package, if needed
- Local or server file storage

---

### A2. User Roles — Basic

Phase A should keep roles simple.

Recommended roles:

1. **Admin**
2. **Coach / Assessor**
3. **Management Viewer**

Permissions:

| Feature                 | Admin |               Coach | Management |
| ----------------------- | ----: | ------------------: | ---------: |
| Manage candidates       |   Yes |  View assigned only |  View only |
| Update candidate status |   Yes | Recommendation only |         No |
| Add assessment notes    |   Yes |                 Yes |         No |
| Upload documents        |   Yes |                  No |         No |
| View reports            |   Yes |             Limited |        Yes |
| Manage users            |   Yes |                  No |         No |

---

### A3. Candidate Registration Module

Build a digital registration form based on the current Google Form.

Fields:

- Player full name
- Year of birth
- Date of birth
- Country of birth
- Citizenship
- Year player arrived in Qatar
- Playing position:
  - Goalkeeper
  - Defender
  - Midfielder
  - Attacker

- School attending
- Previous club/academy
- Parent name
- Parent phone number
- Parent WhatsApp number
- Email address
- Notes
- Season

Admin features:

- Add candidate manually
- Edit candidate
- View candidate profile
- Delete/archive candidate
- Search candidate by name, phone, or status

---

### A4. Candidate Status Tracking

Start with simple statuses.

Recommended Phase A statuses:

1. New Application
2. Assessment Scheduled
3. Assessment Completed
4. Accepted
5. Waiting List
6. Rejected
7. Documents Requested
8. Documents Completed
9. Submitted to QFA/FIFA
10. Joined Team

Each candidate profile should show:

- Current status
- Last updated date
- Updated by
- Internal notes

---

### A5. Assessment Module — Basic

Features:

- Create assessment note for candidate
- Record assessment date
- Select recommended decision:
  - Accepted
  - Waiting List
  - Rejected

- Add coach comments
- Add simple rating fields:
  - Technical
  - Physical
  - Discipline
  - Potential

Keep it simple in Phase A. Do not overbuild scoring formulas yet.

---

### A6. Basic Document Checklist

For accepted players, admin can track documents.

Example document checklist:

- Passport copy
- QID copy
- Birth certificate
- Player photo
- Parent QID/passport
- Previous club release letter
- Medical document, if needed
- QFA document
- FIFA document

For each document:

- Pending
- Received
- Approved
- Rejected / Needs correction

Features:

- Upload document
- Replace document
- Add note
- View document status

---

### A7. Basic QFA/FIFA Tracking

In Phase A, keep this as one simple section.

Fields:

- QFA status:
  - Not Started
  - Submitted
  - Approved
  - Returned / Need Correction

- FIFA status:
  - Not Started
  - Submitted
  - Approved
  - Returned / Need Correction

- Submission date
- Reference number
- Notes

---

### A8. Basic Dashboard

Dashboard cards:

- Total candidates
- New applications
- Assessment scheduled
- Accepted
- Waiting list
- Rejected
- Documents pending
- Submitted to QFA/FIFA
- Joined team

Simple charts:

- Candidates by status
- Candidates by position
- Candidates by year of birth

---

### A9. Excel Export

Export:

- Candidate list
- Accepted players
- Waiting list
- Rejected players
- Missing documents list

---

## Phase A Deliverables

At the end of Phase A, the client receives:

1. Working Laravel web application
2. Admin login
3. Candidate registration form
4. Candidate profile page
5. Candidate status tracking
6. Basic assessment notes
7. Basic document checklist
8. Basic QFA/FIFA tracking
9. Basic dashboard
10. Excel export
11. Deployment to server
12. Basic admin training

---

## Phase A Timeline

| Week   | Work                                                     |
| ------ | -------------------------------------------------------- |
| Week 1 | Requirement confirmation, database design, Laravel setup |
| Week 2 | Authentication, roles, candidate registration            |
| Week 3 | Candidate status, assessment module                      |
| Week 4 | Document checklist, QFA/FIFA tracking                    |
| Week 5 | Dashboard, export, testing                               |
| Week 6 | Deployment, fixes, training                              |

Estimated duration: **4–6 weeks**

---

# Phase B — Full Internal Recruitment Management System

## Objective

Upgrade the MVP into a complete internal recruitment management system.

Phase B focuses on improving control, history, reporting, document management, communication, and joining workflow.

---

## Phase B Scope

### B1. Advanced Candidate Profile

Enhance candidate profile to include:

- Full personal information
- Parent/guardian information
- Assessment history
- Status history
- Document progress
- QFA/FIFA submission history
- Communication history
- Joining progress
- Internal notes
- Attachments

---

### B2. Advanced Status Workflow

Replace simple status update with structured workflow.

Recommended full workflow:

1. New Application
2. Application Reviewed
3. Contacted
4. Assessment Scheduled
5. Assessment Attended
6. Assessment Missed
7. Assessment Completed
8. Accepted
9. Waiting List
10. Rejected
11. Documents Requested
12. Documents In Progress
13. Documents Completed
14. Ready for QFA
15. Submitted to QFA
16. QFA Returned
17. QFA Approved
18. Ready for FIFA
19. Submitted to FIFA
20. FIFA Returned
21. FIFA Approved
22. Ready to Join
23. Joined Team
24. Withdrawn / Inactive

Each status change should include:

- Status
- Date/time
- Updated by
- Notes
- Optional attachment

---

### B3. Audit Trail / Activity Log

Track important actions:

- Candidate created
- Candidate updated
- Status changed
- Assessment added
- Document uploaded
- Document approved/rejected
- QFA/FIFA status updated
- Team assigned
- User logged in
- Report exported

This protects the academy from confusion like:

“Who changed this candidate to accepted?”
“When was this document uploaded?”
“Why was the player moved to waiting list?”

---

### B4. Full Assessment Management

Enhance assessment module.

Features:

- Create assessment sessions
- Assign candidates to sessions
- Assign coach/assessor
- Attendance status
- Coach evaluation form
- Multiple ratings:
  - Technical skill
  - Physical ability
  - Tactical understanding
  - Discipline
  - Teamwork
  - Potential

- Coach recommendation
- Final admin decision
- Assessment comments
- Attachment support, if needed

---

### B5. Full Document Management

Improve document module from simple checklist to full management.

Features:

- Define document types from settings
- Required/optional documents
- Document status:
  - Pending
  - Uploaded
  - Under Review
  - Approved
  - Rejected
  - Expired

- Expiry date
- Rejection reason
- Version history
- Download document
- Download all player documents
- Document completion percentage

Example:

**Player Document Completion: 75%**

This helps staff immediately know if a player is ready for QFA/FIFA submission.

---

### B6. QFA/FIFA Submission Workflow

Separate QFA and FIFA workflows properly.

QFA workflow:

1. Not Started
2. Preparing Documents
3. Ready for Submission
4. Submitted to QFA
5. Returned for Correction
6. Resubmitted
7. Approved
8. Completed

FIFA workflow:

1. Not Started
2. Preparing Documents
3. Ready for Submission
4. Submitted to FIFA
5. Returned for Correction
6. Resubmitted
7. Approved
8. Completed

Each submission should include:

- Submission date
- Submitted by
- Reference number
- Status
- Notes
- Attachments
- Approval date

---

### B7. Team Joining Workflow

Add full joining process after approval.

Features:

- Assign player to age group
- Assign player to team
- Assign coach
- Set joining date
- Training start date
- Kit/uniform status
- Orientation status
- Registration completed status
- Joining checklist
- Final “Joined Team” status

Example joining checklist:

| Item                    | Status  |
| ----------------------- | ------- |
| Documents completed     | Done    |
| QFA/FIFA approval       | Done    |
| Team assigned           | Done    |
| Coach informed          | Done    |
| Training start date set | Pending |
| Kit/uniform provided    | Pending |

---

### B8. Communication Log

Track communications with parents/players.

Features:

- Add call log
- Add WhatsApp note
- Add email note
- Add meeting note
- Store communication date
- Staff member
- Communication result
- Follow-up date

This is very important because many recruitment issues happen through phone/WhatsApp.

---

### B9. Email Templates

Add reusable templates:

- Assessment invitation
- Accepted player message
- Waiting list message
- Rejection message
- Missing document request
- QFA/FIFA update
- Joining instruction

In Phase B, the system may generate the message/template. Actual WhatsApp/SMS sending can remain manual or be added in Phase C.

---

### B10. Advanced Dashboard

Add richer dashboard views.

Dashboard sections:

- Recruitment pipeline
- Candidate status distribution
- Assessment attendance
- Accepted vs rejected
- Missing documents
- QFA pending submissions
- FIFA pending submissions
- Players ready to join
- Players joined this season
- Candidates by nationality
- Candidates by position
- Candidates by age group/team

---

### B11. Reports

Reports:

- Candidate master report
- Accepted players report
- Waiting list report
- Rejected players report
- Missing documents report
- Assessment report
- QFA submission report
- FIFA submission report
- Joined players report
- Recruitment summary report

Export formats:

- Excel
- PDF

---

### B12. Google Form / Excel Import

Allow admin to import old Google Form data.

Features:

- Upload Excel/CSV
- Map columns
- Validate data
- Avoid duplicates
- Import candidates
- Show import result:
  - Imported
  - Skipped
  - Failed

This is important because they already use Google Forms.

---

### B13. Advanced Search and Filters

Search/filter by:

- Player name
- Parent phone
- WhatsApp
- Nationality
- Year of birth
- Age group
- Position
- School
- Previous club
- Status
- Assessment result
- Document completion
- QFA status
- FIFA status
- Team
- Season

---

## Phase B Deliverables

At the end of Phase B, the client receives:

1. Advanced candidate profile
2. Full recruitment workflow
3. Status history
4. Activity log
5. Full assessment sessions
6. Full document management
7. Document completion percentage
8. Full QFA workflow
9. Full FIFA workflow
10. Team joining workflow
11. Communication log
12. Email/message templates
13. Advanced dashboard
14. PDF reports
15. Advanced Excel reports
16. Google Form / Excel import
17. Advanced search and filtering
18. Updated training session
19. Updated user guide

---

## Phase B Timeline

| Week   | Work                                               |
| ------ | -------------------------------------------------- |
| Week 1 | Workflow enhancement, status history, activity log |
| Week 2 | Advanced assessment sessions                       |
| Week 3 | Full document management                           |
| Week 4 | QFA/FIFA workflow and joining workflow             |
| Week 5 | Reports, dashboard, import                         |
| Week 6 | Testing, training, deployment update               |

Estimated duration after Phase A: **4–6 weeks**

---

# Phase C — Parent/Player Portal & Automation

## Objective

Allow parents/players to participate directly in the recruitment process.

Phase C reduces admin workload by allowing parents to upload documents, see missing requirements, receive reminders, and check application progress.

---

## Phase C Scope

### C1. Parent/Player Portal

Create a separate portal for parents/players.

Features:

- Parent login
- View player profile
- View application status
- View assessment appointment
- View required documents
- Upload missing documents
- View document review status
- View academy messages
- Update contact information, if allowed

---

### C2. Parent Account Creation

Options:

1. Admin creates parent account manually
2. System creates parent account after candidate registration
3. Parent receives invitation link
4. Parent sets password

Recommended for first release:

**Admin sends invitation link after candidate is accepted or documents are requested.**

This prevents too many unnecessary parent accounts for rejected applicants.

---

### C3. Parent Document Upload

Parents can upload documents directly.

Features:

- Upload passport
- Upload QID
- Upload birth certificate
- Upload photo
- Upload other required documents
- See status:
  - Pending
  - Uploaded
  - Approved
  - Rejected

- See rejection reason
- Re-upload corrected document

Admin still approves or rejects documents.

---

### C4. Parent Application Progress View

Parent can see simplified progress.

Example:

1. Application Received
2. Assessment Scheduled
3. Assessment Completed
4. Accepted
5. Documents Required
6. Under QFA/FIFA Process
7. Approved
8. Ready to Join

Do not expose internal notes to parents.

---

### C5. Automated Reminders

Add automated reminders for:

- Missing documents
- Rejected documents
- Assessment appointment
- QFA/FIFA pending requirements
- Joining instructions

Notification channels:

- Email
- In-app notification
- SMS/WhatsApp integration-ready

---

### C6. WhatsApp/SMS Integration Readiness

Prepare system for external provider integration.

Possible features:

- WhatsApp message template
- Send WhatsApp manually by link
- Store WhatsApp communication log
- Later connect to WhatsApp Business API provider
- SMS provider integration, if needed

Important: actual WhatsApp Business API may require provider approval, templates, and additional monthly/usage fees.

---

### C7. Multi-Season Management

Enhance season management.

Features:

- Create recruitment season
- Assign candidates to season
- Compare seasons
- Archive previous season
- Duplicate document requirements by season
- Season-based dashboard

Example:

- 2026/27 Season
- 2027/28 Season

---

### C8. Parent Portal Security

Security requirements:

- Parent can only view their own child/player
- Secure document upload
- File type validation
- File size limit
- Private file storage
- Password reset
- Email verification, if required
- Terms/consent checkbox, if required

---

### C9. Public Registration Page Enhancement

Improve public form.

Features:

- Branded registration page
- Mobile-friendly
- Field validation
- Confirmation message
- Optional email confirmation
- Optional duplicate check
- Optional consent checkbox
- Optional file pre-upload, if needed

---

### C10. Advanced Notifications

Internal staff notifications:

- New candidate registered
- Candidate accepted
- Parent uploaded document
- Document rejected
- Player ready for QFA
- Player ready for FIFA
- Player ready to join

Parent notifications:

- Assessment scheduled
- Document missing
- Document approved/rejected
- Joining instructions

---

## Phase C Deliverables

At the end of Phase C, the client receives:

1. Parent/player portal
2. Parent login
3. Parent document upload
4. Parent application progress screen
5. Parent notification center
6. Automated reminders
7. WhatsApp/SMS readiness
8. Multi-season management
9. Enhanced public registration form
10. Portal security rules
11. Updated admin dashboard
12. Updated training
13. Updated user guide

---

## Phase C Timeline

| Week   | Work                                          |
| ------ | --------------------------------------------- |
| Week 1 | Parent portal setup, parent accounts          |
| Week 2 | Parent document upload and review workflow    |
| Week 3 | Parent progress view and notifications        |
| Week 4 | WhatsApp/SMS readiness, multi-season features |
| Week 5 | Public form enhancement and security testing  |
| Week 6 | Testing, deployment, training                 |

Estimated duration after Phase B: **4–6 weeks**

---

# Suggested Database Design by Phase

## Phase A Tables

Start with:

1. users
2. roles / permissions
3. candidates
4. candidate_status_histories
5. assessments
6. document_types
7. candidate_documents
8. qfa_fifa_tracking
9. teams
10. seasons

---

## Phase B Additional / Enhanced Tables

Add or expand:

1. assessment_sessions
2. assessment_session_candidates
3. activity_logs
4. communication_logs
5. qfa_submissions
6. fifa_submissions
7. joining_checklists
8. joining_checklist_items
9. report_exports
10. import_batches
11. import_batch_rows

---

## Phase C Additional Tables

Add:

1. parent_accounts
2. parent_player_links
3. parent_notifications
4. document_upload_requests
5. reminder_rules
6. reminder_logs
7. message_templates
8. seasons_enhanced_settings
9. public_registration_settings

---

# Recommended Development Order

## Build Order for Phase A

1. Project setup
2. Authentication
3. Roles
4. Candidate model/database
5. Candidate registration form
6. Candidate list
7. Candidate profile
8. Status update
9. Assessment notes
10. Document checklist
11. QFA/FIFA basic tracking
12. Dashboard
13. Excel export
14. Testing
15. Deployment

---

## Build Order for Phase B

1. Status history enhancement
2. Activity log
3. Advanced candidate profile
4. Assessment session scheduling
5. Advanced document management
6. QFA submission module
7. FIFA submission module
8. Team joining module
9. Communication log
10. Templates
11. Advanced dashboard
12. Reports
13. Import module
14. Testing
15. Deployment update

---

## Build Order for Phase C

1. Parent portal authentication
2. Parent/player account linking
3. Parent dashboard
4. Parent document upload
5. Document review feedback
6. Parent progress tracking
7. Notifications
8. Reminder system
9. WhatsApp/SMS readiness
10. Multi-season management
11. Testing
12. Deployment update

---

# Testing Plan

## Phase A Testing

Test:

- Login
- Candidate registration
- Candidate edit
- Status update
- Assessment note
- Document upload
- QFA/FIFA status
- Dashboard counts
- Excel export

---

## Phase B Testing

Test:

- Status history
- Activity log
- Assessment sessions
- Multiple document statuses
- Document completion percentage
- QFA workflow
- FIFA workflow
- Joining checklist
- Communication log
- Reports
- Excel/CSV import
- Advanced filters

---

## Phase C Testing

Test:

- Parent login
- Parent permissions
- Parent document upload
- Parent cannot see other candidates
- Parent progress page
- Notifications
- Reminder rules
- Public registration form
- Multi-season separation

---

# Deployment Plan

## Recommended Deployment

Use a VPS or dedicated server.

Server stack:

- Ubuntu Server
- Apache or Nginx
- PHP latest supported by Laravel
- MySQL
- Redis, optional but recommended for queues/cache
- Supervisor for queue workers
- SSL certificate
- Daily backup

Deployment steps:

1. Prepare server
2. Install PHP, MySQL, web server
3. Configure domain and SSL
4. Upload Laravel project
5. Configure `.env`
6. Run migrations
7. Set permissions
8. Configure queue worker
9. Configure scheduler
10. Test login and uploads
11. Run final acceptance testing

---

# Handover Plan

For each phase, provide:

1. Admin account
2. Source code handover
3. Database structure
4. Deployment notes
5. User guide
6. Training session
7. Known limitations
8. Future improvement list

---

# Commercial Structure by Phase

Since the client wants quick implementation, you can present the project like this:

| Phase   | Description                                  |                 Price |
| ------- | -------------------------------------------- | --------------------: |
| Phase A | MVP Recruitment System                       |            QAR 12,000 |
| Phase B | Full Internal Recruitment Management Upgrade |  Additional QAR 6,000 |
| Phase C | Parent Portal & Automation Upgrade           | Additional QAR 10,000 |

Total:

| Package         |      Total |
| --------------- | ---------: |
| Phase A only    | QAR 12,000 |
| Phase A + B     | QAR 18,000 |
| Phase A + B + C | QAR 28,000 |

This keeps the same prices from your proposal while making the delivery roadmap clearer.

---

# Recommended Client Message

You can explain it to the client like this:

> For quick implementation, I recommend starting with Phase A, which delivers the core recruitment system and solves the immediate problems of candidate tracking, assessment status, document checklist, and basic QFA/FIFA tracking.
>
> The system will be designed from the beginning to support future upgrades. Phase B can then add the full internal workflow, advanced reporting, communication log, document completion percentage, QFA/FIFA detailed workflow, and team joining process.
>
> Phase C can be implemented later if the academy wants parents or players to log in directly, upload documents, and receive automated reminders. This phased approach allows the academy to launch quickly, reduce cost and risk, and expand the system based on actual operational needs.

---

# Final Recommendation

For implementation, I recommend:

**Start with Phase A immediately.**
Design the database and structure to support **Phase B and Phase C later**.
Do not build Phase C first because the parent portal will increase complexity, testing, and support requirements.

Best path:

**Phase A now → Phase B after staff feedback → Phase C after workflow is stable.**
