// File: js/doctor-prescriptions.js
document.addEventListener("DOMContentLoaded", () => {
    // 1) Tab switching
    const tabs     = document.querySelectorAll(".content-tabs .tab-btn");
    const panels   = document.querySelectorAll(".tab-content");
    tabs.forEach(btn => {
      btn.addEventListener("click", () => {
        tabs.forEach(b => b.classList.remove("active"));
        panels.forEach(p => p.classList.remove("active"));
        btn.classList.add("active");
        document.getElementById(btn.dataset.tab).classList.add("active");
      });
    });
  
    // 2) Recent Prescriptions: search + filters
    const rows         = Array.from(document.querySelectorAll("#recent .prescription-row"));
    const searchInput  = document.getElementById("search-prescriptions");
    const dateFilter   = document.getElementById("filter-date");
    const statusFilter = document.getElementById("filter-status");
  
    function filterRows() {
      const term   = searchInput.value.trim().toLowerCase();
      const df     = dateFilter.value;
      const sf     = statusFilter.value;
      const now    = new Date();
  
      rows.forEach(row => {
        // text match
        const patient = row.dataset.patient;
        const med     = row.dataset.med;
        const textOK  = !term || patient.includes(term) || med.includes(term);
  
        // status match
        const statusOK = !sf || row.dataset.status === sf;
  
        // date match
        let dateOK = true;
        if (df) {
          const d = new Date(row.dataset.date);
          const diffDays = Math.floor((now - d) / (1000*60*60*24));
          if (df === "today")   dateOK = diffDays === 0;
          if (df === "week")    dateOK = diffDays <= 7;
          if (df === "month")   dateOK = diffDays <= 30;
        }
  
        row.style.display = (textOK && statusOK && dateOK) ? "" : "none";
      });
    }
  
    searchInput.addEventListener("input", filterRows);
    dateFilter.addEventListener("change", filterRows);
    statusFilter.addEventListener("change", filterRows);
  
    // 3) Create Prescription: add‑medication
    const addBtn = document.querySelector(".add-medication");
    if (addBtn) {
      addBtn.addEventListener("click", () => {
        const container = document.querySelector(".medication-form");
        const block = document.createElement("div");
        block.innerHTML = `
          <hr>
          <div class="form-group">
            <label>Medication</label>
            <input type="text" name="medication[]" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Dosage</label>
              <input type="text" name="dosage[]" required>
            </div>
            <div class="form-group">
              <label>Frequency</label>
              <select name="frequency[]" required>
                <option value="once">Once daily</option>
                <option value="twice">Twice daily</option>
                <option value="three">Three times daily</option>
                <option value="as-needed">As needed</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Duration</label>
              <input type="number" name="duration_value[]" min="1" value="7" required>
              <select name="duration_unit[]">
                <option value="days">Days</option>
                <option value="weeks">Weeks</option>
                <option value="months">Months</option>
              </select>
            </div>
            <div class="form-group">
              <label>Instructions</label>
              <textarea name="instructions[]" rows="2"></textarea>
            </div>
          </div>
          <button type="button" class="btn btn-outline remove-medication">Remove</button>
        `.trim();
        container.append(block);
        block.querySelector(".remove-medication")
             .addEventListener("click", () => block.remove());
      });
    }
  
    // 4) Save as Template
    const saveTplBtn = document.getElementById("save-as-template");
    if (saveTplBtn) {
      saveTplBtn.addEventListener("click", () => {
        const form = document.getElementById("prescription-form");
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
  });
  