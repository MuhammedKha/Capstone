// script.js — Shared JavaScript for OABS

// Filter available slots in reschedule form to only show future ones from same provider
function updateSlotsByProvider() {
    const apptDropdown = document.getElementById("appointment_id");
    const selectedOption = apptDropdown?.options[apptDropdown.selectedIndex];
    const providerId = selectedOption?.getAttribute("data-provider");

    const slotOptions = document.querySelectorAll("#new_slot_id option");
    const now = new Date();

    slotOptions.forEach(opt => {
        const optProvider = opt.getAttribute("data-provider");
        const slotText = opt.textContent;

        const dateMatch = slotText.match(/(\d{4}-\d{2}-\d{2})/);
        const timeMatch = slotText.match(/\((\d{2}:\d{2}:\d{2}) to/);

        let slotDateTime = null;
        if (dateMatch && timeMatch) {
            const datetimeStr = `${dateMatch[1]}T${timeMatch[1]}`;
            slotDateTime = new Date(datetimeStr);
        }

        const isExpired = slotDateTime && slotDateTime < now;

        opt.style.display = (providerId && optProvider === providerId && !isExpired) ? "block" : "none";
    });

    const slotSelect = document.getElementById("new_slot_id");
    if (slotSelect) slotSelect.value = "";
}

// Populate dropdowns with time values (15-minute intervals)
function populateTimeDropdowns() {
    const times = [];
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 15) {
            let hour = h.toString().padStart(2, '0');
            let min = m.toString().padStart(2, '0');
            times.push(`${hour}:${min}:00`);
        }
    }

    const start = document.getElementById("start_time");
    const end = document.getElementById("end_time");

    if (start && end) {
        start.innerHTML = '<option value="">-- Select Start Time --</option>';
        end.innerHTML = '<option value="">-- Select End Time --</option>';

        times.forEach(time => {
            start.innerHTML += `<option value="${time}">${time}</option>`;
            end.innerHTML += `<option value="${time}">${time}</option>`;
        });
    }
}

// Toggle visibility of cancelled section (used in some views)
function toggleCancelledAppointments() {
    const section = document.getElementById("cancelledAppointments");
    if (section) {
        section.classList.toggle("d-none");
    }
}

// Filter provider view: show past/upcoming/all appointments
function setupProviderFilters() {
    const buttons = document.querySelectorAll(".filter-appointments");
    const rows = document.querySelectorAll(".appointment-row");
    const today = new Date().toISOString().split("T")[0];

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.getAttribute("data-type");

            rows.forEach(row => {
                const date = row.getAttribute("data-date");
                const status = row.getAttribute("data-status");
                let show = true;

                if (filter === "upcoming") {
                    show = status === "booked" && date >= today;
                } else if (filter === "past") {
                    show = (status === "booked" || status === "completed") && date < today;
                } else {
                    show = true; // "all"
                }

                row.style.display = show ? "" : "none";
            });
        });
    });
}

// Admin filters and search input
function setupAdminFilters() {
    const filterButtons = document.querySelectorAll(".filter-btn");
    const searchInput = document.getElementById("searchInput");
    const rows = document.querySelectorAll("#appointmentsTable tbody tr");
    const today = new Date().toISOString().split("T")[0];

    filterButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const filter = btn.getAttribute("data-type");

            rows.forEach(row => {
                const date = row.getAttribute("data-date");
                const status = row.getAttribute("data-status");
                let show = true;

                if (filter === "upcoming") {
                    show = status === "booked" && date >= today;
                } else if (filter === "past") {
                    show = (status === "booked" || status === "completed") && date < today;
                } else if (filter === "cancelled") {
                    show = status === "cancelled";
                } else {
                    show = true;
                }

                row.style.display = show ? "" : "none";
            });
        });
    });

    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });
    }
}

// DOM Loaded
window.addEventListener("DOMContentLoaded", function () {
    populateTimeDropdowns();

    const appointmentDropdown = document.getElementById("appointment_id");
    if (appointmentDropdown) {
        appointmentDropdown.addEventListener("change", updateSlotsByProvider);
        updateSlotsByProvider();
    }

    const toggleBtn = document.getElementById("toggleCancelledBtn");
    if (toggleBtn) {
        toggleBtn.addEventListener("click", toggleCancelledAppointments);
    }

    setupAdminFilters();
    setupProviderFilters();
});