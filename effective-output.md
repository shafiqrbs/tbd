# Getting Effective Output from Claude Code — A Complete Guide

This document covers everything you need to know about writing better prompts,
using context, skills, agents, and other features to get maximum and accurate
output from Claude Code.

---

## Table of Contents

1. [The Golden Rule](#1-the-golden-rule)
2. [Writing Better Prompts](#2-writing-better-prompts)
3. [Using Context Effectively](#3-using-context-effectively)
4. [Skills & Slash Commands](#4-skills--slash-commands)
5. [Agents & Subagents](#5-agents--subagents)
6. [The Workflow Cycle](#6-the-workflow-cycle)
7. [Context Window Management](#7-context-window-management)
8. [Interrupting & Course-Correcting](#8-interrupting--course-correcting)
9. [Advanced Techniques](#9-advanced-techniques)
10. [Common Mistakes to Avoid](#10-common-mistakes-to-avoid)
11. [Quick Reference Cheatsheet](#11-quick-reference-cheatsheet)

---

## 1. The Golden Rule

**Always tell Claude how to verify its own work.**

This single practice improves output quality more than anything else. When Claude
can check its own work (run tests, compare output, validate behavior), it
catches its own mistakes before you ever see them.

Without verification:
```
Add a discount calculation to the sales module.
```
Claude writes code that looks correct but may have edge case bugs. You become
the only feedback loop.

With verification:
```
Add a discount calculation to the sales module.
Test cases:
- 10% discount on 1000 = 900
- 0% discount on 500 = 500
- 100% discount on 200 = 0
- Negative discount should throw an error
Write tests for these cases and run them.
```
Claude writes the code, runs the tests, sees failures, fixes them — all before
showing you the result. You get working code on the first try.

---

## 2. Writing Better Prompts

### 2.1 Be Specific, Not Vague

The most common mistake is being too vague. Claude works best when it knows
exactly what you want.

#### Bad Prompts vs Good Prompts

| Bad (Vague)                        | Good (Specific)                                                        |
|------------------------------------|------------------------------------------------------------------------|
| Fix the bug                        | Login fails when session expires. Check `AuthController`. Write a test |
| Add a new feature                  | Add bulk delete to `ProductController` with soft delete support        |
| Make it faster                     | The `getStockReport` query is slow. Add database indexing or caching   |
| Clean up the code                  | Extract the duplicate validation logic in `SalesController` lines 45-80 into a shared method |
| Write tests                        | Write tests for `PurchaseService::calculateTotal()` covering tax, discount, and zero-quantity cases |

### 2.2 The Anatomy of a Great Prompt

A great prompt has up to 4 parts (not all are always needed):

```
[WHAT]     What you want done — the task itself
[WHERE]    Which files, modules, or areas of code
[HOW]      Constraints, patterns to follow, things to avoid
[VERIFY]   How Claude should check its work
```

#### Example — Simple Task (WHAT + WHERE is enough)

```
Add a `phone` field to the Customer entity in the Core module.
```

#### Example — Medium Task (WHAT + WHERE + HOW)

```
Add a bulk export feature to the Inventory module.
Use PhpSpreadsheet (already installed).
Follow the same pattern as the existing report export in
app/Services/ReportExportService.php.
```

#### Example — Complex Task (All 4 parts)

```
[WHAT]   Implement a stock alert system that notifies when product
         quantity falls below a threshold.

[WHERE]  Create a new service in Modules/Inventory/App/Services/.
         Add a new Doctrine entity for alert configuration.
         Add API endpoints in the Inventory module routes.

[HOW]    Follow the existing service patterns in the module.
         Use the notification system in app/Services/Notification/.
         Alerts should be per-product and per-warehouse.

[VERIFY] Write tests for:
         - Alert triggers when stock drops below threshold
         - No alert when stock is above threshold
         - Alert respects per-warehouse configuration
         Run the tests and ensure they pass.
```

### 2.3 Reference Files Directly

Instead of describing where code lives, point to it:

```
Bad:  "Look at the controller that handles purchases"
Good: "Look at Modules/Inventory/App/Http/Controllers/PurchaseController.php"
```

You can also use `@` to reference files:
```
Follow the pattern in @Modules/Inventory/App/Services/StockService.php
```

### 2.4 Reference Existing Patterns

When you want Claude to follow your codebase's style:

```
Look at how ProductController handles CRUD operations.
Follow that exact same pattern to create a WarehouseTransferController.
```

This is far more effective than describing patterns in words. Claude reads the
example and replicates the approach precisely.

### 2.5 Delegate, Don't Dictate

Think of Claude as a capable developer. Give context and direction, not
step-by-step file edits:

```
Bad (micromanaging):
"Open file X, go to line 45, add this exact code..."

Good (delegating):
"The checkout flow breaks when a customer has no default address.
The relevant code is in Modules/Inventory/App/Services/SalesService.php.
Investigate and fix it."
```

Claude will read the code, understand the problem, and implement a proper fix.
You get a better solution because Claude sees the full context.

### 2.6 Specify What You DON'T Want

Sometimes it helps to say what to avoid:

```
Refactor the report generation in AccountingService.
- Do NOT change the public API or method signatures
- Do NOT add new dependencies
- Keep backward compatibility with existing callers
```

---

## 3. Using Context Effectively

Context is information Claude has access to when working on your task.
Better context = better output. There are several layers of context:

### 3.1 CLAUDE.md — Your Project's Instruction Manual

CLAUDE.md is a special file that Claude reads at the start of EVERY session.
It is the most important tool for consistent, accurate output.

#### Where to Place CLAUDE.md

| Location                    | Scope                    | Shared?          |
|-----------------------------|--------------------------|------------------|
| `./CLAUDE.md`               | This project             | Yes (via git)    |
| `~/.claude/CLAUDE.md`       | All your projects        | No (personal)    |
| `.claude/rules/*.md`        | Topic-specific rules     | Yes (via git)    |

#### What to Put in CLAUDE.md

```markdown
# Project: TBD Backend

## Commands
- Run tests: `php artisan test`
- Run single test: `./vendor/bin/phpunit tests/Feature/SomeTest.php`
- Lint: `./vendor/bin/pint`
- Clear cache: `php artisan cache:clear && php artisan config:clear`

## Architecture Rules
- All entities use Doctrine ORM annotations, NOT Eloquent migrations
- Business logic goes in Services, NOT Controllers
- Controllers only handle request/response
- Use FormRequest classes for validation

## Code Style
- Follow PSR-12
- Use type hints on all method parameters and return types
- Entity properties must be private with getters/setters

## Gotchas
- Always run `php artisan doctrine:schema:update` after entity changes
- Multi-tenant queries must include domain_id filter
- JWT tokens expire after 60 minutes
```

#### Rules for a Good CLAUDE.md

- Keep it under 200 lines (longer = Claude ignores parts of it)
- Include things Claude CANNOT guess from reading code
- Skip obvious things ("write clean code", "follow best practices")
- Update it when you discover new gotchas or patterns
- Use `@path` to reference detailed docs without bloating the file

#### Scoped Rules with .claude/rules/

For larger projects, split instructions by topic:

```
.claude/rules/
├── doctrine-entities.md     # Rules for writing entities
├── api-conventions.md       # Rules for API endpoints
├── testing.md               # Rules for writing tests
└── module-structure.md      # Rules for module organization
```

You can scope rules to specific file patterns:

```markdown
---
paths:
  - "Modules/Accounting/**/*.php"
---

# Accounting Module Rules
- All monetary values use integer cents, never floats
- Every transaction must have a corresponding ledger entry
- Always validate account head existence before posting
```

### 3.2 Auto Memory — Claude Learns From You

Claude automatically saves learnings from your sessions to:
`~/.claude/projects/<project>/memory/MEMORY.md`

This includes:
- Build commands Claude discovers work
- Debugging patterns and gotchas
- Your preferences (demonstrated through corrections)
- Architecture notes

**View/edit memory:**
```
/memory
```

**Explicitly ask Claude to remember something:**
```
"Always use Doctrine entities, never create Eloquent migrations in this project.
Remember this."
```

**Ask Claude to forget something:**
```
"Stop remembering that we use Redis for caching — we switched to Memcached."
```

### 3.3 Conversation Context

Everything you say in a session is context. Manage it wisely:

- Start fresh between unrelated tasks with `/clear`
- Don't let irrelevant conversation accumulate
- For a quick side question without polluting context, use `/btw`
- Use `/compact` to summarize and free space when context gets large

### 3.4 Direct Context Injection

You can feed Claude external context directly:

```bash
# Pipe file contents
cat error.log | claude "explain this error and suggest a fix"

# Pipe command output
php artisan route:list | claude "find all inventory routes"

# Paste images (screenshots, designs, diagrams)
# Just drag-and-drop or paste into the terminal
```

---

## 4. Skills & Slash Commands

Skills are reusable workflows that Claude loads on demand. Think of them as
"saved prompts with superpowers."

### 4.1 Built-in Commands You Should Know

| Command              | What It Does                                              |
|----------------------|-----------------------------------------------------------|
| `/init`              | Generate a starter CLAUDE.md from your project            |
| `/clear`             | Reset conversation (fresh start)                          |
| `/compact`           | Summarize context to free space                           |
| `/compact <topic>`   | Summarize but keep focus on a specific topic              |
| `/memory`            | View/edit Claude's auto-saved memories                    |
| `/simplify`          | Review recent changes for quality issues and fix them     |
| `/rewind`            | Undo to a previous checkpoint                             |
| `/context`           | Show how much context window is used                      |
| `/btw`               | Ask a side question without adding to context             |
| `/permissions`       | Configure tool permissions                                |
| `/hooks`             | Configure automation hooks                                |

### 4.2 Creating Custom Skills

Create a file at `.claude/skills/<name>/SKILL.md`:

#### Example — Module Test Runner

```markdown
---
name: test-module
description: Run tests for a specific module
disable-model-invocation: true
---

Run all tests for the module specified in $ARGUMENTS:
1. Navigate to Modules/$ARGUMENTS/
2. Run the test suite
3. Report results with any failures explained
```

Usage: `/test-module Inventory`

#### Example — Entity Generator

```markdown
---
name: create-entity
description: Create a new Doctrine entity following project conventions
disable-model-invocation: true
---

Create a new Doctrine entity in module $ARGUMENTS following these steps:
1. Read an existing entity in the same module for pattern reference
2. Create the new entity with proper ORM annotations
3. Create the corresponding Eloquent model
4. Create the repository class
5. Run doctrine:schema:update --dump-sql to preview changes
```

Usage: `/create-entity Inventory/StockTransfer`

#### Example — Knowledge Skill (Auto-Invoked)

```markdown
---
name: api-patterns
description: API design patterns for this project
user-invocable: false
---

When creating API endpoints in this project:
- Use JsonRequestResponse::returnJsonResponse() for all responses
- Validate with FormRequest classes
- Follow RESTful naming: GET /list, POST /store, PUT /update, DELETE /delete
- Always include domain_id filtering for multi-tenant isolation
- Wrap database operations in transactions
```

This skill loads automatically when Claude detects it is relevant (creating API
endpoints). You never need to invoke it manually.

### 4.3 Skill Configuration Options

| Option                       | Purpose                                               |
|------------------------------|-------------------------------------------------------|
| `disable-model-invocation`   | Only YOU can invoke it (good for side-effect actions)  |
| `user-invocable: false`      | Only CLAUDE invokes it (background knowledge)          |
| `$ARGUMENTS`                 | Placeholder replaced by whatever you type after `/name`|

---

## 5. Agents & Subagents

Agents are specialized Claude instances that handle specific types of tasks.
They run in their own context window, keeping your main conversation clean.

### 5.1 When to Use Agents

| Situation                           | Use Agent?  | Which One?       |
|-------------------------------------|-------------|------------------|
| Quick file search                   | No          | Use Glob/Grep    |
| Research across many files          | Yes         | Explore          |
| Plan a complex feature              | Yes         | Plan             |
| Run tests and report results        | Yes         | General-purpose  |
| Simple single-file edit             | No          | Do it directly   |
| Investigate unfamiliar module       | Yes         | Explore          |

### 5.2 Built-in Agent Types

#### Explore Agent (Fast Research)

Best for: Finding files, searching code, understanding codebase structure.
It is read-only (cannot edit files) and uses a faster model for speed.

```
"Use the Explore agent to find all places where stock quantity
is modified in the Inventory module."
```

Specify thoroughness: "quick", "medium", or "very thorough"

```
"Use the Explore agent (very thorough) to map out how the
purchase flow works from controller to database."
```

#### Plan Agent (Architecture & Design)

Best for: Designing implementation before coding. Read-only.

```
"Use the Plan agent to design how we should implement
a product bundling feature in the Inventory module."
```

#### General-Purpose Agent (Full Access)

Best for: Complex multi-step tasks that need file editing, running commands, etc.

```
"Use a general-purpose agent to refactor all controllers
in the Accounting module to use the new response format."
```

### 5.3 Creating Custom Agents

Create a file at `.claude/agents/<name>.md`:

#### Example — Code Reviewer Agent

```markdown
---
name: code-reviewer
description: Reviews code for quality and security issues
tools: Read, Glob, Grep
model: sonnet
---

You are a code reviewer for a Laravel application using Doctrine ORM.
Review the specified code and check for:
- SQL injection vulnerabilities
- Missing input validation
- Multi-tenant data leaks (missing domain_id filters)
- Doctrine annotation errors
- Missing error handling at system boundaries

Provide specific, actionable feedback with file paths and line numbers.
```

#### Example — Test Writer Agent

```markdown
---
name: test-writer
description: Writes tests for existing code
tools: Read, Glob, Grep, Write, Edit, Bash
model: opus
---

You write tests for a Laravel application. When given a class or feature:
1. Read the source code thoroughly
2. Identify all public methods and edge cases
3. Write comprehensive tests following existing test patterns
4. Run the tests and fix any failures
5. Report the final test results
```

### 5.4 Running Agents in Parallel

For independent tasks, run multiple agents simultaneously:

```
"Research these three things in parallel using separate agents:
1. How authentication works in the Core module
2. How stock calculations work in the Inventory module
3. How ledger entries work in the Accounting module"
```

Claude launches three Explore agents simultaneously, each investigating
independently. Results come back faster than sequential research.

### 5.5 Agent Isolation with Worktrees

For risky experiments, run an agent in an isolated git worktree:

```
"Use a general-purpose agent in a worktree to experiment with
refactoring the SalesService to use the new discount engine."
```

The agent works on a copy of the repo. If the changes are good, they can be
merged. If not, the worktree is discarded. Your working directory stays clean.

---

## 6. The Workflow Cycle

For complex tasks, follow this cycle for best results:

### Step 1: Explore (Understand the Problem)

Use Plan Mode or the Explore agent to understand before changing anything.

```
Read Modules/Inventory/App/Services/SalesService.php and explain
how the discount calculation currently works.
```

### Step 2: Plan (Design the Solution)

Ask Claude to create a plan before implementing:

```
I want to add tiered discounts (buy 10 get 5% off, buy 50 get 15% off).
What files need to change? What is the approach? Create a plan.
```

Review the plan. Correct any misunderstandings. This is cheap — much cheaper
than fixing wrong implementation later.

### Step 3: Implement (Write the Code)

Now let Claude implement the approved plan:

```
Implement the tiered discount plan. Follow the existing patterns in
SalesService. Include the Doctrine entity for discount tiers.
```

### Step 4: Verify (Check the Work)

Always verify:

```
Run the tests. Also manually test with:
- Product with no discount tier
- Product with 10-unit tier (5%)
- Product at exactly 50 units (15%)
- Product at 51 units (still 15%)
```

### When to Skip Steps

| Task Complexity       | Steps to Follow               |
|-----------------------|-------------------------------|
| Typo fix              | Just do it (Step 3 only)      |
| Simple bug fix        | Step 3 + Step 4               |
| New feature           | All 4 steps                   |
| Architectural change  | All 4 steps, extra planning   |

---

## 7. Context Window Management

Claude's context window is finite. As it fills up, quality degrades. Managing
context is critical for long sessions.

### Monitor Context Usage

```
/context
```

This shows how much of the context window is used.

### Strategies for Managing Context

| Strategy                        | When to Use                                    |
|---------------------------------|------------------------------------------------|
| `/clear`                        | Between unrelated tasks                        |
| `/compact`                      | Mid-task when context is getting large          |
| `/compact <topic>`              | Keep focus on specific area during compaction   |
| Use Explore agent               | For research (keeps results out of main context)|
| Use subagents for verbose tasks | Test runs, log analysis, large file reviews    |
| Move instructions to CLAUDE.md  | Repeated instructions you give every session   |
| `/btw`                          | Side questions without polluting context        |

### Signs Your Context Is Too Full

- Claude starts forgetting earlier instructions
- Responses become less accurate or more generic
- Claude repeats work it already did
- Claude contradicts its earlier statements

**Fix:** Run `/compact` or `/clear` and start fresh with a better prompt.

---

## 8. Interrupting & Course-Correcting

You do NOT need to wait for Claude to finish. Interrupt early and often.

### Keyboard Shortcuts

| Shortcut             | Action                                        |
|----------------------|-----------------------------------------------|
| `Esc`                | Stop current action (context preserved)        |
| `Esc` + `Esc`       | Open rewind menu                               |
| Type while running   | Claude stops and reads your input              |

### When to Interrupt

- Claude is going in the wrong direction
- Claude is editing the wrong file
- You realize you forgot to mention something important
- The approach is more complex than needed

### When to Rewind

```
/rewind
```

Opens a menu showing checkpoints. Pick one to restore both conversation
and code to that point. Use this when:

- Claude made changes you want to completely undo
- You want to try a different approach from a clean state
- Something went wrong and you want to go back

### The Two-Correction Rule

If you have corrected Claude twice and it is still not getting it right:

1. Run `/clear`
2. Write a NEW prompt that incorporates everything you learned
3. Include the corrections directly in the new prompt

A fresh session with a better prompt almost always beats a long session
with accumulated corrections and confusion.

---

## 9. Advanced Techniques

### 9.1 Hooks — Automated Actions

Hooks run shell commands automatically in response to events.

Configure with `/hooks` or in `.claude/settings.json`:

```json
{
  "hooks": {
    "postEdit": {
      "command": "./vendor/bin/pint --dirty",
      "description": "Auto-format after edits"
    },
    "preCommit": {
      "command": "php artisan test --stop-on-failure",
      "description": "Run tests before committing"
    }
  }
}
```

### 9.2 MCP Servers — External Integrations

Connect Claude to external tools and services:

```bash
# Add a database MCP server
claude mcp add postgres -- npx @modelcontextprotocol/server-postgres

# Add a custom API server
claude mcp add my-api -- node path/to/server.js
```

Claude can then query your database, fetch from APIs, or interact with
external services directly.

### 9.3 Non-Interactive Mode — Scripting with Claude

Use Claude in scripts and automation:

```bash
# Simple question
claude -p "List all API routes in the Inventory module"

# JSON output for parsing
claude -p "Analyze this codebase" --output-format json

# Pipe input
cat error.log | claude -p "explain this error"

# Stream output
claude -p "Review this PR" --output-format stream-json
```

### 9.4 Multiple Sessions & Worktrees

For large tasks, run multiple Claude sessions in parallel:

```bash
# Session 1: Working on authentication
cd /path/to/project && claude

# Session 2 (different terminal): Working on inventory
cd /path/to/project && claude

# Or use git worktrees for full isolation
git worktree add ../project-feature feature-branch
cd ../project-feature && claude
```

### 9.5 Image & Visual Input

Claude is multimodal — it can see images:

- Paste screenshots of bugs to help Claude understand the issue
- Paste UI designs for Claude to implement
- Paste error screenshots instead of typing error messages
- Paste diagrams for Claude to understand architecture

Just drag-and-drop or paste directly into the terminal.

---

## 10. Common Mistakes to Avoid

### Mistake 1: Being Too Vague
```
Bad:  "Fix the API"
Good: "The /api/inventory/stock endpoint returns 500 when warehouse_id
       is null. Add validation in the FormRequest."
```

### Mistake 2: Not Providing Verification
```
Bad:  "Add email notifications"
Good: "Add email notifications. Test with: new order triggers email,
       cancelled order triggers email, duplicate order does NOT trigger.
       Write and run the tests."
```

### Mistake 3: Micromanaging Every Step
```
Bad:  "Open file X, go to line 45, change 'foo' to 'bar', then open file Y..."
Good: "Rename the 'foo' method to 'bar' across the Inventory module
       and update all callers."
```

### Mistake 4: Never Clearing Context
Long sessions accumulate irrelevant context. Claude's accuracy drops.
Clear between unrelated tasks.

### Mistake 5: Not Using CLAUDE.md
If you find yourself repeating the same instructions ("use Doctrine, not
Eloquent migrations"), put it in CLAUDE.md. Say it once, use it forever.

### Mistake 6: Ignoring the Plan Step
Jumping straight to implementation for complex tasks leads to wrong
solutions. Spend 30 seconds on planning to save 30 minutes on rework.

### Mistake 7: Not Referencing Existing Patterns
```
Bad:  "Create a new controller" (Claude guesses your conventions)
Good: "Create a new controller following the pattern in ProductController"
      (Claude matches your exact style)
```

### Mistake 8: Fighting Instead of Restarting
After 2-3 failed corrections, `/clear` and rewrite the prompt. Fresh
context with a better prompt is almost always the right move.

---

## 11. Quick Reference Cheatsheet

### Essential Commands

```
/init              → Generate starter CLAUDE.md
/clear             → Fresh start (reset context)
/compact           → Summarize context (free space)
/memory            → View/edit saved memories
/rewind            → Undo to checkpoint
/context           → Check context usage
/simplify          → Review and improve recent changes
/btw               → Side question (no context pollution)
Esc                → Stop current action
Esc + Esc          → Rewind menu
```

### Prompt Template

```
[WHAT]     → What do you want done?
[WHERE]    → Which files/modules?
[HOW]      → Constraints, patterns to follow?
[VERIFY]   → How should Claude check its work?
```

### Context Priority (Most to Least Persistent)

```
CLAUDE.md           → Every session, every project (if in ~/)
.claude/rules/      → Every session, scoped by file path
Auto Memory         → Every session, machine-local
Conversation        → Current session only
/btw                → Not saved at all
```

### Agent Selection Guide

```
Simple file search         → Glob / Grep (no agent needed)
Codebase exploration       → Explore agent
Architecture planning      → Plan agent
Multi-step implementation  → General-purpose agent
Risky experiments          → Agent with worktree isolation
```

### The Golden Rules (Summary)

1. Always include verification criteria
2. Be specific about WHAT, WHERE, HOW
3. Reference existing patterns in your code
4. Clear context between unrelated tasks
5. Put repeated instructions in CLAUDE.md
6. Plan before implementing complex features
7. Interrupt early when direction is wrong
8. After 2 failed corrections, start fresh
