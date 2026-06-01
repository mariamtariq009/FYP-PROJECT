// Legacy entry point: keep working if older pages include this file.
// Prefer including `Assets/js/theme.js` for persistence + unified behavior.
const toggleBtn = document.getElementById('theme-toggle');
if (toggleBtn) {
  toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    document.body.classList.toggle('light');
  });
}