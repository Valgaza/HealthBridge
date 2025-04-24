document.addEventListener("DOMContentLoaded", () => {
  // TAB SWITCHING
  const tabButtons = document.querySelectorAll('.content-tabs .tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      // deactivate all
      tabButtons.forEach(b => b.classList.remove('active'));
      tabContents.forEach(c => c.classList.remove('active'));
      // activate this
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  // UPCOMING TAB FILTERING
  const upcomingTbody = document.querySelector('#upcoming table.data-table tbody');
  if (upcomingTbody) {
    const rows = Array.from(upcomingTbody.rows);
    const searchInput = document.getElementById('search-upcoming');
    const typeFilter = document.getElementById('filter-type');

    // live search by patient name (3rd cell)
    searchInput?.addEventListener('input', () => {
      const term = searchInput.value.toLowerCase();
      rows.forEach(r => {
        const name = r.cells[2].textContent.toLowerCase();
        r.style.display = name.includes(term) ? '' : 'none';
      });
    });

    // filter by type
    typeFilter?.addEventListener('change', () => {
      const val = typeFilter.value.toLowerCase();
      rows.forEach(r => {
        const span = r.querySelector('.consultation-type');
        const txt  = span?.textContent.toLowerCase() || '';
        r.style.display = (!val || txt === val) ? '' : 'none';
      });
    });
  }

  // (You can mirror the above approach for the #past tab if you wish)
});
