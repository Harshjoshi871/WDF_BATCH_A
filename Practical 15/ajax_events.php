<?php
/**
 * Practical 15: Asynchronous AJAX Event Explorer (Connecting Practical 13)
 * StudentHub Full-Stack Portal
 */
$page_title = "AJAX Event Hub (SPA)";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="toolbar">
        <div>
            <h1>Asynchronous Event Hub (SPA)</h1>
            <p>Single Page Interface performing dynamic CRUD operations via Fetch API without page reload.</p>
        </div>
        <div>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <button id="openAjaxModalBtn" class="btn btn-primary">+ Add Event (AJAX)</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="ajaxSearchInput" placeholder="Instant debounced search (300ms)...">
        </div>

        <div class="filter-box">
            <label for="ajaxCategoryFilter">Category:</label>
            <select id="ajaxCategoryFilter">
                <option value="">All Categories</option>
                <option value="Technical">Technical</option>
                <option value="Workshop">Workshop</option>
                <option value="Cultural">Cultural</option>
                <option value="Sports">Sports</option>
                <option value="Seminar">Seminar</option>
            </select>
        </div>

        <button id="ajaxRefreshBtn" class="btn btn-outline">🔄 Refresh</button>
    </div>

    <!-- Loading State -->
    <div id="ajaxSpinner" class="spinner-container" style="display: none;">
        <div class="spinner"></div>
        <p>Loading events via API...</p>
    </div>

    <!-- Error State -->
    <div id="ajaxError" class="alert alert-danger" style="display: none;"></div>

    <!-- Grid Container -->
    <div id="ajaxEventGrid" class="event-grid"></div>

    <!-- Empty State -->
    <div id="ajaxEmpty" class="empty-state" style="display: none;">
        <p>No events found.</p>
    </div>
</div>

<!-- Modal for AJAX Add/Edit -->
<div id="ajaxEventModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="ajaxModalTitle">Create Campus Event</h3>
            <button type="button" class="close-btn" id="closeAjaxModalBtn">&times;</button>
        </div>

        <form id="ajaxForm">
            <input type="hidden" id="ajaxEventId">

            <div class="form-group">
                <label for="ajaxTitle">Event Title *</label>
                <input type="text" id="ajaxTitle" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="ajaxCategory">Category *</label>
                    <select id="ajaxCategory" required>
                        <option value="Technical">Technical</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Sports">Sports</option>
                        <option value="Seminar">Seminar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ajaxDate">Date *</label>
                    <input type="date" id="ajaxDate" required>
                </div>
            </div>

            <div class="form-group">
                <label for="ajaxDesc">Description</label>
                <textarea id="ajaxDesc" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="ajaxStatus">Status</label>
                <select id="ajaxStatus">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelAjaxModalBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveAjaxBtn">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<script>
    const isAdmin = <?= ($user && $user['role'] === 'admin') ? 'true' : 'false' ?>;
</script>
<script src="js/ajax_portal.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
