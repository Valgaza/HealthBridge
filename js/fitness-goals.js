document.addEventListener("DOMContentLoaded", () => {
    const tabs   = document.querySelectorAll(".content-tabs .tab-btn");
    const panels = document.querySelectorAll(".tab-content");
  
    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        const targetId = tab.getAttribute("data-tab");
  
        // deactivate all tabs and panels
        tabs.forEach(t => t.classList.remove("active"));
        panels.forEach(p => p.classList.remove("active"));
  
        // activate the clicked tab
        tab.classList.add("active");
  
        // show the matching panel
        const panel = document.getElementById(targetId);
        if (panel) panel.classList.add("active");
      });
    });
  });
  