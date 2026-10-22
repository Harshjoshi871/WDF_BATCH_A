# Practical 12: Event Management CRUD with Poster Upload and File Validation

## 1. Problem Definition
Develop an event management CRUD module allowing administrators to publish, view, update, and delete campus events. Implement secure file upload handling for event posters with rigorous server-side MIME-type and size validation, automated unique file renaming, and execution protection.

## 2. Key Questions & Viva Analysis
1. **Are add, update, and delete functionalities implemented correctly?**
   - **Add:** Uploads poster, verifies size/MIME, inserts event record via prepared statement.
   - **Update:** Prepopulates form; allows updating event text fields while retaining current poster or uploading a replacement (old poster is unlinked from disk).
   - **Delete:** Removes database record and automatically deletes the associated poster image file from the `uploads/` directory.
2. **Is the UI linked with the database correctly?**
   - Yes, event cards display dynamic database data: title, category pill, formatted event date, description, poster thumbnail, and registration status.
3. **Are poster uploads validated for type and size?**
   - Yes:
     - **Size Check:** Enforces 2 MB maximum limit (`$file_size > 2 * 1024 * 1024`).
     - **Extension Check:** Permitted extensions: `.jpg`, `.jpeg`, `.png`, `.webp`.
     - **MIME Inspection:** Uses PHP `finfo_file(FILEINFO_MIME_TYPE)` to verify raw binary image signatures, defeating extension spoofing attacks (e.g. `script.php.png`).
4. **Are success and failure messages displayed?**
   - Yes, session-based flash banners (`$_SESSION['flash_msg']`) provide immediate feedback on creation, modification, or deletion.

## 3. Extensions Implemented
- **Intermediate Extension (Category & Status Filters):** Filter events by domain category (`Technical`, `Workshop`, `Cultural`, `Sports`, `Seminar`) and registration status (`Open` / `Closed`).
- **Advanced Extension (Hardening & Cryptographic Renaming):**
  - Filenames are randomized with `poster_` + `bin2hex(random_bytes(8))` to eliminate collisions and prevent directory traversal.
  - Upload folder contains `.htaccess` explicitly disabling script execution (`php_flag engine off` and denying `.php`/`.phtml` requests).
  - Actions logged into `audit_logs` table.

## 4. Test Scenarios
| Test ID | Test Scenario | Uploaded Input | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | Valid Event Creation | Event details + 500KB `.jpg` image | Event created, poster saved, shown in grid | Pass |
| TC-02 | Oversized Image | Image size > 2MB (e.g., 3.5MB file) | Blocked with "Poster file size exceeds 2 MB limit" | Pass |
| TC-03 | Invalid Extension | Document file `.pdf` or `.docx` | Blocked with extension validation error | Pass |
| TC-04 | Spoofed MIME Type | Text file renamed to `payload.jpg` | Blocked by PHP `finfo_file` MIME inspection | Pass |
| TC-05 | Poster Replacement | Update event with new PNG image | New poster stored, old poster removed from disk | Pass |
| TC-06 | Event Deletion | Click "Delete Event" | Record removed and disk file deleted | Pass |
