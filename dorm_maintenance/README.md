# Dorm Management System — Login/Logout + Smart Maintenance Priority

Scope: only the Login/Logout auth flow and the "Smart Maintenance Priority"
feature, built against the subset of the shared ER diagram this feature
needs (Student, Room, Maintenance_request), plus a new Login table.

## Setup
1. Create the database: import `sql/schema.sql` into MySQL
   (e.g. `mysql -u root -p < sql/schema.sql`, or via phpMyAdmin).
2. Update credentials in `config/db.php` if your MySQL user/password
   differ from the defaults (`root` / empty password).
3. Make sure the `uploads/` folder is writable by the web server
   (`chmod 775 uploads` on Linux/Mac).
4. Serve the folder with PHP's built-in server for local testing:
   `php -S localhost:8000` from inside `dorm_maintenance/`, then
   visit `http://localhost:8000`.

## Flow
- `register.php` → creates a Student + Login row together (standalone
  demo signup; two sample students are pre-seeded in `schema.sql` but
  have no password yet — register fresh accounts, or add a Login row
  for them manually via password_hash()).
- `login.php` → authenticates against Student + Login, starts a session.
- `dashboard.php` → summary stats + links into the maintenance feature.
- `maintenance_submit.php` → submit a request (description + optional
  photo); `includes/categorize.php` auto-assigns Category and Priority
  from keywords in the description.
- `maintenance_list.php` → the student's own requests, sorted by
  priority then date, with status badges.
- `logout.php` → destroys the session.

## Notes for teammates
- `Student` and `Room` are treated as shared tables — only the columns
  from the group ER diagram are used here, nothing added to them.
- `Login` is a new table (Student_ID FK, PasswordHash, LastLogin) since
  auth wasn't in the original diagram. Safe to keep separate so it
  doesn't touch anyone else's schema.
- `Maintenance_request.Photo` stores a relative file path
  (`uploads/req_xxx.jpg`), not a BLOB — keeps the DB small and the
  photo directly servable as an `<img src>`.
- Category/priority logic lives entirely in `includes/categorize.php`
  as a keyword lookup — easy to extend with more keywords, or swap
  for a real classifier later without changing any other file.
