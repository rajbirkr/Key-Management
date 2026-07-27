========================================================================
   ATLAS HUB - Campus Space Manager (Beginner PHP + MySQL + HTML/CSS/JS)
========================================================================

FILES INCLUDED IN THIS PROJECT:
-------------------------------
1. database.sql   -> MySQL database creation and table insert script
2. config.php     -> Database connection & PHP session configuration
3. header.php     -> Navigation header component
4. footer.php     -> Footer component
5. index.php      -> Main user dashboard showing floors & user booking logs
6. floor.php      -> Specific floor room viewer & booking submission form
7. admin.php      -> Admin panel for approving or rejecting room requests
8. login.php      -> User and Admin login page
9. register.php   -> Account registration page
10. logout.php    -> Logout script
11. style.css     -> Simple, clean beginner CSS stylesheet
12. script.js     -> Simple JavaScript helper for room clicks


HOW TO RUN THIS BACKEND ON YOUR COMPUTER (XAMPP / LOCALHOST GUIDE):
-------------------------------------------------------------------

STEP 1: Download & Install XAMPP
--------------------------------
1. Download XAMPP for Windows / Mac from https://www.apachefriends.org/
2. Install XAMPP and open the XAMPP Control Panel.
3. Click "Start" on both "Apache" and "MySQL".


STEP 2: Setup Database in phpMyAdmin
------------------------------------
1. Open your browser and go to: http://localhost/phpmyadmin/
2. Click on "Import" tab at the top menu.
3. Choose the "database.sql" file from this folder.
4. Click "Go" at the bottom to create the database (`campus_booking`) and tables.


STEP 3: Move Project Files to htdocs
------------------------------------
1. Copy all the files from this folder.
2. Paste them inside your XAMPP htdocs folder:
   - On Windows: C:\xampp\htdocs\campus-booking\
   - On Mac: /Applications/XAMPP/htdocs/campus-booking/


STEP 4: Open in Web Browser
---------------------------
1. Open your browser and type:
   http://localhost/campus-booking/login.php

2. Use the Demo Credentials:
   - User Role Login:
     Phone: 9876543210
     Password: user

   - Admin Role Login:
     Phone: 1234567890
     Password: admin


SUMMARY OF WHAT THE BACKEND DOES:
---------------------------------
• config.php connects PHP to MySQL database using standard mysqli_connect().
• login.php & register.php check user records from `users` table.
• index.php displays floor status and queries the `bookings` table.
• floor.php inserts new room requests into the `bookings` table as "pending".
• admin.php updates the request status to "approved" or "rejected".
