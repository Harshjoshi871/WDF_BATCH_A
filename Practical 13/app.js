/**
 * Practical 13: Asynchronous AJAX / Fetch API CRUD Controller
 * StudentHub Portal - CHARUSAT ITUE203
 */

document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const eventGrid = document.getElementById("eventGrid");
    const loadingSpinner = document.getElementById("loadingSpinner");
    const errorState = document.getElementById("errorState");
    const emptyState = document.getElementById("emptyState");
    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const refreshBtn = document.getElementById("refreshBtn");

    // Modal Elements
    const eventModal = document.getElementById("eventModal");
    const modalTitle = document.getElementById("modalTitle");
    const eventForm = document.getElementById("eventForm");
    const eventIdInput = document.getElementById("eventId");
    const titleInput = document.getElementById("modalEventTitle");
    const categoryInput = document.getElementById("modalEventCategory");
    const dateInput = document.getElementById("modalEventDate");
    const descInput = document.getElementById("modalEventDesc");
    const statusInput = document.getElementById("modalEventStatus");

    const openAddModalBtn = document.getElementById("openAddModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cancelModalBtn = document.getElementById("cancelModalBtn");
    const toastContainer = document.getElementById("toastContainer");

    // Initial Load
    fetchEvents();

    // 1. Fetch Events Asynchronously from PHP JSON API
    async function fetchEvents() {
        const query = searchInput.value.trim();
        const category = categoryFilter.value;

        loadingSpinner.style.display = "block";
        errorState.style.display = "none";
        emptyState.style.display = "none";
        eventGrid.innerHTML = "";

        try {
            const url = `api.php?action=list&search=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`;
            const response = await fetch(url);

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP Error ${response.status}`);
            }

            const resData = await response.json();
            const events = resData.data || [];

            loadingSpinner.style.display = "none";

            if (events.length === 0) {
                emptyState.style.display = "block";
            } else {
                renderEventCards(events);
            }
        } catch (error) {
            loadingSpinner.style.display = "none";
            errorState.textContent = `API Communication Failure: ${error.message}`;
            errorState.style.display = "block";
            showToast(error.message, "error");
        }
    }

    // 2. Render Cards in the DOM
    function renderEventCards(events) {
        eventGrid.innerHTML = "";

        events.forEach(e => {
            const card = document.createElement("div");
            card.className = "ajax-card";
            card.id = `event-card-${e.id}`;

            const dateStr = e.event_date ? new Date(e.event_date).toLocaleDateString("en-US", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric"
            }) : "TBA";

            card.innerHTML = `
                <div class="card-top">
                    <span class="category-badge">${escapeHtml(e.category)}</span>
                    <span class="status-pill status-${escapeHtml(e.status)}">${escapeHtml(e.status.toUpperCase())}</span>
                </div>
                <h3 class="card-title">${escapeHtml(e.title)}</h3>
                <div class="card-date">🗓 ${dateStr}</div>
                <p class="card-desc">${escapeHtml(e.description || "No description provided.")}</p>
                <div class="card-actions">
                    <button class="btn-sm btn-edit" onclick="handleEdit(${e.id})">Edit</button>
                    <button class="btn-sm btn-delete" onclick="handleDelete(${e.id}, '${escapeHtml(addslashes(e.title))}')">Delete</button>
                </div>
            `;

            eventGrid.appendChild(card);
        });
    }

    // 3. Debounced Search (Intermediate Extension: 300ms delay)
    function debounce(func, delay = 300) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    searchInput.addEventListener("input", debounce(() => {
        fetchEvents();
    }, 300));

    categoryFilter.addEventListener("change", () => {
        fetchEvents();
    });

    refreshBtn.addEventListener("click", () => {
        fetchEvents();
        showToast("Events feed refreshed", "info");
    });

    // 4. Modal Open & Close Logic
    function openModal(isEdit = false) {
        eventModal.style.display = "flex";
        if (!isEdit) {
            modalTitle.textContent = "Create New Event (AJAX)";
            eventForm.reset();
            eventIdInput.value = "";
            statusInput.value = "open";
            categoryInput.value = "Technical";
        }
    }

    function closeModal() {
        eventModal.style.display = "none";
        eventForm.reset();
    }

    openAddModalBtn.addEventListener("click", () => openModal(false));
    closeModalBtn.addEventListener("click", closeModal);
    cancelModalBtn.addEventListener("click", closeModal);

    // Close on backdrop click
    eventModal.addEventListener("click", (e) => {
        if (e.target === eventModal) closeModal();
    });

    // 5. Create or Update Event via Async Fetch
    eventForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = eventIdInput.value;
        const isUpdate = id !== "";
        const action = isUpdate ? "update" : "create";

        const payload = {
            action: action,
            title: titleInput.value.trim(),
            category: categoryInput.value,
            event_date: dateInput.value,
            description: descInput.value.trim(),
            status: statusInput.value
        };

        if (isUpdate) {
            payload.id = parseInt(id, 10);
        }

        const saveBtn = document.getElementById("saveEventBtn");
        saveBtn.disabled = true;
        saveBtn.textContent = "Saving...";

        try {
            const response = await fetch("api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const resData = await response.json();

            if (!response.ok || !resData.success) {
                throw new Error(resData.message || "Failed to save event");
            }

            closeModal();
            showToast(resData.message, "success");
            fetchEvents(); // Refresh data asynchronously
        } catch (err) {
            showToast(err.message, "error");
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = "Save Event";
        }
    });

    // 6. Global Handlers for Edit and Delete
    window.handleEdit = async function (id) {
        try {
            const res = await fetch(`api.php?action=get&id=${id}`);
            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || "Unable to fetch event details");
            }

            const item = data.data;
            eventIdInput.value = item.id;
            titleInput.value = item.title;
            categoryInput.value = item.category;
            dateInput.value = item.event_date;
            descInput.value = item.description || "";
            statusInput.value = item.status;

            modalTitle.textContent = `Edit Event #${item.id}`;
            openModal(true);
        } catch (err) {
            showToast(err.message, "error");
        }
    };

    window.handleDelete = async function (id, title) {
        if (!confirm(`Are you sure you want to delete event '${title}' asynchronously?`)) {
            return;
        }

        try {
            const res = await fetch("api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "delete", id: id })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || "Failed to delete event");
            }

            showToast(data.message, "success");
            fetchEvents();
        } catch (err) {
            showToast(err.message, "error");
        }
    };

    // 7. Toast Notification Utility
    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span>${escapeHtml(message)}</span>`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("toast-fadeout");
            setTimeout(() => toast.remove(), 400);
        }, 3200);
    }

    // Helper functions
    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function addslashes(string) {
        return (string + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }
});
