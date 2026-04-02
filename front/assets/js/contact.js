(function () {
  const form = document.getElementById('contactForm');
  const message = document.getElementById('message');
  const count = document.getElementById('charCount');

  function refreshCount() {
    if (!message || !count) return;
    count.textContent = String(message.value.length);
  }

  if (message) {
    message.addEventListener('input', refreshCount);
    refreshCount();
  }

  window.resetContactForm = function resetContactForm() {
    window.location.href = '/contact';
  };

  if (form) {
    form.addEventListener('submit', function (e) {
      if (message && message.value.length > 1000) {
        e.preventDefault();
        alert('Le message est limité à 1000 caractères.');
      }
    });
  }
})();
