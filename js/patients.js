// File: js/patients.js

document.addEventListener("DOMContentLoaded", () => {
    const searchInput   = document.getElementById("search-patients");
    const patientCards  = Array.from(document.querySelectorAll(".patient-card"));
  
    if (!searchInput || patientCards.length === 0) return;
  
    searchInput.addEventListener("input", () => {
      const term = searchInput.value.trim().toLowerCase();
  
      patientCards.forEach(card => {
        // Grab the patient name and ID text
        const infoEl = card.querySelector(".patient-basic-info");
        const text    = infoEl ? infoEl.innerText.toLowerCase() : "";
  
        // Show card if either name or ID matches the search term
        card.style.display = text.includes(term) ? "" : "none";
      });
    });
  });
  