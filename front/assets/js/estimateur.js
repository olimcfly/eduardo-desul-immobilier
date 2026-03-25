(function () {
  const form = document.getElementById('estimateur-form');
  if (!form) return;

  form.addEventListener('change', function (event) {
    if (event.target.name === 'mode') {
      document.body.dataset.estimateurMode = event.target.value;
    }
  });
})();
