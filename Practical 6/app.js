let students = [];
let filteredStudents = [];

let currentPage = 1;
const studentsPerPage = 6;

const studentContainer = document.getElementById("studentContainer");
const searchInput = document.getElementById("searchInput");
const departmentFilter = document.getElementById("departmentFilter");
const sortSelect = document.getElementById("sortSelect");

const loading = document.getElementById("loading");
const error = document.getElementById("error");

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const pageNumber = document.getElementById("pageNumber");

fetchStudents();

function fetchStudents() {
    fetch("students.json")
        .then(response => {
            if (!response.ok) {
                throw new Error("Unable to load student data");
            }

            return response.json();
        })
        .then(data => {
            students = data;
            filteredStudents = [...students];

            loading.style.display = "none";

            applyFilters();
        })
        .catch(err => {
            loading.style.display = "none";
            error.textContent = "Error: " + err.message;
        });
}

function applyFilters() {
    const searchText = searchInput.value.toLowerCase();
    const department = departmentFilter.value;

    filteredStudents = students.filter(student => {
        const matchesSearch = student.name
            .toLowerCase()
            .includes(searchText);

        const matchesDepartment =
            department === "all" ||
            student.department === department;

        return matchesSearch && matchesDepartment;
    });

    applySorting();

    currentPage = 1;

    displayStudents();
}

function applySorting() {
    const sortValue = sortSelect.value;

    if (sortValue === "nameAsc") {
        filteredStudents.sort((a, b) =>
            a.name.localeCompare(b.name)
        );
    }

    if (sortValue === "nameDesc") {
        filteredStudents.sort((a, b) =>
            b.name.localeCompare(a.name)
        );
    }

    if (sortValue === "marksHigh") {
        filteredStudents.sort((a, b) =>
            b.marks - a.marks
        );
    }

    if (sortValue === "marksLow") {
        filteredStudents.sort((a, b) =>
            a.marks - b.marks
        );
    }
}

function displayStudents() {
    studentContainer.innerHTML = "";

    const startIndex = (currentPage - 1) * studentsPerPage;
    const endIndex = startIndex + studentsPerPage;

    const currentStudents =
        filteredStudents.slice(startIndex, endIndex);

    if (currentStudents.length === 0) {
        studentContainer.innerHTML =
            "<p>No students found.</p>";

        updatePagination();
        return;
    }

    currentStudents.forEach(student => {
        const card = document.createElement("div");

        card.className = "student-card";

        card.innerHTML = `
            <h2>${student.name}</h2>
            <p><strong>ID:</strong> ${student.id}</p>
            <p><strong>Department:</strong> ${student.department}</p>
            <p><strong>Semester:</strong> ${student.semester}</p>
            <p class="marks"><strong>Marks:</strong> ${student.marks}</p>
            <p><strong>Email:</strong> ${student.email}</p>
        `;

        studentContainer.appendChild(card);
    });

    updatePagination();
}

function updatePagination() {
    const totalPages =
        Math.ceil(filteredStudents.length / studentsPerPage);

    pageNumber.textContent =
        `Page ${currentPage} of ${totalPages || 1}`;

    prevBtn.disabled = currentPage === 1;

    nextBtn.disabled =
        currentPage === totalPages || totalPages === 0;
}

searchInput.addEventListener("input", applyFilters);

departmentFilter.addEventListener("change", applyFilters);

sortSelect.addEventListener("change", applyFilters);

prevBtn.addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        displayStudents();
    }
});

nextBtn.addEventListener("click", () => {
    const totalPages =
        Math.ceil(filteredStudents.length / studentsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        displayStudents();
    }
});