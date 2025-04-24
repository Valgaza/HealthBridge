// health-tips.js
// Enables search and filter functionality on Health Tips page

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-tips');
    const categorySelect = document.getElementById('filter-category');
    const doctorSelect = document.getElementById('filter-doctor');
    const tipCards = Array.from(document.querySelectorAll('.health-tip-card'));
  
    function normalize(str) {
      return str.trim().toLowerCase();
    }
  
    function filterTips() {
      const query = normalize(searchInput.value);
      const category = normalize(categorySelect.value);
      const doctor = normalize(doctorSelect.value);
  
      tipCards.forEach(card => {
        const title = normalize(card.querySelector('.tip-header h3').textContent);
        const content = normalize(card.querySelector('.tip-content').textContent);
        const cardCategory = normalize(card.dataset.category);
        const cardDoctor = normalize(card.dataset.doctor);
  
        const matchesSearch = !query || title.includes(query) || content.includes(query);
        const matchesCategory = !category || cardCategory === category;
        const matchesDoctor = !doctor || cardDoctor === doctor;
  
        if (matchesSearch && matchesCategory && matchesDoctor) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }
  
    // attach events
    searchInput.addEventListener('input', filterTips);
    categorySelect.addEventListener('change', filterTips);
    doctorSelect.addEventListener('change', filterTips);
  });
  