# QA Test Plan — Personal Finance Tracker

Manual/exploratory test plan covering Auth, Expenses, Income Expectations,
Savings Goals, and the Dashboard. Written against the app as of 2026-07-15
(post WP-removal). Cases are grouped by feature, then by risk category
(happy path, boundary, security, data integrity, cross-cutting). Cases
marked **[AUTOMATED]** now have a PHPUnit regression test (see
`tests/Feature/`); everything else is manual/exploratory.

Severity key: **S1** breaks data integrity or security · **S2** wrong
behavior/confusing UX · **S3** cosmetic/polish.

## Confirmed Findings (fixed 2026-07-15, Day 12)

All three findings below were fixed together by adding `'max:99999999.99'`
and `'regex:/^\d+(\.\d{1,2})?$/'` to the `amount`/`expected_amount`/
`target_amount` rules in `ExpenseController`, `IncomeExpectationController`,
and `SavingsGoalController` respectively (each with a custom `.regex`
message). Verified via `php artisan tinker` before writing the rule that
this exact combination accepts `0.01`-`99999999.99` with 0-2 decimal places
and rejects everything else described below; see the automated tests added
alongside the fix (boundary/decimal-places/scientific-notation cases in
each `*ControllerTest.php`).

1. **[S1 - FIXED] No upper bound on `amount` / `expected_amount` /
   `target_amount`.** All three controllers validated with `numeric` + a
   `min`, but no `max`. Verified live: posting `amount=100000000.00` (9
   digits, exceeds the `expenses.amount` column's true ceiling of
   `decimal(10,2)` → `99999999.99`) was accepted by validation and saved
   as-is under SQLite (dev/test DB), because SQLite doesn't enforce column
   precision. On a stricter engine (MySQL/Postgres in production) the same
   input would either throw an unhandled `QueryException` ("out of range
   value") or get silently truncated depending on strict-mode settings -
   neither is a clean validation error for the user. **Fix**: added
   `'max:99999999.99'` to all three amount rules.
2. **[S2 - FIXED] Silent rounding on save.** An expense saved with
   `amount=42.995` was persisted as `43.00` (confirmed via tinker) -
   Eloquent's `decimal:2` cast rounds half-up on write with no user-facing
   feedback. **Fix**: added a `regex` rule requiring at most 2 decimal
   places, so `42.995` (and any 3+ decimal-place input) now gets a clean
   422 instead of being silently rounded away.
3. **[S3 - FIXED] Scientific notation accepted.** `amount=1e2` passed the
   `numeric` rule and was stored as `100.00`. **Fix**: the same `regex`
   rule requires plain decimal notation (`^\d+(\.\d{1,2})?$`), which
   scientific notation doesn't match.

## Auth

| # | Case | Steps | Expected |
|---|------|-------|----------|
| A1 | Register → land on dashboard | Register with valid data | Redirected to `/dashboard`, empty-state prompts shown |
| A2 | Duplicate email registration | Register twice with same email | Validation error, no second user created |
| A3 | Login with wrong password | — | Validation error, no session created, no user enumeration in the message |
| A4 | Logout invalidates session | Login, logout, press browser back | Back button doesn't restore an authenticated view; hitting `/dashboard` again redirects to login |
| A5 | Session fixation | Note session cookie before login, login, compare cookie after | Session ID regenerates on login (Breeze default — confirm not broken) |
| A6 | Password reset with expired/reused token | Use a reset link twice | Second use rejected |
| A7 | Guest hits every authenticated route directly | `/dashboard`, `/expenses`, `/expenses/create`, `/income-expectations`, `/savings-goals`, and each `/…/{id}/edit` | All redirect to `/login`, none 500 |

## Expenses (`/expenses`)

| # | Case | Expected | Status |
|---|------|----------|--------|
| E1 | Create with valid data | 302 to index, row scoped to user | tested |
| E2 | Create missing required field (amount/category/date) | 422, no row created | partially tested (category only) |
| E3 | Amount boundary: `0.00` | Rejected (`min:0.01`) | **[AUTOMATED]** new |
| E4 | Amount boundary: `0.01` | Accepted | **[AUTOMATED]** new |
| E5 | Amount: negative (`-5`) | Rejected | **[AUTOMATED]** new |
| E6 | Amount: oversized (`100000000.00`) | Rejected (`max:99999999.99`, Finding #1) | **[AUTOMATED]** fixed |
| E6b | Amount boundary: exactly `99999999.99` | Accepted | **[AUTOMATED]** new |
| E6c | Amount: more than 2 decimal places (`42.995`) | Rejected (Finding #2) | **[AUTOMATED]** fixed |
| E6d | Amount: exactly 2 decimal places (`42.99`) | Accepted | **[AUTOMATED]** new |
| E6e | Amount: scientific notation (`1e2`) | Rejected (Finding #3) | **[AUTOMATED]** fixed |
| E7 | Category: valid value wrong case (`FOOD`) | Rejected — `in:` rule is case-sensitive, enum column is lowercase | **[AUTOMATED]** new |
| E8 | Category: value not in the fixed list | Rejected | tested |
| E9 | Date: garbage string (`"not-a-date"`) | Rejected | not yet automated — quick manual check |
| E10 | Date: far future / far past (e.g. `1900-01-01`, `2999-12-31`) | Accepted (no business rule restricts this) — confirm this is intentional, not an oversight | manual — product question |
| E11 | Notes: HTML/script payload (`<script>alert(1)</script>`) | Rendered as inert text in the index table, not executed | verified — view uses `{{ }}`, safe |
| E12 | Notes: very long string (10k chars) | Column is `text`, should accept; confirm page doesn't break layout | manual |
| E13 | Spoof `user_id` in the POST body to attach the expense to another user | Ignored — expense is created under the *authenticated* user regardless of payload | **[AUTOMATED]** new |
| E14 | View another user's edit form directly (`GET /expenses/{id}/edit`) | 403, not just blocked on submit | **[AUTOMATED]** new — was untested (only `PUT`/`DELETE` had coverage) |
| E15 | Update/delete another user's expense | 403, original data unchanged | tested |
| E16 | Delete a user cascades their expenses | Rows removed | tested |
| E17 | Edit/delete a nonexistent ID | 404, not 500 | manual sanity check |
| E18 | Two browser tabs: edit the same expense in both, submit both | Last write wins, no crash (no optimistic locking exists — confirm that's acceptable) | manual |
| E19 | Index page with zero expenses | Empty state renders, no divide-by-zero/undefined-index errors | manual |

## Income Expectations (`/income-expectations`)

| # | Case | Expected | Status |
|---|------|----------|--------|
| I1 | Set for a month | 302, row created, `month` normalized to the 1st | tested |
| I2 | Duplicate month for the same user | Rejected with a clean validation error, not a raw `QueryException` | tested |
| I3 | Same month, different user | Allowed (unique constraint is per-user) | manual — worth an explicit test |
| I4 | Update but keep the same month | Not treated as a self-conflict | tested |
| I5 | `expected_amount` negative | Rejected | **[AUTOMATED]** new |
| I6 | `expected_amount` = `0` | Accepted (boundary is `min:0`, zero income is valid) | **[AUTOMATED]** new |
| I7 | `expected_amount` oversized (`100000000.00`) | Rejected (`max:99999999.99`, Finding #1) | **[AUTOMATED]** fixed |
| I7b | `expected_amount` boundary: exactly `99999999.99` | Accepted | **[AUTOMATED]** new |
| I7c | `expected_amount` more than 2 decimal places (`42.995`) | Rejected (Finding #2) | **[AUTOMATED]** fixed |
| I7d | `expected_amount` exactly 2 decimal places (`42.99`) | Accepted | **[AUTOMATED]** new |
| I7e | `expected_amount` scientific notation (`1e2`) | Rejected (Finding #3) | **[AUTOMATED]** fixed |
| I8 | Malformed month value bypassing the `<input type=month>` picker, e.g. raw POST `month=2026-13` | Rejected — verified via tinker that Laravel's `date_format` round-trip check catches invalid months/days (doesn't silently roll over to the next month) | **[AUTOMATED]** new — locks in this behavior as a regression guard |
| I9 | View another user's edit form directly | 403 | **[AUTOMATED]** new |
| I10 | Delete a user cascades their income expectations | Rows removed | **[AUTOMATED]** new — only `expenses` cascade was covered before |
| I11 | Race: two near-simultaneous submits for the same new month (double-click submit) | The uniqueness check is check-then-insert (TOCTOU) — a genuine race could slip past the app-level check and hit the DB unique constraint as a raw 500 instead of a validation error. Not practically testable in single-threaded PHPUnit; flagging as a known risk, not exercised here | not automated — documented risk only |

## Savings Goals (`/savings-goals`)

Same shape as Income Expectations — mirror cases I1–I11 with
`target_amount`/`savings_goals`. Notable one specific to this feature:

| # | Case | Expected | Status |
|---|------|----------|--------|
| S1 | `target_amount` negative | Rejected | **[AUTOMATED]** new |
| S2 | `target_amount` = `0` | Accepted, and the dashboard's progress-bar math must not divide by zero when this goal is later referenced | **[AUTOMATED]** new (paired with Dashboard D6 below) |
| S3 | View another user's edit form directly | 403 | **[AUTOMATED]** new |
| S4 | Delete a user cascades their savings goals | Rows removed | **[AUTOMATED]** new |
| S5 | Malformed month value | Rejected | **[AUTOMATED]** new |
| S6 | `target_amount` oversized (`100000000.00`) | Rejected (`max:99999999.99`, Finding #1) | **[AUTOMATED]** fixed |
| S6b | `target_amount` boundary: exactly `99999999.99` | Accepted | **[AUTOMATED]** new |
| S6c | `target_amount` more than 2 decimal places (`42.995`) | Rejected (Finding #2) | **[AUTOMATED]** fixed |
| S6d | `target_amount` exactly 2 decimal places (`42.99`) | Accepted | **[AUTOMATED]** new |
| S6e | `target_amount` scientific notation (`1e2`) | Rejected (Finding #3) | **[AUTOMATED]** fixed |

## Dashboard (`/dashboard`)

| # | Case | Expected | Status |
|---|------|----------|--------|
| D1 | Totals/income/goal/progress for the current month | Correct math, other months excluded | tested |
| D2 | Missing income and/or goal | Friendly prompts, no `%` shown | tested |
| D3 | Category grouping/sorting | Correct sums, descending order | tested |
| D4 | Cross-user isolation | Another user's rows never leak in | tested |
| D5 | Overspending (`spent > expected income`) | `actualSavings` negative, progress clamped to `0`, "-$X over budget" shown, not a negative-width bar | tested (100% clamp case only) — **add explicit negative case** |
| D6 | Savings goal with `target_amount = 0` | Must not throw a division-by-zero; guarded by `target_amount > 0` in the controller — confirm the guard actually holds | **[AUTOMATED]** new |
| D7 | Month boundary — expense dated the very first/last second of the month | Correctly included via `whereYear`/`whereMonth`, not off-by-one at midnight in `UTC` (app timezone is fixed at `UTC`, not user-local — confirm this is an accepted product limitation, not a bug for users in other timezones) | manual — product question |
| D8 | Quick-add form validation error | Redirects back to `/dashboard` with errors (relies on `Referer`, not an explicit redirect route) — confirm this doesn't break if `Referer` is stripped by a privacy-focused browser/extension | manual — real edge case, low priority |
| D9 | Chart rendering with a single category / zero categories | No broken Chart.js render, no console error | manual (browser) |

## Cross-Cutting / Security

| # | Case | Expected |
|---|------|----------|
| X1 | Every mutating route (`POST`/`PUT`/`DELETE`) requires the `auth` middleware | Confirmed by routes/web.php grouping; guest-redirect tests exist for each index |
| X2 | CSRF token required on all forms | Framework-level (`VerifyCsrfToken`); not re-verified here — recommend one explicit test if this ever moves off Blade+Breeze defaults |
| X3 | Mass-assignment via extra POST fields (`user_id`, `id`, `created_at`) | Ignored — controllers only ever pass the explicit `validated()` array to `create()`/`update()` |
| X4 | Policy coverage matches controller usage | `ExpensePolicy`/`IncomeExpectationPolicy`/`SavingsGoalPolicy` each define `view`/`update`/`delete`, but `view` is never actually invoked in any controller (`edit()` calls `authorize('update', ...)`, and there's no `show` route). Not a bug — `Route::resource(...)->except('show')` means `view` is dead code. Worth a comment or removing it for clarity. |
| X5 | Browser back-button after logout shows a cached authenticated page | Depends on `Cache-Control` headers Breeze sets on authenticated responses — worth a manual check in an actual browser, not exercisable via `php artisan test` |

## Not Automatable Here (needs a real browser)
- Mobile viewport rendering of all index/create/edit pages and the dashboard chart.
- Chart.js rendering correctness (bar chart truly reflects category totals visually).
- Back-button/cache-header behavior after logout.
- Any race-condition/concurrency case (I11).

