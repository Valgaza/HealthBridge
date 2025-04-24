document.addEventListener("DOMContentLoaded", () => {
  // Handle responsive sidebar toggle
  const toggleSidebar = document.querySelector(".toggle-sidebar")
  const sidebar = document.querySelector(".sidebar")

  if (toggleSidebar && sidebar) {
    toggleSidebar.addEventListener("click", () => {
      sidebar.classList.toggle("active")
    })
  }

  // Add current date to the dashboard
  const dateElement = document.querySelector(".current-date")
  if (dateElement) {
    const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" }
    const today = new Date()
    dateElement.textContent = today.toLocaleDateString("en-US", options)
  }
})
