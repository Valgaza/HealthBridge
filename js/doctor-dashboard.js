document.addEventListener("DOMContentLoaded", () => {
  // Handle responsive sidebar toggle
  const toggleSidebar = document.querySelector(".toggle-sidebar")
  const sidebar = document.querySelector(".sidebar")

  if (toggleSidebar && sidebar) {
    toggleSidebar.addEventListener("click", () => {
      sidebar.classList.toggle("active")
    })
  }

  // OPTIONAL: consultation request actions (still stubbed)
  const acceptButtons = document.querySelectorAll(".request-actions .btn-primary")
  const declineButtons = document.querySelectorAll(".request-actions .btn-outline")

  acceptButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const requestItem = button.closest(".request-item")
      const patientName = requestItem.querySelector("h4").textContent
      alert(`Consultation request from ${patientName} accepted.`)
      requestItem.style.opacity = "0.5"
      button.disabled = true
      button.nextElementSibling.disabled = true
    })
  })

  declineButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const requestItem = button.closest(".request-item")
      const patientName = requestItem.querySelector("h4").textContent
      if (confirm(`Decline consultation request from ${patientName}?`)) {
        alert(`Consultation request from ${patientName} declined.`)
        requestItem.style.opacity = "0.5"
        button.disabled = true
        button.previousElementSibling.disabled = true
      }
    })
  })

  // NOTE: Removed start-consultation-button click handler since links are now real <a href="consultation.php?id=…">
})
