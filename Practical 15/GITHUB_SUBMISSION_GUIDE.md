# GitHub Submission and Repository Version Control Guide

This guide outlines the version control workflow and Git commit history for submitting the StudentHub semester project for **ITUE203 - Web Development Frameworks**.

---

### 1. Recommended Repository Folder Layout
```
studenthub-portal/
├── Practical 1/              # Project Initiation, Sitemap, Wireframes & README
├── Practical 2/              # Semantic HTML5 Pages (10+ Skeletons)
├── Practical 3/              # Responsive CSS3, Grid & Flexbox Styling
├── Practical 4/              # JavaScript DOM Manipulation & Interactivity
├── Practical 5/              # Registration Form with Regex Validations
├── Practical 6/              # Fetch API with JSON (Filter, Sort, Pagination)
├── Practical 7/              # PHP Form Processing with CSV File Storage
├── Practical 8/              # MySQL Schema Design, ER Model & DB Connectivity
├── Practical 9/              # Secure User Registration (Bcrypt & Duplicate Check)
├── Practical 10/             # Session Login/Logout, RBAC, Timeout & Remember-Me
├── Practical 11/             # Student Management CRUD (Search, Filter, CSV)
├── Practical 12/             # Event Management CRUD & Poster Upload Validation
├── Practical 13/             # RESTful JSON API & Asynchronous AJAX CRUD
├── Practical 14/             # Admin Dashboard, Dynamic Menus & Audit Logs
├── Practical 15/             # Full Integration, Local Deployment & Documentation
├── .gitignore
└── README.md
```

---

### 2. Git Setup & Initialization Commands
Open PowerShell or Git Bash inside your project directory:

```bash
# 1. Initialize Git repository
git init

# 2. Add remote GitHub repository
git remote add origin https://github.com/your-username/studenthub-portal.git

# 3. Create .gitignore file
```

Example `.gitignore`:
```gitignore
# Operating System files
Thumbs.db
Desktop.ini
.DS_Store

# Temporary and log files
*.log
*.tmp

# Uploaded dynamic posters (sample test uploads)
# Keep placeholder graphics
/uploads/poster_*
!/uploads/default_poster.svg
!/uploads/.htaccess

# IDE directories
.vscode/
.idea/
```

---

### 3. Progressive Commit History Guideline
Commit each practical step sequentially with standard conventional commit messages:

```bash
# Practical 1 to 8 Initial Foundation
git add "Practical 1/" "Practical 2/" "Practical 3/" "Practical 4/" "Practical 5/" "Practical 6/" "Practical 7/" "Practical 8/"
git commit -m "feat: complete practicals 1-8 foundation (UI, validations, JSON & schema)"

# Practical 9
git add "Practical 9/"
git commit -m "feat(p9): implement secure registration with bcrypt hashing, duplicate email checks and CSRF"

# Practical 10
git add "Practical 10/"
git commit -m "feat(p10): implement secure login/logout with sessions, RBAC, 15m timeout and remember-me tokens"

# Practical 11
git add "Practical 11/"
git commit -m "feat(p11): build student management CRUD with live search, course filters, pagination and CSV export"

# Practical 12
git add "Practical 12/"
git commit -m "feat(p12): add event management CRUD with poster upload, MIME verification and .htaccess protection"

# Practical 13
git add "Practical 13/"
git commit -m "feat(p13): develop RESTful JSON API and asynchronous AJAX CRUD single-page application"

# Practical 14
git add "Practical 14/"
git commit -m "feat(p14): build admin dashboard with dynamic role menus, SQL aggregation cards and audit logs"

# Practical 15
git add "Practical 15/"
git commit -m "feat(p15): integrate full-stack StudentHub portal, deployment guides, test reports and documentation"

# Final Push
git branch -M main
git push -u origin main
```
