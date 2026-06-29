// Theme toggle
const themeToggles = document.querySelectorAll('[data-theme-toggle]');
const html = document.documentElement;
const savedTheme = localStorage.getItem('pawstay-theme') || 'light';
html.setAttribute('data-theme', savedTheme);
updateThemeIcons(savedTheme);

themeToggles.forEach(btn => {
  btn.addEventListener('click', () => {
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('pawstay-theme', next);
    updateThemeIcons(next);
  });
});

function updateThemeIcons(theme) {
  document.querySelectorAll('[data-theme-icon]').forEach(icon => {
    icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
  });
}

// Sidebar toggle (mobile)
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebar = document.getElementById('adminSidebar');
const backdrop = document.querySelector('.sidebar-backdrop');

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    backdrop?.classList.toggle('show');
  });
}
if (backdrop) {
  backdrop.addEventListener('click', () => {
    sidebar?.classList.remove('open');
    backdrop.classList.remove('show');
  });
}

// Dropdowns
document.querySelectorAll('.dropdown').forEach(drop => {
  const btn = drop.querySelector('button');
  const menu = drop.querySelector('.dropdown-menu');
  if (!btn || !menu) return;
  btn.addEventListener('click', e => {
    e.stopPropagation();
    document.querySelectorAll('.dropdown-menu.show').forEach(m => { if (m !== menu) m.classList.remove('show'); });
    menu.classList.toggle('show');
  });
});
document.addEventListener('click', () => {
  document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

// Table search
document.querySelectorAll('[data-table-search]').forEach(input => {
  const tableId = input.getAttribute('data-table-search');
  const table = document.getElementById(tableId);
  if (!table) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
});

// Chip filter tabs
document.querySelectorAll('.chip-tabs').forEach(tabs => {
  tabs.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      tabs.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const filter = chip.dataset.filter;
      const tableId = tabs.dataset.tableTarget;
      if (!tableId) return;
      const table = document.getElementById(tableId);
      if (!table) return;
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
      });
    });
  });
});

// Form validation
document.querySelectorAll('form.needs-validation').forEach(form => {
  form.addEventListener('submit', e => {
    if (!form.checkValidity()) {
      e.preventDefault();
      form.classList.add('was-validated');
    }
  });
});
