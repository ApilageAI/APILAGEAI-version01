# Security Guidelines for ApilageAI

## 🔒 Overview

This document outlines critical security practices for the ApilageAI project. **Never commit sensitive data to version control.**

---

## ⚠️ Never Commit

The `.gitignore` file protects the following from being committed:

- **Configuration files:** `config.php`, `bootstrap.php`
- **Environment files:** `.env`, `.env.local`, `.env.*.local`
- **Credentials:** Database passwords, API keys, tokens
- **SSL Certificates:** `*.cert`, `*.key`, `*.pem`
- **Debug files:** `*_debug.txt`, `*.log`
- **User uploads:** `doc_text/`, `stats/`, user data
- **IDE files:** `.vscode/`, `.idea/`

---

## 🚀 Setup Instructions

### 1. **Initial Setup**

```bash
# Clone the repository
git clone https://github.com/yourusername/APILAGEAI-HIGH-SECURED-2026.git
cd APILAGEAI-HIGH-SECURED-2026

# Create configuration from template
cp config.example.php apilageai.lk/backend/config.php
cp .env.example .env.local
```

### 2. **Configure Environment**

Edit `.env.local` with your local values:

```bash
# Edit with your editor
nano .env.local
# or
vim .env.local
```

Edit `config.php` with your environment-specific settings:

```bash
nano apilageai.lk/backend/config.php
```

### 3. **SSH Keys (if applicable)**

```bash
# Generate SSH keys for deployment
ssh-keygen -t rsa -b 4096 -f ~/.ssh/apilageai_deploy
# Do NOT commit private keys!
```

---

## 🔐 Credential Management Best Practices

### **Database Credentials**
- Use strong, unique passwords (min 16 characters)
- Use environment variables instead of hardcoding
- Rotate credentials regularly
- Use different credentials per environment (dev/staging/prod)

### **API Keys**
- Store in `.env.local` only
- Rotate API keys monthly
- Use different keys per environment
- Never share keys in chat, email, or documentation

### **SSL Certificates**
- Store in a secure location outside version control
- Use environment-specific certificates
- Auto-renew certificates before expiration
- Backup certificates securely

### **SSH Keys**
- Generate unique keys per deployment
- Protect private keys with passphrases
- Use SSH key management tools (SSH agent)
- Rotate keys quarterly

---

## 📝 Sensitive File Templates

### Create `config.php` from template
```bash
cp config.example.php apilageai.lk/backend/config.php
# Then edit with your actual credentials
```

### Create `.env.local` from template
```bash
cp .env.example .env.local
# Then edit with your actual credentials
```

---

## 🔍 Pre-Commit Verification

Before committing, verify no sensitive data is exposed:

```bash
# Check git staging area for secrets
git diff --cached | grep -i "password\|api_key\|secret\|token"

# Check uncommitted changes
git diff | grep -i "password\|api_key\|secret\|token"

# List all files that would be committed
git diff --cached --name-only
```

---

## 🛡️ Environment Variables

### Development
```bash
APP_ENV=development
DB_HOST=localhost
GEMINI_API_KEY=test_key_dev
```

### Staging
```bash
APP_ENV=staging
DB_HOST=staging-db.internal
GEMINI_API_KEY=test_key_staging
```

### Production
```bash
APP_ENV=production
DB_HOST=prod-db.internal
GEMINI_API_KEY=live_api_key
# Use secure vaults for production credentials
```

---

## 🚨 If Credentials Are Exposed

**Immediate Actions:**

1. **Revoke credentials immediately**
   ```bash
   # Database: Change user password
   # API Keys: Rotate all exposed keys
   # SSH Keys: Disable and regenerate
   ```

2. **Check git history**
   ```bash
   # Find when credentials were committed
   git log --all -p -S "password" | head -100
   ```

3. **Remove from history** (if leaked)
   ```bash
   # Use git-filter-repo or BFG Repo-Cleaner
   # Warning: This rewrites history!
   git filter-repo --path config.php --invert-paths
   ```

4. **Force push** (only if necessary)
   ```bash
   # Use with extreme caution
   git push --force-with-lease
   ```

5. **Notify team** and document the incident

---

## 📋 .gitignore Structure

```
config files          → Never commit credentials
environment files     → .env* files ignored
logs & debug          → Debug output ignored
user data & uploads   → User-generated data ignored
dependencies          → node_modules, vendor ignored
ide/os files          → Editor files ignored
```

---

## ✅ Pre-Deployment Checklist

- [ ] `.env.local` created and configured
- [ ] `config.php` created with correct credentials
- [ ] No `.env` or `.env.local` in git history
- [ ] No database passwords in code
- [ ] No API keys in code or config files
- [ ] SSL certificates properly configured
- [ ] All credentials rotated for production
- [ ] Deployment scripts use environment variables
- [ ] Team members have access to credentials via secure vault

---

## 🔗 Additional Resources

- [OWASP: Credential Management](https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html)
- [Git Security Best Practices](https://git-scm.com/book/en/v2/Git-Tools-Signing-Your-Work)
- [GitHub Secret Scanning](https://docs.github.com/en/code-security/secret-scanning)

---

**Last Updated:** April 2026  
**Maintainer:** Development Team  
**Review Frequency:** Quarterly
