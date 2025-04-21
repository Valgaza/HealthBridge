document.addEventListener("DOMContentLoaded", () => {
  // Tab switching functionality
  const tabButtons = document.querySelectorAll(".tab-btn")
  const tabContents = document.querySelectorAll(".tab-content")

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      tabButtons.forEach((btn) => btn.classList.remove("active"))
      tabContents.forEach((content) => content.classList.remove("active"))
      button.classList.add("active")
      document.getElementById(button.dataset.tab).classList.add("active")
    })
  })

  // Set today's date as default for symptom date
  const symptomDateInput = document.getElementById("symptom-date")
  if (symptomDateInput) {
    const today = new Date().toISOString().substr(0, 10)
    symptomDateInput.value = today
  }

  // Set current time as default for symptom time
  const symptomTimeInput = document.getElementById("symptom-time")
  if (symptomTimeInput) {
    const now = new Date()
    const hh = String(now.getHours()).padStart(2, "0")
    const mm = String(now.getMinutes()).padStart(2, "0")
    symptomTimeInput.value = `${hh}:${mm}`
  }

  // Search functionality for symptom history
  const searchInput = document.getElementById("search-symptoms")
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const term = this.value.toLowerCase()
      document.querySelectorAll(".data-table tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? "" : "none"
      })
    })
  }

  // Filter functionality for symptom history
  const filterSeverity = document.getElementById("filter-severity")
  const filterDate     = document.getElementById("filter-date")

  function applyFilters() {
    const sev = filterSeverity ? filterSeverity.value : ""
    const dt  = filterDate ? filterDate.value : ""
    const today = new Date()

    document.querySelectorAll(".data-table tbody tr").forEach((row) => {
      let show = true

      // Severity filter
      if (sev) {
        const cell = row.querySelector("td:nth-child(4) .severity")
        if (cell && !cell.classList.contains(sev)) show = false
      }

      // Date filter
      if (dt && show) {
        const cellDate = new Date(row.querySelector("td:nth-child(2)").textContent)
        if (dt === "week" && cellDate < new Date(today - 7*24*60*60*1000)) show = false
        if (dt === "month" && cellDate < new Date(today.getFullYear(), today.getMonth()-1, today.getDate())) show = false
        if (dt === "year" && cellDate < new Date(today.getFullYear()-1, today.getMonth(), today.getDate())) show = false
      }

      row.style.display = show ? "" : "none"
    })
  }

  if (filterSeverity) filterSeverity.addEventListener("change", applyFilters)
  if (filterDate)     filterDate.addEventListener("change", applyFilters)
})
