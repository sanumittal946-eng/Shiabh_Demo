# Sahib Classes - Educational & Coaching Institute Website

A complete, professional, and mobile-responsive educational website built natively with PHP 8.2 and MySQL. Features dynamic course management, SEO integration, custom student portal, and a robust backend.

## Tech Stack
- **Frontend**: HTML5, CSS3, Vanilla JS, Bootstrap 5.3
- **Backend**: PHP 8.2+ (PDO with prepared statements)
- **Database**: MySQL 8.0+
- **Libraries/Assets**: FontAwesome 6 (CDN), Google Fonts, AOS Animations, Chart.js, Masonry.js

## Folder Structure
```text
/
├── admin/               # Admin dashboard templates (UI mockups)
├── assets/
│   ├── css/             # Custom styles (style.css, responsive.css)
│   ├── js/              # Custom scripts (main.js)
│   └── images/          # Image assets
├── includes/
│   ├── config.php       # App settings & session config
│   ├── db.php           # PDO Database singleton class
│   ├── functions.php    # Utility functions (Sanitization, CSRF, etc.)
│   ├── header.php       # Global responsive header
│   └── footer.php       # Global mega footer
├── sql/
│   ├── schema.sql       # Database schema creation
│   └── seed.sql         # Dummy data for immediate testing
├── .htaccess            # URL rewriting & Security blocks
├── sitemap.xml          # XML Sitemap for SEO
└── *.php                # Main frontend pages
```

## Setup Instructions

### 1. Database Configuration
1. Open your MySQL client / phpMyAdmin.
2. Create a new database named `edu_website`.
3. Import `sql/schema.sql` to build the tables structure.
4. Import `sql/seed.sql` to populate the database with extensive dummy data.

### 2. Environment Setup
1. Move/Clone the project folder into your local server's document root (e.g., `htdocs`, `www`). Make sure the folder is named `edu_website` if you rely on the `.htaccess` RewriteBase.
2. Open `includes/config.php`.
3. Update the database credentials if necessary (Default is localhost, root user, no password).
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'edu_website');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Define the base URL accurately.
   ```php
   define('BASE_URL', 'http://localhost/edu_website/');
   ```

### 3. Start the Application
- Access the homepage at `http://localhost/edu_website/`
- Ensure Apache matches the configuration (AllowOverride All must be enabled for `.htaccess` URL rewriting to function).

## Default Accounts (for local testing)
**Admin:** 
- Email: admin@sahibclasses.com
- Password: password123

**Student:**
- Email: student1@example.com
- Password: password123

## Key Features
- **Security Mechanisms**: CSRF tokens embedded in all forms, input sanitization helpers, PDO prepared queries, Honeypot fields.
- **Performance**: CDN-based heavy libraries, vanilla CSS approach without bulky tailwind CSS where unnecessary.
- **Dynamic Frontend**: Filterable galleries, live search utilities, Animated on Scroll (AOS), masonry layouts.
