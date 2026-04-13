document.querySelectorAll('[data-confirm]').forEach((el) => {
  el.addEventListener('click', (event) => {
    if (!confirm(el.getAttribute('data-confirm'))) {
      event.preventDefault();
    }
  });
});

const revealItems = document.querySelectorAll('.reveal, .card, .hero, .table');
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  },
  { threshold: 0.15 }
);

revealItems.forEach((item) => observer.observe(item));
