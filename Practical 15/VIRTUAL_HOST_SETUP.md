# Intermediate Extension: Apache Virtual Host Configuration (`studenthub.local`)

This guide explains how to configure a custom local domain `http://studenthub.local` pointing directly to the StudentHub portal on XAMPP for Windows.

---

### Step 1: Edit Apache Virtual Hosts Configuration
1. Open the file `C:\xampp\apache\conf\extra\httpd-vhosts.conf` in an editor with Administrator privileges.
2. Ensure the following line is uncommented in `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```
3. Append the following virtual host block to the bottom of `httpd-vhosts.conf`:
   ```apache
   # Default Localhost
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs"
       ServerName localhost
   </VirtualHost>

   # StudentHub Custom Virtual Host
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/StudentHub"
       ServerName studenthub.local
       ServerAlias www.studenthub.local
       <Directory "C:/xampp/htdocs/StudentHub">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       ErrorLog "logs/studenthub-error.log"
       CustomLog "logs/studenthub-access.log" common
   </VirtualHost>
   ```

---

### Step 2: Update Windows Hosts File
1. Open Notepad as **Administrator** (Start Menu -> search Notepad -> right-click -> "Run as administrator").
2. Open the file: `C:\Windows\System32\drivers\etc\hosts`.
3. Add the following entry at the end of the file:
   ```hosts
   127.0.0.1   studenthub.local
   127.0.0.1   www.studenthub.local
   ```
4. Save the file.

---

### Step 3: Restart Apache & Test
1. Open the **XAMPP Control Panel**.
2. Click **Stop** on the Apache service, then click **Start**.
3. Open your browser and navigate to:
   ```
   http://studenthub.local/
   ```
4. The StudentHub portal will load under your custom local domain!
