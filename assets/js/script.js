// script.js — Shared JavaScript for OABS

/**
 * Filters available slots in reschedule form to only show those
 * from the same provider as the selected appointment.
 */
function updateSlotsByProvider() {
    const apptDropdown = document.getElementById("appointment_id");
    const selectedOption = apptDropdown.options[apptDropdown.selectedIndex];
    const providerId = selectedOption ? selectedOption.getAttribute("data-provider") : null;

    const slotOptions = document.querySelectorAll("#new_slot_id option");
    slotOptions.forEach(opt => {
        const optProvider = opt.getAttribute("data-provider");
        if (providerId && optProvider === providerId) {
            opt.style.display = "block";
        } else {
            opt.style.display = "none";
        }
    });

    // Reset slot selection
    const slotSelect = document.getElementById("new_slot_id");
    if (slotSelect) slotSelect.value = "";
}

/**
 * Populate time dropdowns in HH:MM format (15-minute intervals)
 */
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

window.addEventListener("DOMContentLoaded", function () {
    populateTimeDropdowns();
});
