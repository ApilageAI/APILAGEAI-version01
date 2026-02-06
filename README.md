# ApilageAI

ApilageAI is a PHP web app with a Node.js backend for realtime/AI features. The PHP app serves the web UI, handles auth, and connects to MySQL. The Node service provides APIs and Socket.IO.

**Quick Links (Local)**
- Web: `https://apilageai.lk/`
- App: `https://apilageai.lk/app`
- Node API: `https://apilageai.lk/health`

**How The App Works**
1. **Apache + PHP** serve the main site and UI pages from the project root.
2. **Friendly URLs** are routed by `.htaccess` to `app.php`, `auth.php`, `dashboard.php`, `pay.php` (images page).
3. **PHP** reads config from `.env` and `backend/config.php`.
4. **MySQL** stores users, sessions, conversations, messages, transactions, and app data.
5. **Node** handles realtime and AI endpoints used by the UI.

**Key Routes**
- `/` -> `index.php`
- `/app` -> `app.php`
- `/app/chat/{id}` -> `app.php?view=chat&sub_view={id}`
- `/auth` -> `auth.php?do=log`
- `/auth/login` -> login
- `/auth/register` -> register
- `/auth/logout` and `/auth/signout` -> logout
- `/images` -> `dashboard.php`
- `/images/usage` -> `dashboard.php?view=usage`
- `/pay/{amount}` -> `pay.php`

## Requirements
- PHP 8.1+
- MySQL or MariaDB
- Node.js 18+ and npm
- Apache with `mod_rewrite` enabled

## Local Setup (macOS with MAMP)
1. **Set MAMP Document Root** to the project folder.
2. **Enable rewrite + AllowOverride** in Apache.
3. **Create the database**:
```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "DROP DATABASE IF EXISTS apilageai_main_db; CREATE DATABASE apilageai_main_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
4. **Import the schema and data**:
```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot apilageai_main_db < /Users/yourname/path/to/apilageai/apilageai_main_db.sql
```
5. **Set `.env` for local DB**:
```env
DB_HOST=127.0.0.1
DB_PORT=8889
DB_USER=root
DB_PASSWORD=root
DB_NAME=apilageai_main_db
APP_URL=https://apilageai.lk
NODE_API_BASE=https://apilageai.lk
NODE_ENV=development
APP_DEBUG=true
```
6. **Install and run Node**:
```sh
cd /Users/yourname/path/to/apilageai/node
npm install
npm run dev
```
7. **Open**:
- `https://apilageai.lk/`
- `https://apilageai.lk/app`

## Local Setup (Windows with XAMPP/WAMP)
1. **Move the project** to your Apache root, for example:
- `C:\xampp\htdocs\apilageai`
2. **Create the database**:
```bat
mysql -h 127.0.0.1 -P 3306 -u root -p
```
```sql
DROP DATABASE IF EXISTS apilageai_main_db;
CREATE DATABASE apilageai_main_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```
3. **Import the schema and data**:
```bat
mysql -h 127.0.0.1 -P 3306 -u root -p apilageai_main_db < C:\xampp\htdocs\apilageai\apilageai_main_db.sql
```
4. **Set `.env` for local DB**:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=apilageai_main_db
APP_URL=https://apilageai.lk
NODE_API_BASE=https://apilageai.lk
NODE_ENV=development
APP_DEBUG=true
```
5. **Install and run Node**:
```bat
cd C:\xampp\htdocs\apilageai\node
npm install
npm run dev
```
6. **Open**:
- `https://apilageai.lk/`
- `https://apilageai.lk/app`

## Uploads and Permissions
- Uploads are stored in `uploads/userimg` and `uploads/genimg`.
- Ensure these folders are writable by the web server user.

## Server Deployment (Apache)
1. **Upload the project** to your server document root.
2. **Set the domain vhost** to point to the project folder.
3. **Enable Apache rewrite** and allow `.htaccess`.
4. **Create the database** and import `apilageai_main_db.sql`.
5. **Set `.env` for production**:
```env
APP_URL=https://yourdomain.com
NODE_API_BASE=https://yourdomain.com:5001
NODE_ENV=production
APP_DEBUG=false
DB_HOST=your_db_host
DB_PORT=3306
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=apilageai_main_db
SMTP_HOST=your_smtp_host
SMTP_PORT=587
SMTP_USER=your_smtp_user
SMTP_PASS=your_smtp_password
```
6. **Install and run Node**:
```sh
cd /var/www/yourdomain.com/node
npm install --production
npm run dev
```
7. **Reverse proxy Node** (optional) using Apache or Nginx, or expose port `5001`.

## Server Deployment (Nginx)
1. **Serve PHP** with PHP-FPM.
2. **Add rewrites** to map `/app`, `/auth`, `/images`, `/pay` to PHP files.
3. **Proxy Node** to `apilageai.lk`.
4. **Ensure uploads are writable** by the PHP-FPM user.

## Debugging
- **PHP error log (project)**: `backend/error.log`
- **MAMP Apache log**: `/Applications/MAMP/logs/apache_error.log`
- **MAMP PHP log**: `/Applications/MAMP/logs/php_error.log`
- **Node log**: `node/error.log`

**Common Fixes**
- **404 on `/app` or `/auth`**: confirm `mod_rewrite` is enabled and `.htaccess` is allowed.
- **DB access denied**: verify `.env` DB credentials and port.
- **Google login 500**: check PHP error log. Common causes are SMTP timeouts or missing PHP extensions.
- **Uploads failing**: verify folder permissions on `uploads/`.

## Security Notes
- Never commit `.env` or secrets to Git.
- Keep `backend/config.php` out of version control.
- Rotate API keys and SMTP credentials before production.
