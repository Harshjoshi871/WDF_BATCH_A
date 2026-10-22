/**
 * Practical 15: Asynchronous AJAX Portal Controller
 * StudentHub Full-Stack Portal
 */

document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("ajaxEventGrid");
    const spinner = document.getElementById("ajaxSpinner");
    const errorBox = document.getElementById("ajaxError");
    const emptyBox = document.getElementById("ajaxEmpty");
    const searchInput = document.getElementById("ajaxSearchInput");
    const catFilter = document.getElementById("ajaxCategoryFilter");
    const refreshBtn = document.getElementById("ajaxRefreshBtn");

    const modal = document.getElementById("ajaxEventModal");
    const modalTitle = document.getElementById("ajaxModalTitle");
    const form = document.getElementById("ajaxForm");
    const idInput = document.getElementById("ajaxEventId");
    const titleInput = document.getElementById("ajaxTitle");
    const catInput = document.getElementById("ajaxCategory");
    const dateInput = document.getElementById("ajaxDate");
    const descInput = document.getElementById("ajaxDesc");
    const statusInput = document.getElementById("ajaxStatus");

    const openModalBtn = document.getElementById("openAjaxModalBtn");
    const closeModalBtn = document.getElementById("closeAjaxModalBtn");
    const cancelModalBtn = document.getElementById("cancelAjaxModalBtn");
    const toastContainer = document.getElementById("toastContainer");

    fetchEvents();

    async function fetchEvents() {
        const query = searchInput ? searchInput.value.trim() : "";
        const cat = catFilter ? catFilter.value : "";

        if (spinner) spinner.style.display = "block";
        if (errorBox) errorBox.style.display = "none";
        if (emptyBox) emptyBox.style.display = "none";
        if (grid) grid.innerHTML = "";

        try {
            const res = await fetch(`api.php?action=list&search=${encodeURIComponent(query)}&category=${encodeURIComponent(cat)}`);
            if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
            const json = await res.json();
            const events = json.data || [];

            if (spinner) spinner.style.display = "none";

            if (events.length === 0) {
                if (emptyBox) emptyBox.style.display = "block";
            } else {
                renderGrid(events);
            }
        } catch (err) {
            if (spinner) spinner.style.display = "none";
            if (errorBox) {
                errorBox.textContent = "Error loading data: " + err.message;
                errorBox.style.display = "block";
            }
            showToast(err.message, "error");
        }
    }

    function renderGrid(events) {
        if (!grid) return;
        grid.innerHTML = "";

        events.forEach(e => {
            const card = document.createElement("div");
            card.className = "ajax-card";

            const dateStr = e.event_date ? new Date(e.event_date).toLocaleDateString("en-US", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric"
            }) : "TBA";

            let actionsHtml = "";
            if (typeof isAdmin !== "undefined" && isAdmin) {
                actionsHtml = `
                    <div class="card-actions">
                        <button class="btn-sm btn-edit" onclick="editAjaxEvent(${e.id})">Edit</button>
                        <button class="btn-sm btn-delete" onclick="deleteAjaxEvent(${e.id}, '${escapeHtml(addslashes(e.title))}')">Delete</button>
                    </div>
                `;
            } else {
                actionsHtml = `
                    <div class="card-actions">
                        <a href="events.php" class="module-link">View in Events Feed &rarr;</a>
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="card-top">
                    <span class="category-badge">${escapeHtml(e.category)}</span>
                    <span class="status-pill status-${escapeHtml(e.status)}">${escapeHtml(e.status.toUpperCase())}</span>
                </div>
                <h3 class="card-title">${escapeHtml(e.title)}</h3>
                <div class="card-date">🗓 ${dateStr}</div>
                <p class="card-desc">${escapeHtml(e.description || "No description provided.")}</p>
                ${actionsHtml}
            `;

            grid.appendChild(card);
        });
    }

    // Debounce
    function debounce(fn, delay = 300) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    if (searchInput) {
        searchInput.addEventListener("input", debounce(() => fetchEvents(), 300));
    }
    if (catFilter) {
        catFilter.addEventListener("change", () => fetchEvents());
    }
    if (refreshBtn) {
        refreshBtn.addEventListener("click", () => {
            fetchEvents();
            showToast("Events feed refreshed", "info");
        });
    }

    // Modal
    function openModal(isEdit = false) {
        if (!modal) return;
        modal.style.display = "flex";
        if (!isEdit) {
            modalTitle.textContent = "Create Campus Event (AJAX)";
            form.reset();
            idInput.value = "";
            statusInput.value = "open";
            catInput.value = "Technical";
        }
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = "none";
        form.reset();
    }

    if (openModalBtn) openModalBtn.addEventListener("click", () => openModal(false));
    if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener("click", closeModal);

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = idInput.value;
            const isUpdate = id !== "";
            const payload = {
                action: isUpdate ? "update" : "create",
                title: titleInput.value.trim(),
                category: catInput.value,
                event_date: dateInput.value,
                description: descInput.value.trim(),
                status: statusInput.value
            };
            if (isUpdate) payload.id = parseInt(id, 10);

            const saveBtn = document.getElementById("saveAjaxBtn");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Saving...";
            }

            try {
                const res = await fetch("api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.message || "Failed to save");

                closeModal();
                showToast(json.message, "success");
                fetchEvents();
            } catch (err) {
                showToast(err.message, "error");
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Save Event";
                }
            }
        });
    }

    window.editAjaxEvent = async function (id) {
        try {
            const res = await fetch(`api.php?action=get&id=${id}`);
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || "Error fetching event");

            const item = json.data;
            idInput.value = item.id;
            titleInput.value = item.title;
            catInput.value = item.category;
            dateInput.value = item.event_date;
            descInput.value = item.description || "";
            statusInput.value = item.status;

            modalTitle.textContent = `Edit Event #${item.id}`;
            openModal(true);
        } catch (err) {
            showToast(err.message, "error");
        }
    };

    window.deleteAjaxEvent = async function (id, title) {
        if (!confirm(`Delete event '${title}' asynchronously?`)) return;
        try {
            const res = await fetch("api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "delete", id: id })
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || "Failed to delete");
            showToast(json.message, "success");
            fetchEvents();
        } catch (err) {
            showToast(err.message, "error");
        }
    };

    function showToast(message, type = "success") {
        if (!toastContainer) return;
        const toast = document.createElement("div");
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span>${escapeHtml(message)}</span>`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("toast-fadeout");
            setTimeout(() => toast.remove(), 400);
        }, 3200);
    }

    function escapeHtml(str) {
        if (!str) return "";
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function addslashes(string) {
        return (string + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }
});
