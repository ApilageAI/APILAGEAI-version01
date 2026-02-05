# Apilage AI (Local Setup)

This repo contains the PHP web app and the Node backend (Socket/AI). This guide sets up a full local environment on **macOS** (MAMP) and **Windows** (XAMPP/WAMP).

## Requirements
- PHP 8.1+ (MAMP/XAMPP/WAMP)
- MySQL/MariaDB
- Node.js 18+ (recommended)
- npm

## Ports (Local)
- Apache: `http://localhost:8888`
- MySQL: `127.0.0.1:8889` (MAMP default)
- Node API: `http://localhost:5001`

## macOS (MAMP)
1. **Point MAMP Document Root** to the project:
   - `/Users/yourname/Downloads/apilageai`
2. **Ensure Apache rewrite is enabled** and `AllowOverride All` is on.
3. **Create DB + import SQL**:
   ```sh
   /Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot -e "DROP DATABASE IF EXISTS apilageai_main_db; CREATE DATABASE apilageai_main_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   /Applications/MAMP/Library/bin/mysql80/bin/mysql -h 127.0.0.1 -P 8889 -u root -proot apilageai_main_db < /Users/yourname/Downloads/apilageai/apilageai_main_db.sql
   ```
4. **Check PHP DB config** (already set for MAMP):
   - `backend/config.php`
5. **Node env**:
   - Copy `node/.env.example` to `node/.env` (if exists) and fill secrets.
6. **Install + run Node**:
   ```sh
   cd /Users/yourname/Downloads/apilageai/node
   npm install
   npm run dev
   ```
7. **Open**:
   - Main: `http://localhost:8888/`
   - App: `http://localhost:8888/app`
   - Node health: `http://localhost:5001/health`

## Windows (XAMPP/WAMP)
1. **Move project into htdocs/www**:
   - Example: `C:\xampp\htdocs\apilageai`
2. **Point Apache Document Root** to the project folder (or use a VirtualHost).
3. **Create DB + import SQL**:
   ```bat
   mysql -h 127.0.0.1 -P 3306 -u root -p
   ```
   ```sql
   DROP DATABASE IF EXISTS apilageai_main_db;
   CREATE DATABASE apilageai_main_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```
   ```bat
   mysql -h 127.0.0.1 -P 3306 -u root -p apilageai_main_db < C:\xampp\htdocs\apilageai\apilageai_main_db.sql
   ```
4. **Update PHP DB config** if needed:
   - `backend/config.php`
5. **Node env**:
   - Copy `node/.env.example` to `node/.env` (if exists) and fill secrets.
6. **Install + run Node**:
   ```bat
   cd C:\xampp\htdocs\apilageai\node
   npm install
   npm run dev
   ```
7. **Open**:
   - Main: `http://localhost:8888/` (or Apache port)
   - App: `http://localhost:8888/app`
   - Node health: `http://localhost:5001/health`

## Notes
- Uploads are stored in `uploads/userimg` and `uploads/genimg`.
- If Node fails, check `node/error.log`.
- If PHP fails, check MAMP/XAMPP logs.

## Common Fixes
- **Recaptcha error**: ensure your site key and secret are correct and match the domain.
- **Uploads failing**: confirm `uploads/userimg` and `uploads/genimg` exist and are writable.
- **API errors**: ensure Node is running and `NODE_API_BASE` points to `http://localhost:5001`.
