# Effective Output: Getting Maximum Quality from Claude AI

A comprehensive guide for developers, engineers, and technical users.

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Core Principles for Getting Accurate Output](#2-core-principles-for-getting-accurate-output)
3. [The Optimal Prompt Structure](#3-the-optimal-prompt-structure)
4. [How to Use Context Effectively](#4-how-to-use-context-effectively)
5. [Using Skills / Specialized Instructions](#5-using-skills--specialized-instructions)
6. [Prompting Strategies Used by Professionals](#6-prompting-strategies-used-by-professionals)
7. [Debugging with Claude](#7-debugging-with-claude)
8. [How to Ask Claude for High Quality Code](#8-how-to-ask-claude-for-high-quality-code)
9. [How to Improve Claude Responses](#9-how-to-improve-claude-responses)
10. [Real World Prompt Examples](#10-real-world-prompt-examples)
11. [Common Mistakes When Prompting AI](#11-common-mistakes-when-prompting-ai)
12. [A Universal Prompt Template](#12-a-universal-prompt-template)
13. [Conclusion](#13-conclusion)

---

## 1. Introduction

### What Is Prompt Engineering?

Prompt engineering is the practice of crafting instructions that guide an AI model to produce the most accurate, relevant, and useful output possible. Think of it as writing a precise specification for a highly capable but literal-minded collaborator.

Just as a well-written ticket leads to better code, a well-written prompt leads to better AI output.

### Why Prompt Structure Matters

Claude processes your prompt as a complete unit of meaning. The way you organize information directly affects:

- **Accuracy** — Structured prompts reduce ambiguity, leading to fewer hallucinations and incorrect assumptions.
- **Relevance** — Clear constraints keep the response focused on what you actually need.
- **Completeness** — Providing the right context ensures Claude doesn't fill gaps with guesses.
- **Consistency** — A repeatable structure produces reliably high-quality results across different tasks.

### How AI Interprets Instructions

Claude reads your entire prompt and builds an internal representation of what you're asking. Key things to understand:

1. **Order matters.** Information presented first sets the frame for everything that follows.
2. **Specificity wins.** "Optimize this SQL query for read-heavy workloads on PostgreSQL 15" beats "make this faster."
3. **Implicit is risky.** Claude will try to infer what you mean, but explicit instructions eliminate guesswork.
4. **Context is king.** Claude has no memory of your project unless you provide it. Every conversation starts fresh.

---

## 2. Core Principles for Getting Accurate Output

### 2.1 Clarity

Say exactly what you mean. Avoid ambiguous words like "handle," "deal with," or "fix up."

```
Bad:  "Handle the user authentication."
Good: "Implement a login endpoint that accepts email and password,
       validates credentials against the users table, and returns
       a JWT token on success or a 401 error on failure."
```

### 2.2 Specificity

The more specific you are, the more precise the output.

```
Bad:  "Write a function to process data."
Good: "Write a TypeScript function called `parseTransactionCSV` that:
       - Accepts a CSV string with columns: date, amount, description, category
       - Returns an array of Transaction objects
       - Skips rows where amount is not a valid number
       - Parses dates in DD/MM/YYYY format to ISO 8601"
```

### 2.3 Constraints

Constraints act as guardrails that prevent Claude from going off track.

Useful constraints include:

- **Language/framework**: "Use Laravel 11 with Eloquent ORM"
- **Style**: "Follow PSR-12 coding standards"
- **Performance**: "Must handle 10,000 concurrent users"
- **Length**: "Keep the response under 50 lines"
- **Exclusions**: "Do not use any third-party packages"

### 2.4 Context

Always provide the relevant surrounding information. Claude cannot see your codebase, database, or environment unless you share it.

Essential context includes:

- What the project does
- What tech stack you're using
- What you've already tried
- What the expected vs. actual behavior is

### 2.5 Step-by-Step Thinking

For complex problems, explicitly ask Claude to reason through the problem before answering.

```
"Before writing the code, first:
1. Identify the root cause of the N+1 query problem
2. List the relationships involved
3. Propose the eager loading strategy
4. Then write the optimized code"
```

This technique (chain-of-thought prompting) dramatically improves accuracy on multi-step problems.

### 2.6 Examples

Providing input/output examples is one of the most powerful techniques available.

```
"Convert these API responses to the new format.

Example input:
{ "user_name": "john_doe", "user_email": "john@example.com" }

Example output:
{ "name": "john_doe", "email": "john@example.com", "source": "api_v1" }

Now convert this:
{ "user_name": "jane_smith", "user_email": "jane@example.com" }"
```

---

## 3. The Optimal Prompt Structure

The most effective prompts follow a consistent structure. Here are the six components:

### The Six Components

| Component | Purpose | Example |
|-----------|---------|---------|
| **Role** | Sets Claude's expertise level and perspective | "You are a senior Laravel backend engineer" |
| **Context** | Provides background information | "We have a multi-tenant SaaS application using Laravel 11..." |
| **Task** | Defines exactly what you need | "Refactor the OrderService to use the Repository pattern" |
| **Constraints** | Sets boundaries and requirements | "Must maintain backward compatibility with existing API contracts" |
| **Expected Output** | Describes the format of the response | "Provide the complete refactored class with inline comments explaining each change" |
| **Examples** | Shows what good output looks like | "Similar to how UserRepository is structured in our codebase..." |

### Reusable Prompt Template

Copy and fill in this template for any development task:

```markdown
## Role
You are a [specific expertise]. You have deep experience with [relevant technologies].

## Context
- Project: [brief description]
- Tech stack: [languages, frameworks, databases]
- Current situation: [what exists now]
- Problem: [what's wrong or what's needed]

## Task
[Clearly state what you need Claude to do]

## Constraints
- [Constraint 1]
- [Constraint 2]
- [Constraint 3]

## Expected Output
[Describe the format, length, and structure of the response you want]

## Examples (optional)
[Provide input/output examples if applicable]
```

### Template in Action

```markdown
## Role
You are a senior backend engineer specializing in Laravel and RESTful API design.

## Context
- Project: HealthKeeper — a patient management system
- Tech stack: Laravel 11, PHP 8.3, MySQL 8, Redis
- Current situation: The appointment booking endpoint allows double-booking
- Problem: Two patients can book the same time slot simultaneously

## Task
Implement a race-condition-safe appointment booking system that prevents
double-booking using database-level locking.

## Constraints
- Use pessimistic locking with MySQL SELECT ... FOR UPDATE
- Must work within Laravel's Eloquent ORM
- Must return appropriate HTTP status codes (409 for conflicts)
- Must be wrapped in a database transaction
- Include validation for: doctor_id, patient_id, appointment_time

## Expected Output
- The complete controller method
- The request validation class
- The service class with the locking logic
- A brief explanation of why this approach prevents race conditions
```

---

## 4. How to Use Context Effectively

Context is the single most impactful factor in output quality. Here's how to provide it for different scenarios.

### 4.1 Project Description Context

Always start with what your project does:

```
"I'm building HealthKeeper, a hospital management system that handles
patient records, appointment scheduling, billing, and pharmacy inventory.
It's a multi-tenant system where each hospital is a separate tenant."
```

### 4.2 Tech Stack Context

Be explicit about versions and tools:

```
"Tech stack:
- Backend: Laravel 11, PHP 8.3
- Frontend: React 18 with TypeScript
- Database: MySQL 8.0
- Cache: Redis 7
- Queue: Laravel Horizon
- Auth: Laravel Sanctum
- Deployment: Docker on AWS ECS"
```

### 4.3 Current Code Context

When asking about existing code, include the relevant source:

```
"Here is my current controller method that has a bug:

```php
public function store(Request $request)
{
    $appointment = Appointment::create($request->all());
    return response()->json($appointment, 201);
}
```

The problem is that it doesn't validate input and allows mass assignment."
```

### 4.4 Error Message Context

Always include the full error, not a summary:

```
"I'm getting this error when running php artisan migrate:

SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'appointments'
already exists (Connection: mysql, SQL: create table `appointments` ...)

Migration file: 2024_01_15_create_appointments_table.php
I have not modified the migration after the initial creation."
```

### Real-World Context Examples

#### Laravel Example

```markdown
## Context
I have a Laravel 11 application with these models:

- Patient (id, name, email, phone, tenant_id)
- Appointment (id, patient_id, doctor_id, scheduled_at, status, tenant_id)
- Doctor (id, name, specialization, tenant_id)

Relationships:
- Patient hasMany Appointments
- Doctor hasMany Appointments
- Appointment belongsTo Patient, belongsTo Doctor

## Task
Write an Eloquent query that returns all doctors who have more than 5
appointments this week, ordered by appointment count descending.
Include the appointment count and the doctor's specialization.
```

#### React Example

```markdown
## Context
I have a React 18 component using TypeScript:

```tsx
const AppointmentList: React.FC = () => {
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAppointments().then(data => {
      setAppointments(data);
      setLoading(false);
    });
  }, []);

  // Component re-renders unnecessarily when parent state changes
  // even though appointments haven't changed
};
```

## Task
Optimize this component to prevent unnecessary re-renders.
Use React.memo, useMemo, or useCallback as appropriate.
Explain which optimization applies and why.
```

#### API Development Example

```markdown
## Context
I'm building a REST API for a pharmacy module with these endpoints:

- GET    /api/v1/medicines        (list with pagination)
- POST   /api/v1/medicines        (create)
- GET    /api/v1/medicines/{id}   (show)
- PUT    /api/v1/medicines/{id}   (update)
- DELETE /api/v1/medicines/{id}   (soft delete)

Current response format:
{ "data": {...}, "message": "Success", "status": 200 }

## Task
Design the medicine stock management endpoints that handle:
- Stock in (receiving inventory)
- Stock out (dispensing to patients)
- Stock adjustment (corrections)
- Stock transfer (between pharmacy locations)

Follow the same response format and RESTful conventions.
```

#### SQL Debugging Example

```markdown
## Context
This MySQL query takes 12 seconds on a table with 2 million rows:

```sql
SELECT p.name, p.email,
       COUNT(a.id) as total_appointments,
       MAX(a.scheduled_at) as last_appointment
FROM patients p
LEFT JOIN appointments a ON a.patient_id = p.id
WHERE p.tenant_id = 5
  AND a.status = 'completed'
  AND a.scheduled_at >= '2025-01-01'
GROUP BY p.id
ORDER BY total_appointments DESC
LIMIT 20;
```

Table indexes:
- patients: PRIMARY (id), INDEX (tenant_id)
- appointments: PRIMARY (id), INDEX (patient_id)

EXPLAIN shows a full table scan on appointments.

## Task
Optimize this query. Suggest index changes and query restructuring.
Show the EXPLAIN output I should expect after optimization.
```

---

## 5. Using Skills / Specialized Instructions

You can dramatically improve output quality by telling Claude to adopt a specific expert persona. This primes the model to use domain-specific knowledge and reasoning patterns.

### Senior Backend Engineer

```markdown
You are a senior backend engineer with 15 years of experience building
high-traffic applications. You specialize in Laravel, PHP, and MySQL.

You always consider:
- Performance implications of every query
- Security vulnerabilities (SQL injection, XSS, CSRF)
- Edge cases and error handling
- Database indexing strategy
- Caching opportunities
- Code maintainability

Review the following service class and identify all issues:

[paste code here]
```

### Database Performance Expert

```markdown
You are a database performance expert specializing in MySQL and PostgreSQL
optimization for high-traffic applications.

When analyzing queries, you always:
- Check for missing indexes
- Identify N+1 query problems
- Look for full table scans
- Evaluate JOIN strategies
- Consider query plan caching
- Suggest denormalization when appropriate

Analyze this query that runs on a table with 10 million rows:

[paste query and schema here]
```

### Security Engineer

```markdown
You are a senior application security engineer conducting a code review.
You are an expert in OWASP Top 10 vulnerabilities.

Review the following Laravel controller for security vulnerabilities.
For each vulnerability found:
1. Identify the vulnerability type (e.g., SQL injection, XSS, IDOR)
2. Explain the attack vector
3. Rate the severity (Critical / High / Medium / Low)
4. Provide the fixed code

[paste code here]
```

### System Architect

```markdown
You are a senior system architect designing scalable distributed systems.
You have deep experience with microservices, event-driven architecture,
and cloud-native applications.

## Context
We're scaling our monolithic Laravel application that currently handles
1,000 requests/minute. We expect 50,000 requests/minute within 6 months.

## Task
Design a migration strategy from monolith to microservices.
Include:
- Service boundaries based on domain-driven design
- Communication patterns (sync vs async)
- Data ownership and consistency strategy
- Migration phases with rollback plans
- Infrastructure requirements
```

### Combining Multiple Roles

For complex tasks, you can combine expertise:

```markdown
You are both a senior Laravel developer and a database optimization expert.

Review this Eloquent query from both perspectives:
1. As a Laravel developer: Is the Eloquent usage idiomatic and maintainable?
2. As a DBA: What are the performance implications at scale?

Provide recommendations from both perspectives, noting where they conflict.

[paste code here]
```

---

## 6. Prompting Strategies Used by Professionals

### 6.1 Chain-of-Thought Prompting

Ask Claude to show its reasoning process before giving a final answer. This improves accuracy on complex problems.

```markdown
I have a race condition in my appointment booking system. Before proposing
a solution, please:

1. Identify all the places where concurrent requests could cause conflicts
2. Analyze what database operations are not atomic
3. List possible locking strategies (optimistic vs pessimistic)
4. Evaluate trade-offs of each strategy for our use case (high read, low write)
5. Then recommend and implement the best solution
```

### 6.2 Step-by-Step Reasoning

Break complex tasks into explicit steps:

```markdown
I need to implement a role-based access control (RBAC) system in Laravel.

Walk me through this step by step:

Step 1: Design the database schema (roles, permissions, pivot tables)
Step 2: Create the Eloquent models and relationships
Step 3: Build the middleware for route protection
Step 4: Create a Gate/Policy implementation
Step 5: Build an artisan command to seed default roles
Step 6: Show how to use it in controllers and Blade templates

Complete each step fully before moving to the next.
```

### 6.3 Decomposition Prompts

For large tasks, ask Claude to break the problem down first:

```markdown
I need to build a complete inventory management module for a pharmacy system.

Before writing any code:
1. Break this into independent sub-tasks
2. Identify dependencies between sub-tasks
3. Suggest an implementation order
4. Estimate the complexity of each sub-task (simple / medium / complex)

Then implement sub-task #1 completely.
```

### 6.4 Iterative Prompting

Build on previous responses in a structured way:

```
Prompt 1: "Design the database schema for a pharmacy inventory system"
Prompt 2: "Now create the Eloquent models based on that schema"
Prompt 3: "Now build the repository layer for the Medicine model"
Prompt 4: "Now create the API controller using that repository"
Prompt 5: "Now write feature tests for the controller"
```

Each prompt builds on the previous output, maintaining consistency and allowing you to correct course at each step.

### 6.5 Self-Check Prompts

Ask Claude to verify its own work:

```markdown
Write a function that calculates the optimal appointment schedule for
a doctor given their availability and existing appointments.

After writing the code:
1. Trace through it with this test case: [provide test data]
2. Identify any edge cases that would break it
3. Check for off-by-one errors in time calculations
4. Verify the time complexity is acceptable for 1000 appointments/day
5. Fix any issues you find
```

### 6.6 Constraint Tightening

Start broad, then tighten constraints:

```
Prompt 1: "Design an authentication system for a multi-tenant SaaS app"
Prompt 2: "Now add these constraints: must support SSO via SAML 2.0,
           must enforce 2FA for admin roles, session timeout of 30 minutes"
Prompt 3: "Now make it work with Laravel Sanctum and ensure all tokens
           are tenant-scoped"
```

### 6.7 Adversarial Prompting

Ask Claude to find flaws in its own solutions:

```markdown
Here is the authentication flow you just designed.

Now act as a penetration tester. Try to find:
1. Authentication bypass vulnerabilities
2. Token theft scenarios
3. Session fixation possibilities
4. Privilege escalation paths
5. Rate limiting gaps

For each vulnerability found, provide the fix.
```

---

## 7. Debugging with Claude

Effective debugging prompts follow a consistent pattern: **What happened, what should happen, and what you've already tried.**

### The Debugging Prompt Template

```markdown
## Bug Description
[What is going wrong]

## Expected Behavior
[What should happen instead]

## Error Output
```
[Paste the full error message, stack trace, or unexpected output]
```

## Relevant Code
```[language]
[Paste the relevant code — not the entire file, just the relevant parts]
```

## Environment
- [Language/framework version]
- [OS, browser, or runtime]
- [Any relevant configuration]

## What I've Already Tried
- [Attempt 1 and result]
- [Attempt 2 and result]
```

### JavaScript Error Example

```markdown
## Bug Description
The `calculateTotal` function returns NaN when the cart has items
with discount percentages.

## Expected Behavior
Should return the correct total after applying percentage discounts.

## Error Output
```
calculateTotal([{price: 100, discount: "10%"}])
// Returns: NaN
// Expected: 90
```

## Relevant Code
```javascript
function calculateTotal(items) {
  return items.reduce((total, item) => {
    const discount = item.discount || 0;
    return total + (item.price - (item.price * discount));
  }, 0);
}
```

## What I've Already Tried
- Confirmed item.price is always a number
- The issue only occurs when discount is a string like "10%"
```

### React State Issue Example

```markdown
## Bug Description
My component shows stale data after a mutation. The appointment list
doesn't update after creating a new appointment, but refreshing the
page shows the correct data.

## Expected Behavior
After POST /api/appointments succeeds, the appointment list should
immediately show the new appointment.

## Relevant Code
```tsx
// AppointmentList.tsx
const AppointmentList = () => {
  const [appointments, setAppointments] = useState([]);

  useEffect(() => {
    fetch('/api/appointments')
      .then(res => res.json())
      .then(data => setAppointments(data));
  }, []);

  return appointments.map(a => <AppointmentCard key={a.id} {...a} />);
};

// CreateAppointment.tsx (sibling component)
const CreateAppointment = () => {
  const handleSubmit = async (data) => {
    await fetch('/api/appointments', {
      method: 'POST',
      body: JSON.stringify(data)
    });
    // After this, AppointmentList doesn't update
  };
};
```

## Environment
- React 18.2, TypeScript 5.3
- No state management library (useState only)

## What I've Already Tried
- Confirmed the POST request succeeds (201 response)
- Confirmed new data appears on page refresh
```

### Laravel Backend Error Example

```markdown
## Bug Description
The patient search endpoint returns a 500 error when searching by
phone number with a "+" prefix (international format).

## Expected Behavior
Should return matching patients regardless of phone number format.

## Error Output
```
SQLSTATE[42000]: Syntax error or access violation: 1064
You have an error in your SQL syntax near '+1234567890'
(Connection: mysql, SQL: select * from patients
where phone LIKE +1234567890%)
```

## Relevant Code
```php
public function search(Request $request)
{
    $query = $request->input('q');
    $patients = Patient::where('phone', 'LIKE', $query . '%')->get();
    return response()->json($patients);
}
```

## Environment
- Laravel 11, PHP 8.3, MySQL 8.0

## What I've Already Tried
- Works fine with regular numeric searches
- Only fails when "+" is in the search string
```

### SQL Query Optimization Example

```markdown
## Bug Description
This report query takes 45 seconds to complete.

## Expected Behavior
Should complete within 2 seconds for the dashboard.

## Current Query
```sql
SELECT
    d.name AS doctor_name,
    d.specialization,
    COUNT(a.id) AS total_appointments,
    COUNT(CASE WHEN a.status = 'completed' THEN 1 END) AS completed,
    COUNT(CASE WHEN a.status = 'cancelled' THEN 1 END) AS cancelled,
    AVG(TIMESTAMPDIFF(MINUTE, a.scheduled_at, a.completed_at)) AS avg_duration
FROM doctors d
LEFT JOIN appointments a ON a.doctor_id = d.id
WHERE a.scheduled_at BETWEEN '2025-01-01' AND '2025-12-31'
  AND d.tenant_id = 3
GROUP BY d.id
ORDER BY total_appointments DESC;
```

## Table Sizes
- doctors: 500 rows
- appointments: 5 million rows

## Current Indexes
- appointments: PRIMARY(id), INDEX(doctor_id), INDEX(patient_id)

## EXPLAIN Output
```
+----+------+-------+------+---------+------+----------+------+
| id | type | table | rows | key     | Extra                  |
+----+------+-------+------+---------+------+----------+------+
|  1 | ALL  | a     | 5M   | NULL    | Using where; Using tmp |
+----+------+-------+------+---------+------+----------+------+
```

## Task
1. Explain why this query is slow
2. Suggest optimal indexes
3. Rewrite the query if needed
4. Show the expected EXPLAIN after optimization
```

---

## 8. How to Ask Claude for High Quality Code

### 8.1 Clean Architecture

```markdown
## Role
You are a senior software architect who follows clean architecture principles.

## Task
Implement the appointment booking feature using clean architecture layers:

1. **Entity Layer**: Appointment domain model with business rules
2. **Use Case Layer**: BookAppointment use case with validation logic
3. **Interface Layer**: Repository interface and API controller
4. **Infrastructure Layer**: Eloquent repository implementation

## Constraints
- Each layer must only depend on inner layers (dependency rule)
- Use dependency injection throughout
- Business logic must not reference Eloquent or HTTP concerns
- Include interfaces for all external dependencies
```

### 8.2 Best Practices

```markdown
Write a Laravel API resource controller for managing medicines.

Follow these best practices:
- Form Request classes for validation
- API Resources for response transformation
- Repository pattern for data access
- Service layer for business logic
- Proper HTTP status codes (200, 201, 204, 404, 422)
- Consistent JSON response format: { data, message, status }
- Authorization using Laravel Policies
- Pagination for list endpoints
```

### 8.3 Scalable Code

```markdown
## Task
Build a notification system that can handle 100,000 notifications per hour.

## Requirements
- Must support multiple channels: email, SMS, push, in-app
- Must be asynchronous (queue-based)
- Must support notification preferences per user
- Must allow batching for bulk notifications
- Must have retry logic with exponential backoff
- Must log all notification attempts for audit

## Constraints
- Use Laravel queues with Redis driver
- Each channel should be an independent job
- Design for horizontal scaling (multiple queue workers)
- Include a circuit breaker for external services (SMS, email APIs)
```

### 8.4 Secure Code

```markdown
## Role
You are a security-focused backend developer.

## Task
Implement user registration and login for a healthcare application.

## Security Requirements
- Hash passwords with bcrypt (cost factor 12)
- Implement rate limiting: 5 attempts per minute per IP
- Validate and sanitize all inputs
- Use parameterized queries (no raw SQL)
- Return generic error messages (don't reveal if email exists)
- Implement CSRF protection
- Set secure cookie flags (HttpOnly, Secure, SameSite)
- Log all authentication attempts with IP and user agent
- Implement account lockout after 10 failed attempts

## Compliance
- Must comply with HIPAA data handling requirements
- All PHI must be encrypted at rest
```

### 8.5 Optimized Queries

```markdown
## Context
Table: appointments (12 million rows)
Columns: id, patient_id, doctor_id, tenant_id, scheduled_at, status,
         created_at, updated_at

Common queries:
1. List appointments for a doctor on a specific date (most frequent)
2. Count appointments by status for a tenant this month
3. Find available time slots for a doctor

## Task
Design the optimal indexing strategy. For each index:
- Explain why it's needed
- Show which query it optimizes
- Estimate the performance improvement
- Show any covering index opportunities

Also refactor any queries that could benefit from restructuring.
```

---

## 9. How to Improve Claude Responses

### 9.1 Self-Critique

After receiving an answer, ask Claude to evaluate it:

```markdown
Review the code you just wrote and critique it:

1. What are the potential failure points?
2. Are there any security vulnerabilities?
3. How would this perform with 1 million records?
4. Is the error handling comprehensive?
5. What would a senior developer flag in code review?

Then provide an improved version addressing each issue.
```

### 9.2 Alternative Solutions

```markdown
You just gave me Solution A (using database locks).

Now provide two alternative approaches:
- Solution B: Using a queue-based approach
- Solution C: Using an optimistic concurrency approach

For each, list:
- Pros and cons
- Performance characteristics
- Complexity to implement
- Best use case scenario

Then recommend which one is best for my situation
(high read, moderate write, 5,000 concurrent users).
```

### 9.3 Optimization Requests

```markdown
The code you wrote works correctly. Now optimize it for production:

1. Identify any N+1 query problems and fix with eager loading
2. Add caching where appropriate (specify cache keys and TTL)
3. Replace any loops that could use collection methods or bulk operations
4. Add database indexing recommendations
5. Identify anything that should run asynchronously via queues

Show the optimized version with comments explaining each change.
```

### 9.4 Edge Case Analysis

```markdown
Analyze the appointment booking function for edge cases:

1. What happens if two users book the exact same millisecond?
2. What if the doctor's schedule changes during booking?
3. What if the database connection drops mid-transaction?
4. What if scheduled_at is in the past?
5. What if the timezone differs between client and server?
6. What about daylight saving time transitions?
7. What if patient_id or doctor_id references a soft-deleted record?

For each edge case, explain the current behavior and provide a fix.
```

### 9.5 Progressive Refinement

Use a feedback loop to progressively improve output:

```
Step 1: "Write the function"
Step 2: "Add error handling for [specific scenarios]"
Step 3: "Now add input validation"
Step 4: "Add logging for debugging"
Step 5: "Write unit tests covering the edge cases"
```

---

## 10. Real World Prompt Examples

### 10.1 Coding

**Bad prompt:**
```
Write a user model
```

**Good prompt:**
```markdown
## Role
Senior Laravel developer

## Context
Building a multi-tenant healthcare application. Each user belongs to
one tenant (hospital) and can have multiple roles.

## Task
Create the User Eloquent model with:

- Fillable fields: name, email, password, phone, tenant_id, is_active
- Hidden fields: password, remember_token
- Casts: email_verified_at (datetime), is_active (boolean)
- Relationships:
  - belongsTo Tenant
  - belongsToMany Role (via role_user pivot)
  - hasMany AuditLog
- Scopes:
  - active() — where is_active = true
  - forTenant($tenantId) — filtered by tenant
- Accessors:
  - full_name — combines first_name and last_name

## Constraints
- Use Laravel 11 conventions
- Include PHPDoc blocks for all relationships and scopes
- Implement the MustVerifyEmail interface
```

### 10.2 Debugging

**Bad prompt:**
```
My code doesn't work, fix it
```

**Good prompt:**
```markdown
## Bug
The patient search API returns empty results when searching by partial name,
but returns correct results for exact name matches.

## Expected
Searching "Joh" should return patients named "John", "Johnny", "Johnson", etc.

## Actual
Searching "Joh" returns []. Searching "John Smith" returns the correct patient.

## Code
```php
public function search(Request $request)
{
    return Patient::where('name', $request->q)
        ->where('tenant_id', auth()->user()->tenant_id)
        ->limit(20)
        ->get();
}
```

## Task
Identify the bug and provide the corrected code.
Also add sanitization for the search input to prevent SQL injection
(even though Eloquent parameterizes — defense in depth).
```

### 10.3 System Design

**Bad prompt:**
```
Design a notification system
```

**Good prompt:**
```markdown
## Role
Senior system architect with experience in event-driven systems.

## Context
HealthKeeper is a hospital management system serving 50 hospitals.
Each hospital has ~200 staff and ~5,000 active patients.

Current pain point: Notifications are sent synchronously, causing
request timeouts during high-traffic periods (morning shift change).

## Task
Design an asynchronous notification system that supports:
- Channels: Email, SMS, push notification, in-app
- Types: Appointment reminders, lab results ready, prescription alerts,
  system announcements
- User preferences: Each user can enable/disable channels per notification type

## Requirements
- Process 10,000 notifications per minute at peak
- Delivery guarantee: at-least-once for critical notifications
- Retry failed deliveries with exponential backoff
- Dashboard to track delivery rates and failures
- Must integrate with existing Laravel application

## Expected Output
1. Architecture diagram (describe in text)
2. Database schema for notification preferences and logs
3. Queue topology and worker configuration
4. Key code components (interfaces and main service class)
5. Failure handling strategy
```

### 10.4 API Building

**Bad prompt:**
```
Build a REST API for patients
```

**Good prompt:**
```markdown
## Role
Senior API developer following REST best practices.

## Context
- Application: HealthKeeper (multi-tenant healthcare system)
- Auth: Laravel Sanctum (bearer token)
- All endpoints must be tenant-scoped
- Base URL: /api/v1

## Task
Build the complete Patient API module:

### Endpoints
| Method | URI | Description |
|--------|-----|-------------|
| GET | /patients | List with pagination, search, filters |
| POST | /patients | Create patient |
| GET | /patients/{id} | Get patient details with relationships |
| PUT | /patients/{id} | Update patient |
| DELETE | /patients/{id} | Soft delete patient |
| GET | /patients/{id}/appointments | Patient's appointments |
| GET | /patients/{id}/medical-history | Patient's medical records |

### Filters for GET /patients
- search (name, email, phone)
- status (active, inactive)
- gender (male, female)
- age_from, age_to
- sort_by (name, created_at, last_visit)

### Response Format
```json
{
  "data": {},
  "message": "Success",
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150
  }
}
```

### Constraints
- Validate all inputs using Form Request classes
- Transform all responses using API Resource classes
- Use service/repository pattern
- Include OpenAPI/Swagger doc comments
- Rate limit: 60 requests/minute per user
```

---

## 11. Common Mistakes When Prompting AI

### Mistake 1: Vague Prompts

```
Bad:  "Fix my code"
Good: "Fix the TypeError on line 42 of OrderService.php caused by
       calling ->total() on a null $order variable when the order
       is not found in the database."
```

**Why it matters:** Vague prompts force Claude to guess what you mean. Each guess introduces the possibility of irrelevant or incorrect output.

### Mistake 2: Missing Context

```
Bad:  "Write a login function"
Good: "Write a login function for a Laravel 11 API that:
       - Accepts email and password via POST /api/login
       - Validates against the users table
       - Returns a Sanctum token on success
       - Returns 401 with a generic error message on failure
       - Rate limited to 5 attempts per minute"
```

**Why it matters:** Without context, Claude will use defaults that may not match your tech stack, architecture, or requirements.

### Mistake 3: Unclear Expected Output

```
Bad:  "Explain microservices"
Good: "Explain microservices architecture as it applies to breaking apart
       our Laravel monolith. Focus on:
       - How to identify service boundaries
       - Data ownership patterns
       - Communication between services
       Keep it practical with examples from a healthcare domain.
       Limit the response to 500 words."
```

**Why it matters:** Without knowing what format, depth, and focus you want, Claude might write a textbook chapter when you needed a quick briefing.

### Mistake 4: Mixing Multiple Tasks

```
Bad:  "Write the user registration, add email verification, implement
       password reset, set up two-factor authentication, and create
       the admin user management panel"

Good: "Let's build the authentication system step by step.
       Starting with Step 1: Write the user registration endpoint.
       Requirements: [specific requirements]
       I'll ask for the next step after we finalize this one."
```

**Why it matters:** Cramming multiple tasks into one prompt leads to shallow implementation of each. Breaking them apart produces deeper, more thorough output.

### Mistake 5: Not Specifying Constraints

```
Bad:  "Write a database query for the dashboard"
Good: "Write a MySQL query for the admin dashboard that:
       - Returns today's appointment count grouped by status
       - Must execute under 100ms on 5M rows
       - Must use the existing index on (tenant_id, scheduled_at)
       - Must be compatible with MySQL 8.0
       - Use CTEs for readability"
```

**Why it matters:** Constraints prevent Claude from choosing approaches that won't work in your environment.

### Mistake 6: Assuming Claude Knows Your Codebase

```
Bad:  "Update the OrderService to use the new pricing logic"
Good: "Here is the current OrderService: [paste code]
       Here is the new pricing logic: [paste or describe]
       Update OrderService to use the new pricing logic.
       Specifically, replace the calculateTotal() method."
```

**Why it matters:** Claude starts each conversation with zero knowledge of your codebase. Always provide the relevant code.

---

## 12. A Universal Prompt Template

This template works for almost any development task. Copy it and fill in the blanks.

```markdown
# [Task Title]

## Role
You are a [specific role/expertise]. You have extensive experience with
[relevant technologies and domains].

## Context
### Project
[Project name and brief description]

### Tech Stack
- Language: [e.g., PHP 8.3]
- Framework: [e.g., Laravel 11]
- Database: [e.g., MySQL 8.0]
- Other: [e.g., Redis, Docker, AWS]

### Current State
[Describe what exists now — relevant code, schema, architecture]

### Problem / Goal
[What needs to be built, fixed, or improved]

## Task
[Clear, specific description of what you want Claude to do]

### Requirements
1. [Requirement 1]
2. [Requirement 2]
3. [Requirement 3]

## Constraints
- [Technical constraint — e.g., must use specific library]
- [Performance constraint — e.g., must handle N requests/sec]
- [Compatibility constraint — e.g., must maintain backward compatibility]
- [Style constraint — e.g., follow PSR-12, use TypeScript strict mode]

## Expected Output
[Describe exactly what you want back]
- [ ] Complete working code
- [ ] Explanation of approach
- [ ] Test cases
- [ ] Migration files
- [ ] API documentation

## Examples (if applicable)
### Input
[Sample input]

### Expected Output
[Sample output]

## Additional Notes
[Anything else Claude should know — edge cases, things to avoid, etc.]
```

### Quick Versions for Common Tasks

**Quick Bug Fix:**
```
Bug: [what's wrong]
Expected: [what should happen]
Error: [paste error]
Code: [paste relevant code]
Stack: [language, framework, version]
Fix it and explain the root cause.
```

**Quick Feature:**
```
Feature: [what to build]
Stack: [tech stack]
Must: [requirements as bullet points]
Must not: [constraints]
Output: [what you want back — code, tests, migration, etc.]
```

**Quick Review:**
```
Review this [language] code for:
- Bugs
- Security issues
- Performance problems
- Best practice violations

[paste code]

For each issue: identify, explain, fix.
```

---

## 13. Conclusion

Getting consistently high-quality output from Claude comes down to five habits:

### 1. Be Specific
The more precise your prompt, the more precise the response. Replace vague words with concrete details. Instead of "fast," say "under 200ms." Instead of "secure," list the specific vulnerabilities to protect against.

### 2. Provide Context
Claude knows nothing about your project until you tell it. Share relevant code, schemas, error messages, and architectural decisions. Context is the difference between a generic answer and a solution that works in your specific environment.

### 3. Use Structure
Follow the Role-Context-Task-Constraints-Output-Examples framework. Structured prompts produce structured answers. Use the templates provided in this guide as starting points.

### 4. Iterate and Refine
Don't expect perfection on the first try for complex tasks. Break large problems into smaller prompts. Ask Claude to critique and improve its own output. Use progressive refinement to reach production-quality solutions.

### 5. Learn from Results
When Claude gives you a great response, note what made your prompt effective. When the output misses the mark, identify what was ambiguous or missing. Over time, you'll develop intuition for crafting effective prompts.

---

### Quick Reference Card

| Situation | Technique |
|-----------|-----------|
| Complex problem | Chain-of-thought: ask Claude to reason step by step |
| Need multiple options | Ask for alternatives with pros/cons |
| Production code | Request security review, edge cases, error handling |
| Performance-critical | Specify scale, provide table sizes, ask for EXPLAIN analysis |
| Learning a concept | Ask for explanation with examples from your domain |
| Code review | Assign a security/performance expert role |
| Architecture decision | Request trade-off analysis with your specific constraints |
| Debugging | Provide error, code, expected behavior, and what you tried |

---

*This guide is a living document. Update it as you discover new techniques that work for your specific workflow.*