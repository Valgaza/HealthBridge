// /js/prescriptions.js

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.content-tabs .tab-btn');
    const panes = document.querySelectorAll('.tab-content');
  
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        // deactivate all tabs & panes
        tabs.forEach(t => t.classList.remove('active'));
        panes.forEach(p => p.classList.remove('active'));
  
        // activate clicked tab + its pane
        tab.classList.add('active');
        const pane = document.getElementById(tab.dataset.tab);
        if (pane) pane.classList.add('active');
      });
    });
  
    // Search within Prescription History
    const searchInput = document.getElementById('search-prescriptions');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase();
        document
          .querySelectorAll('#history tbody tr')
          .forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term)
              ? ''
              : 'none';
          });
      });
    }
  
    // (Optional) Date‐range filter could go here if you need it later
  });
  