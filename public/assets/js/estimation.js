/* ── Estimation JS ───────────────────────────────────────────── */

'use strict';

// ── Stepper multi-étapes ──────────────────────────────────────
let currentStep = 1;
const steps     = document.querySelectorAll('.estimation-step');
const stepDots  = document.querySelectorAll('.step');
const totalSteps = steps.length;

function showStep(n) {
  steps.forEach((s, i) => {
    s.hidden = i + 1 !== n;
  });
  stepDots.forEach((dot, i) => {
    dot.classList.toggle('active', i + 1 === n);
    dot.classList.toggle('done',   i + 1 < n);
  });
  document.querySelector('.prev-btn')?.toggleAttribute('disabled', n === 1);
}

document.querySelectorAll('.next-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    if (currentStep < totalSteps) {
      currentStep++;
      showStep(currentStep);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });
});

document.querySelectorAll('.prev-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  });
});

// ── Type bien buttons ─────────────────────────────────────────
document.querySelectorAll('.type-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const input = document.getElementById('type-bien');
    if (input) input.value = btn.dataset.value;
  });
});

// ── Range sliders ─────────────────────────────────────────────
document.querySelectorAll('input[type="range"]').forEach(range => {
  const output = document.getElementById(range.id + '-val');
  if (!output) return;
  const format = range.dataset.format;
  function update() {
    let val = parseInt(range.value).toLocaleString('fr-FR');
    output.textContent = format === 'price' ? val + ' €' : val + ' m²';
  }
  range.addEventListener('input', update);
  update();
});

// Init
if (steps.length) showStep(1);
