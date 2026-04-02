window.ModuleWelcome = {
  init(config) {
    this.config = config || {};
    const textarea = document.getElementById('free-field-input');
    const counter = document.getElementById('char-counter');
    if (textarea && counter) {
      textarea.addEventListener('input', () => {
        counter.textContent = String(textarea.value.length);
      });
    }
  }
};

window.selectAction = function(btn, value) {
  document.querySelectorAll('.action-choice-btn').forEach((el) => el.classList.remove('is-selected'));
  btn.classList.add('is-selected');
  const hidden = document.getElementById('action-choice-hidden');
  const submit = document.getElementById('btn-continuer');
  if (hidden) hidden.value = value;
  if (submit) submit.disabled = false;
};
