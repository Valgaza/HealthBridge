// File: js/doctor-health-tips.js
document.addEventListener("DOMContentLoaded", () => {
    // Tab switching
    const tabs      = document.querySelectorAll(".content-tabs .tab-btn");
    const contents  = document.querySelectorAll(".tab-content");
    tabs.forEach(btn => {
      btn.addEventListener("click", () => {
        tabs.forEach(b => b.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
      });
    });
  
    // My Health Tips: search + filters
    const searchInput       = document.getElementById("search-tips");
    const filterCategory    = document.getElementById("filter-category");
    const filterVisibility  = document.getElementById("filter-visibility");
    const tipCards          = Array.from(document.querySelectorAll("#my-tips .health-tip-card"));
  
    function filterTips() {
      const term = searchInput.value.trim().toLowerCase();
      const cat  = filterCategory.value;
      const vis  = filterVisibility.value;
  
      tipCards.forEach(card => {
        const title   = card.querySelector(".tip-header h3").textContent.toLowerCase();
        const content = card.querySelector(".tip-content p").textContent.toLowerCase();
        const cardCat = card.dataset.category;
        const cardVis = card.dataset.visibility;
  
        const matchesSearch = title.includes(term) || content.includes(term);
        const matchesCat    = !cat || cardCat === cat;
        const matchesVis    = !vis || cardVis === vis;
  
        card.style.display = (matchesSearch && matchesCat && matchesVis) ? "" : "none";
      });
    }
  
    if (searchInput)       searchInput.addEventListener("input", filterTips);
    if (filterCategory)    filterCategory.addEventListener("change", filterTips);
    if (filterVisibility)  filterVisibility.addEventListener("change", filterTips);
  
    // Create‑Tip: show/hide patient dropdown
    const visibilityRadios = document.querySelectorAll('input[name="visibility"]');
    const patientSelect    = document.querySelector(".patient-select");
    visibilityRadios.forEach(radio => {
      radio.addEventListener("change", () => {
        patientSelect.style.display = (radio.value === "patient" && radio.checked) ? "block" : "none";
      });
    });
  
    // “Save as Template” button
    const saveTplBtn = document.getElementById("save-as-template");
    if (saveTplBtn) {
      saveTplBtn.addEventListener("click", () => {
        const form = document.getElementById("health-tip-form");
        // inject a hidden flag so PHP can save it as a template
        if (!form.querySelector("[name=save_as_template]")) {
          const inp = document.createElement("input");
          inp.type  = "hidden";
          inp.name  = "save_as_template";
          inp.value = "1";
          form.appendChild(inp);
        }
        form.submit();
      });
    }
  
    // Templates tab: (placeholder for future filtering)
    const tplFilter = document.getElementById("template-category-filter");
    if (tplFilter) {
      tplFilter.addEventListener("change", () => {
        // when you populate .templates-grid with real cards
        // you can filter them just like above.
      });
    }
  });
  