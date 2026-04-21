# CityBridge — PHP/MySQL Procedural Build

A complete procedural PHP + MySQLi implementation of the CityBridge permit
management system, driven by the `CityBridge` database schema.

---

## 1. Setup

1. Start MAMP / XAMPP so PHP + MySQL are running.
2. Open phpMyAdmin and import your SQL script to create the `CityBridge`
   database (with all tables and sample data).
3. Copy all files in this folder to your web server's document root
   (for MAMP: `/Applications/MAMP/htdocs/citybridge/`).
4. Open `db.php` and confirm the connection settings match your MAMP
   setup. The defaults are:

       $host     = "localhost";
       $user     = "root";
       $password = "root";
       $dbname   = "CityBridge";
       $port     = 8889;     // MAMP default MySQL port

5. Create an `uploads/` folder inside the project directory and make it
   writable (so permit attachments can be saved):

       mkdir uploads
       chmod 775 uploads

6. Visit `http://localhost:8888/citybridge/Home.html` in your browser.

---

## 2. Test credentials

Hashes match the passwords in your SQL `INSERT` statements.

| Role  | Email                 | Password    |
|-------|-----------------------|-------------|
| User  | dhay@gmail.com        | User@123    |
| User  | ali@gmail.com         | User@123    |
| User  | jana@gmail.com        | User@123    |
| Admin | admin@citybridge.com  | Admin@123   |

---

## 3. File list

### Entry points
- `Home.html` — landing page (log in / sign up)
- `login.php` — sign-in (redirects to `user.php` or `admin.php` by role)
- `signup.php` — register a new user + company
- `logout.php` — destroys the session

### User pages
- `user.php` — dashboard: company info, stats, recent permits
- `my-permits.php` — list of your permits with status filter (`?status=pending`)
- `reqprimt.php` — submit a new permit (labor / equipment / medical / electronic)
- `edit-permit-pending.php?id=N` — edit a still-pending permit
- `permit-details-pending.php?id=N`
- `permit-details-approved.php?id=N`
- `permit-details-rejected.php?id=N`
- `renew.php?id=N` — extends approved permit's expiry by +12 months
- `authorities.php` — authorized providers (from `authority` table)
- `safety-guidelines.php` — rules per category (from `safety_guideline` table)

### Admin pages
- `admin.php` — dashboard with pending / approved / rejected lists
- `admin-pending.php?id=N` — review a pending permit; Approve or Reject
- `admin-approved.php?id=N` — read-only view of an approved permit
- `admin-rejected.php?id=N` — read-only view with the rejection reason

### Shared
- `auth.php` — `require_login()` + `fmt_date()` / `type_label()` /
  `permit_code()` / `e()` (htmlspecialchars alias)
- `db.php` — mysqli connection
- `style.css` — unchanged
- `script.js` — unchanged layout/validation, but the stray JS-only
  redirects on login/signup/permit forms have been removed so that the
  real PHP POST is never bypassed.

---

## 4. How the data model connects

- `account` holds login credentials + a role (`'user'` or `'admin'`).
- Users also have a row in `user_account` (+ a linked `company`).
- Admins also have a row in `admin_account`.
- Permits live in the parent `permit` table; each permit has a sub-type
  row in exactly one of `labor_permit` / `equipment_permit` /
  `medical_permit` / `electronic_permit`.
- Attachments live in `attachment` and point back at a `permit_id`.

All queries use **prepared statements** (`mysqli prepare / bind_param /
execute`) to prevent SQL injection. The permit submission + update flows
use `begin_transaction / commit / rollback` so the parent and sub-type
rows stay in sync.

---

## 5. Notes on fixes applied

While building this I found and corrected a few issues in the files
that were uploaded:

1. **`admin.php`** — the original referred to `$_SESSION['user_id']` but
   `login.php` actually stores `$_SESSION["account_id"]`, so the admin
   profile never loaded. It also joined `user_account`, but admins live
   in `admin_account`. Both are fixed.

2. **`script.js`** — the login, signup, and permit submit handlers all
   called `preventDefault()` and then did `window.location.href =
   'user.html'` (or just showed an alert). That meant the PHP back-end
   was never actually called and no rows were written to the database.
   The submit handlers now validate client-side and then let the form
   post naturally to PHP.

3. **Link targets** — the uploaded HTML pages linked to each other as
   `user.html`, `my-permits.html`, etc. Since we're now using PHP files,
   every in-app link has been updated to the `.php` equivalent.
   `Home.html` stays as HTML (it's a static landing page).

4. **Permit form action** — `reqprimt.html` posted to itself but had no
   PHP handler. The new `reqprimt.php` handles `POST` by inserting the
   parent + sub-type row and optionally saving the attachment file.

---

## 6. Features that work end-to-end

- Sign up → inserts `account` + `user_account` + `company` rows.
- Log in → session set, redirected by role.
- User requests a permit → file uploaded to `uploads/`, records written
  across `permit` + sub-type + `attachment`.
- User edits a still-pending permit → sub-type row updated.
- User views approved permit → can click "Renew" to add 12 months.
- User views rejected permit → sees the admin's reason.
- Admin reviews pending → Approve (sets 12-month expiry) or Reject
  (saves a reason).
- Admin dashboard + user dashboard show live counts from the database.
- Safety Guidelines and Authorities pages are both loaded from the DB,
  not hardcoded.
