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
