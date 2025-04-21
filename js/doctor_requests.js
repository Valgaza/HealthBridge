document.addEventListener("DOMContentLoaded", () => {
    const acceptButtons = document.querySelectorAll(".request-actions .btn-primary");
    const declineButtons = document.querySelectorAll(".request-actions .btn-outline");
  
    acceptButtons.forEach(button => {
      button.addEventListener("click", () => {
        const item = button.closest(".request-item");
        const name = item.querySelector("h4").textContent;
        if (!confirm(`Accept consultation request from ${name}?`)) return;
        // TODO: send an AJAX POST to your PHP endpoint to mark accepted:
        // fetch('/doctor/handle_request.php', { method:'POST', body: new URLSearchParams({ id, action:'accept' }) })
  
        alert(`Consultation request from ${name} accepted.`);
        item.style.opacity = "0.5";
        button.disabled = true;
        item.querySelector(".btn-outline").disabled = true;
      });
    });
  
    declineButtons.forEach(button => {
      button.addEventListener("click", () => {
        const item = button.closest(".request-item");
        const name = item.querySelector("h4").textContent;
        if (!confirm(`Decline consultation request from ${name}?`)) return;
        // TODO: send AJAX POST to mark declined
  
        alert(`Consultation request from ${name} declined.`);
        item.style.opacity = "0.5";
        button.disabled = true;
        item.querySelector(".btn-primary").disabled = true;
      });
    });
  });
  