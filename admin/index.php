<?php
// admin/index.php — redirige vers l'entrée publique
// Ce fichier ne doit pas être appelé directement.
// L'entrée admin est : public/admin/index.php (accessible via /admin/)
header('Location: /admin');
exit;
