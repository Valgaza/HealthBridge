document.addEventListener("DOMContentLoaded", () => {
    // Tab switching for patient consultations
    const tabs    = document.querySelectorAll(".content-tabs .tab-btn");
    const panels  = document.querySelectorAll(".tab-content");
  
    tabs.forEach(btn => {
      btn.addEventListener("click", () => {
        // deactivate everything
        tabs.forEach(t => t.classList.remove("active"));
        panels.forEach(p => p.classList.remove("active"));
  
        // activate the clicked tab and its panel
        btn.classList.add("active");
        const panelId = btn.dataset.tab;
        document.getElementById(panelId).classList.add("active");
      });
    });
  
    // Show/hide “Related Symptom” dropdown on Book New form
    const symptomRelation = document.getElementById("symptom-relation"),
          symptomSelect   = document.querySelector(".symptom-select");
  
    if (symptomRelation && symptomSelect) {
      symptomRelation.addEventListener("change", () => {
        symptomSelect.style.display = symptomRelation.value === "yes" ? "block" : "none";
      });
    }
  });
  