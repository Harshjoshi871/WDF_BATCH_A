# Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload

## 1. Problem Definition
Build a modern RESTful JSON API in PHP and consume its endpoints using the JavaScript `fetch()` API. Perform full CRUD operations (List, Add, Edit, Delete) asynchronously within a Single Page Application (SPA) architecture without full browser reloads.

## 2. Key Questions & Viva Analysis
1. **Do PHP endpoints return valid JSON responses?**
   - Yes, the backend sets `header('Content-Type: application/json; charset=utf-8')` and formats all outputs via `json_encode()` with unified keys: `status`, `success`, `message`, `data`, and `timestamp`.
2. **Are GET and POST requests handled properly?**
   - Yes:
     - `GET ?action=list`: Reads query filters and delivers the JSON array of events.
     - `GET ?action=get&id=...`: Fetches details of a specific event.
     - `POST ?action=create`: Reads JSON payload, validates fields, inserts via prepared statements, and responds with HTTP 201 Created.
     - `POST ?action=update`: Updates an existing record and returns HTTP 200 OK.
     - `POST ?action=delete`: Removes the event record and responds with HTTP 200 OK.
3. **Are loading, success, and error states shown on the frontend?**
   - Yes:
     - **Loading State:** CSS spinner activates during network requests.
     - **Success State:** Non-blocking floating toast notifications confirm operations.
     - **Error State:** Red toast alerts and error banners appear if API calls or database operations fail.
4. **Is input validation performed before database operations?**
   - Yes, backend `api.php` strictly verifies title length, required dates, and enum values prior to running any MySQLi prepared statements.

## 3. Extensions Implemented
- **Intermediate Extension (Debounced AJAX Search):** `searchInput` attaches a 300ms debounce handler that cancels pending timeouts on keypress, delivering smooth real-time search without flooding the server with HTTP requests.
- **Advanced Extension (HTTP Status Codes & Centralized Error Handler):**
  - Explicit HTTP response codes: `200` (OK), `201` (Created), `400` (Bad Request), `404` (Not Found), `405` (Method Not Allowed), `500` (Internal Server Error).
  - All admin operations logged in `audit_logs`.

## 4. Test Scenarios
| Test ID | Test Scenario | Steps | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | Async Feed Fetch | Open `index.html` | Spinner displays, cards populate from JSON API without page reload | Pass |
| TC-02 | Debounced Search | Type "Hackathon" in search box | Search triggers after 300ms pause, filtering cards instantly | Pass |
| TC-03 | Async Event Creation | Click "Add New Event", submit form | Modal closes, success toast appears, card appended in DOM | Pass |
| TC-04 | Async Event Update | Click "Edit", modify date, submit | Modal closes, card updates in-place via DOM without refresh | Pass |
| TC-05 | Async Deletion | Click "Delete", confirm | Event removed from DB & DOM card disappears with toast | Pass |
| TC-06 | API Status Validation | Inspect Browser DevTools Network tab | Verify `application/json` header and HTTP 200/201 response codes | Pass |
