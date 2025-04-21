document.addEventListener("DOMContentLoaded", () => {
  // Show/hide doctor-specific fields based on user type
  const userTypeSelect = document.getElementById("user-type")
  const doctorFields   = document.querySelector(".doctor-fields")

  if (userTypeSelect && doctorFields) {
    userTypeSelect.addEventListener("change", () => {
      doctorFields.style.display =
        (userTypeSelect.value === "doctor") ? "block" : "none"
    })
  }
})
