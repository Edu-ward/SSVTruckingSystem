# SSV Trucking System — Deployment Guide
# One-Time Setup on InfinityFree + GitHub Actions CI/CD

Follow these steps ONCE to set up your live server.
After that, every `git push` auto-deploys your code.

---

## STEP 1: Sign Up on InfinityFree

1. Go to https://infinityfree.com
2. Click **Register** and create a free account
3. After logging in, click **+ New Account**
4. Choose a free subdomain (e.g., `ssvtrucking.rf.gd` or similar)
5. Wait ~1 minute for the account to be created

---

## STEP 2: Create Your MySQL Database

1. In the InfinityFree control panel, click **MySQL Databases**
2. Click **+ New Database**
3. Note down these 4 values (you will need them later):
   - **MySQL Server** (hostname, e.g., `sql123.infinityfree.com`)
   - **Database Name** (e.g., `if0_12345678_ssv_trucking`)
   - **Username** (e.g., `if0_12345678`)
   - **Password** (whatever you set)

---

## STEP 3: Import Your Database Schema

1. In the InfinityFree control panel, click **phpMyAdmin**
2. Click your database name on the left panel
3. Click the **Import** tab at the top
4. Click **Choose File** and select `ssv_trucking.sql` from your project folder
5. Click **Go / Import**
6. You should see "Import has been successfully finished"

---

## STEP 4: Set DB Credentials on the Live Server

On InfinityFree (which uses FastCGI), the most reliable method is creating a `config.prod.php` file directly in the file manager.

1. In the InfinityFree control panel, go to **File Manager**
2. Navigate into the **`htdocs/`** directory
3. Click **New File** and name it **`config.prod.php`**
4. Paste the following with your real InfinityFree database credentials:

```php
<?php
define('DB_HOST', 'sql302.infinityfree.com');
define('DB_NAME', 'if0_42840593_ssv_trucking');
define('DB_USER', 'if0_42840593');
define('DB_PASS', 'your_password_here');
define('IS_PRODUCTION', true);
```

5. Save the file.
*(Note: `config.prod.php` is ignored by git and will never be overwritten by GitHub deployments).*

6. Also ensure your Apache rules file inside `htdocs/` is named **`.htaccess`** (NOT `.htdocs`).

---

## STEP 5: Get Your FTP Credentials

1. In the InfinityFree control panel, click **FTP Accounts**
2. Note down:
   - **FTP Hostname** (e.g., `ftpupload.net`)
   - **FTP Username** (e.g., `epiz_12345678`)
   - **FTP Password**

---

## STEP 6: Add FTP Credentials as GitHub Secrets

This lets GitHub Actions upload files to InfinityFree securely.

1. Go to https://github.com/Edu-ward/SSVTruckingSystem
2. Click **Settings** > **Secrets and variables** > **Actions**
3. Click **New repository secret** and add each of:

| Secret Name   | Value                          |
|---------------|-------------------------------|
| `FTP_SERVER`  | Your FTP hostname (e.g., `ftpupload.net`) |
| `FTP_USERNAME`| Your FTP username              |
| `FTP_PASSWORD`| Your FTP password              |

---

## STEP 7: Trigger Your First Deploy

1. Make any small change to a file (e.g., add a comment)
2. Commit and push to GitHub:

```powershell
git add .
git commit -m "chore: trigger initial deployment"
git push
```

3. Go to https://github.com/Edu-ward/SSVTruckingSystem/actions
4. Watch your deployment run — it should show a green checkmark ✅

---

## STEP 8: Visit Your Live Site

Open your InfinityFree subdomain in a browser:
`http://your-subdomain.rf.gd`

You should see the SSV Trucking login page!

---

## After Setup: Day-to-Day Workflow

```
1. Make code changes in XAMPP (local)
2. Test on localhost
3. If DB schema changed: run deploy\export-db.ps1
4. git add . && git commit -m "your message" && git push
5. GitHub Actions auto-deploys in ~1-2 minutes ✅
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| 500 Internal Server Error | Check .htaccess — InfinityFree may not support some directives |
| Database connection error | Double-check SetEnv values in .htaccess on live server |
| Files not uploading | Check GitHub Secrets are correctly named: FTP_SERVER, FTP_USERNAME, FTP_PASSWORD |
| GitHub Action failing | Go to Actions tab on GitHub and read the error log |
| Site shows blank page | Enable error display temporarily: `php_flag display_errors on` in .htaccess |
