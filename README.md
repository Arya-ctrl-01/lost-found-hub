# University Lost & Found Hub — PHP + MySQL + Bootstrap 5

A complete lost and found management system for universities.

---

## 🛠️ COMPLETE SETUP GUIDE (Step-by-Step)

### STEP 1: Install XAMPP

1. Download XAMPP from **https://www.apachefriends.org/**
2. Install it (Windows: `C:\xampp`, Mac: `/Applications/XAMPP`)
3. Open **XAMPP Control Panel**
4. Click **Start** next to **Apache** (should show green)
5. Click **Start** next to **MySQL** (should show green)
6. Verify: open browser → go to **http://localhost** → you should see the XAMPP welcome page

### STEP 2: Copy Project Files

1. Navigate to XAMPP's web root folder:
   - **Windows:** `C:\xampp\htdocs\`
   - **Mac:** `/Applications/XAMPP/htdocs/`
   - **Linux:** `/opt/lampp/htdocs/`

2. Create a folder named `lost_found_hub` inside `htdocs`

3. Copy ALL files from this `php_project` folder into `lost_found_hub`:
   ```
   C:\xampp\htdocs\lost_found_hub\
   ├── admin/
   ├── api/
   ├── assets/
   ├── config/
   ├── includes/
   ├── uploads/
   ├── index.php
   ├── login.php
   ├── register.php
   ├── dashboard.php
   ├── report_lost.php
   ├── report_found.php
   ├── item_details.php
   └── notifications.php
   ```

### STEP 3: Create the Database

1. Open browser → go to **http://localhost/phpmyadmin**
2. Click **"New"** in the left sidebar
3. Type database name: `lost_found_hub`
4. Select collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

### STEP 4: Import the SQL Schema

1. In phpMyAdmin, click on `lost_found_hub` database (left sidebar)
2. Click the **"Import"** tab at the top
3. Click **"Choose File"** → navigate to `config/database.sql`
   - Full path: `C:\xampp\htdocs\lost_found_hub\config\database.sql`
4. Click **"Go"** at the bottom
5. You should see: ✅ "Import has been successfully finished"

**Alternative:** Click the **"SQL"** tab and paste the contents of `database.sql` manually, then click "Go".

### STEP 5: Create the Uploads Folder

1. Navigate to `C:\xampp\htdocs\lost_found_hub\`
2. Create a folder named `uploads` if it doesn't already exist
3. On Mac/Linux, set permissions: `chmod 777 uploads`

### STEP 6: Verify Database Connection

1. Open `config/db.php` in a text editor
2. Check these values match your XAMPP setup:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'lost_found_hub';
   $DB_USER = 'root';
   $DB_PASS = '';    // empty by default in XAMPP
   ```
3. Save and close

### STEP 7: Access the Application! 🚀

Open browser → go to: **http://localhost/lost_found_hub/**

---

## 🔐 Default Accounts

| Role    | Email                  | Password   |
|---------|------------------------|------------|
| Admin   | admin@university.edu   | admin123   |
| Student | john@university.edu    | admin123   |
| Student | jane@university.edu    | admin123   |

*(All sample accounts use `admin123` as password for testing)*

---

## 📁 Complete Folder Structure

```
lost_found_hub/
│
├── config/
│   ├── db.php              ← Database connection + helper functions
│   └── database.sql        ← SQL schema (import this first!)
│
├── includes/
│   ├── navbar.php          ← Shared navigation bar
│   ├── head.php            ← Shared <head> includes (CSS, fonts)
│   ├── scripts.php         ← Shared JS includes (Bootstrap, Leaflet)
│   ├── alerts.php          ← Shared success/error alert banners
│   └── item_card.php       ← Reusable item card component
│
├── assets/
│   ├── css/
│   │   └── style.css       ← All custom styles (matches React design)
│   └── js/
│       └── app.js          ← Image preview, map init, utilities
│
├── api/
│   ├── login_process.php   ← Handles login form POST
│   ├── register_process.php← Handles register form POST
│   ├── add_item.php        ← Handles both lost/found item creation
│   ├── claim_item.php      ← Submit a claim with QR generation
│   ├── admin_action.php    ← Approve/reject/delete items
│   ├── claim_action.php    ← Approve/reject/collect claims
│   ├── verify_qr.php       ← QR code verification
│   └── logout.php          ← Destroy session and redirect
│
├── admin/
│   └── admin_dashboard.php ← Admin panel (sidebar, tables, QR verify)
│
├── uploads/                ← Uploaded item images stored here
│
├── index.php               ← Landing page (hero + features + recent items)
├── login.php               ← Login page
├── register.php            ← Registration page
├── dashboard.php           ← Main dashboard (search + lost/found panels)
├── report_lost.php         ← Report lost item form + map
├── report_found.php        ← Report found item form + map
├── item_details.php        ← Item details + map + claim modal + matches
├── notifications.php       ← User notifications list
│
└── README.md               ← This file
```

---

## ✅ Features Included

| Feature                    | Status |
|----------------------------|--------|
| User Registration & Login  | ✅     |
| Password Hashing (bcrypt)  | ✅     |
| Session Management         | ✅     |
| Report Lost Items          | ✅     |
| Report Found Items         | ✅     |
| Image Upload + Preview     | ✅     |
| Interactive Map (Leaflet)  | ✅     |
| Global Search + Filter     | ✅     |
| Item Details Page          | ✅     |
| Claim System with Modal    | ✅     |
| QR Code Generation         | ✅     |
| QR Code Verification       | ✅     |
| Admin Dashboard            | ✅     |
| Approve/Reject Items       | ✅     |
| Manage Claims              | ✅     |
| User Management            | ✅     |
| In-App Notifications       | ✅     |
| Smart Match Suggestions    | ✅     |
| Responsive (Mobile-ready)  | ✅     |
| Sample Data Included       | ✅     |

---

## 🎨 Tech Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- **Backend:** PHP 7.4+ (Core PHP, no frameworks)
- **Database:** MySQL (via phpMyAdmin)
- **Maps:** Leaflet.js (OpenStreetMap)
- **QR Codes:** goqr.me API (external QR image generation)
- **Server:** XAMPP (Apache + MySQL)

---

## 🐛 Troubleshooting

| Problem                         | Solution                                           |
|---------------------------------|----------------------------------------------------|
| "Connection failed" error       | Make sure XAMPP MySQL is running                   |
| "Database not found"            | Create `lost_found_hub` database in phpMyAdmin     |
| "Table doesn't exist"           | Import `config/database.sql` via phpMyAdmin        |
| Images not uploading            | Create `uploads/` folder with write permissions    |
| Page shows PHP code             | Make sure XAMPP Apache is running                  |
| CSS/JS not loading              | Check folder is named `lost_found_hub` in htdocs   |
| Map not showing                 | Make sure you have internet (loads from CDN)        |
| "Access denied" on admin        | Login with admin@university.edu / admin123         |
| Blank white page                | Enable PHP error display in `php.ini`: `display_errors = On` |
