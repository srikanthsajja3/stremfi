# StremFi Client & Device Management Portal

A premium management portal for operators and admins to manage customers, customer devices, and process recharges.

## Technology Stack
* **Frontend:** React (Vite, SPA, custom glassmorphism styling)
* **Backend:** PHP (REST API, token-based authentication, custom JWT logic)
* **Database:** MySQL / MariaDB (using PDO connection)

---

## Folder Structure
```
stremfi/
├── README.md
├── backend/
│   ├── config/
│   │   └── database.php  # Database connection credentials
│   ├── index.php         # REST API Router, Controller & CORS headers
│   └── setup_db.php      # Automation tool to import database dump
└── frontend/
    ├── index.html
    ├── vite.config.js
    └── src/
        ├── index.css     # Global design system variables & fonts
        ├── App.css       # Layout styles for sidebar, modals, and grids
        ├── App.jsx       # Main React codebase (state, routes, modals)
        └── main.jsx
```

---

## Installation & Setup Instructions

### 1. Prerequisite Installations
If you don't have PHP and MariaDB/MySQL installed locally, install them using Homebrew:
```bash
brew install php mariadb
```

Start the MariaDB/MySQL database server:
```bash
brew services start mariadb
```

### 2. Setup the Database
Run our automated database setup script to create the database and import the `stremfi_backup.sql` file:
```bash
php backend/setup_db.php
```

### 3. Run the Backend API
Start PHP's built-in server inside the `backend` directory:
```bash
php -S localhost:8000 -t backend/
```
Your backend API will now be listening on `http://localhost:8000`.

### 4. Run the Frontend App
Navigate to the `frontend` folder, install the packages (scaffolded), and launch the Vite server:
```bash
cd frontend
npm run dev
```
Your web app will start on `http://localhost:5173`. Open it in your browser!

---

## Log In Credentials
Since the imported seed data contains sample operators, you can sign in with:
* **Operator Email:** `operator@example.com` (or `superadmin@example.com` for full admin rights)
* **Password:** `123456`
