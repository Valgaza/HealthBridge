document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.content-tabs .tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
  
    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.dataset.tab;
  
        // deactivate all tabs & panels
        tabButtons.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
  
        // activate the clicked tab + its panel
        btn.classList.add('active');
        const panel = document.getElementById(targetId);
        if (panel) panel.classList.add('active');
      });
    });
  });
  