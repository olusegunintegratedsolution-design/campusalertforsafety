# Administrator Login Setup — Database Authentication

This version uses the project's existing `users` table for administrator authentication.

## 1. Administrator login URL

Open:

`/admin/login.php`

The login accepts an account only when all three conditions are true:

- the email exists in `users`;
- `role` is `admin`;
- `status` is `active`;

and the submitted password matches the stored password hash.

The normal `/login.php` remains separate for student/staff access.

## 2. Set the admin password in TiDB

Generate a password hash on a computer with PHP installed. Do not put the plaintext password into the source code or SQL file.

### Windows/XAMPP

```cmd
C:\xampp\php\php.exe -r "echo password_hash('YOUR_NEW_TEST_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Copy the generated hash.

Then run this in TiDB Cloud SQL Editor:

```sql
UPDATE campus_safety_db.users
SET password = 'PASTE_GENERATED_HASH_HERE',
    role = 'admin',
    status = 'active'
WHERE email = 'admin@ilaropoly.edu.ng';
```

Verify the account:

```sql
SELECT id, email, role, status
FROM campus_safety_db.users
WHERE email = 'admin@ilaropoly.edu.ng';
```

The result should show the administrator email with `role = admin` and `status = active`.

PHP's `password_hash()` creates a strong one-way password hash, and `password_verify()` checks the submitted password against that stored hash. The hash contains the information required for verification, so no plaintext password needs to be stored. 

## 3. Remove the old environment-based admin credentials

The current administrator login no longer reads:

- `ADMIN_EMAIL`
- `ADMIN_PASSWORD_HASH`

After deploying this version, those two Vercel variables are no longer needed for administrator login and can be deleted from the project settings.

If you change environment variables, Vercel requires a new deployment for the change to take effect.

## 4. Deploy

Commit and push the project normally:

```bash
git add .
git commit -m "Simplify administrator authentication"
git push
```

Then open:

`https://campusalertforsafety.vercel.app/admin/login.php`

## 5. Test the complete authentication flow

1. Correct administrator email + the password you just hashed → `/admin/dashboard.php`.
2. Wrong administrator password → denied.
3. Student/staff credentials at `/admin/login.php` → denied.
4. Administrator credentials at normal `/login.php` → denied.
5. Direct `/admin/dashboard.php` while logged out → redirected to normal login.
6. Admin session → allowed into protected admin pages.
7. Logout → session ends and protected admin pages are inaccessible.

## Important

Do not commit or paste your real administrator password into:

- `admin/login.php`
- `database/database.sql`
- `setup_db.php`
- GitHub
- public screenshots
- chat messages

If a password has already been exposed during testing, replace it with a new password before going live.


## 6. Temporary password recovery

This package includes `/admin/reset-password.php`. It requires a temporary Vercel environment variable named `ADMIN_RESET_KEY`. Generate a strong random value locally, add it to Vercel Production, deploy, use the recovery page once, then remove `ADMIN_RESET_KEY` and redeploy. Never put the recovery key in source code.
