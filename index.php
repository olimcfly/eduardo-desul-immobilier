<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Site en maintenance | Eduardo De Sul Immobilier</title>
  <meta name="description" content="Le site d'Eduardo De Sul Immobilier est actuellement en maintenance. Revenez très bientôt ou contactez-nous directement." />
  <meta name="robots" content="noindex, nofollow" />

  <style>
    :root {
      --bg: #0f172a;
      --bg-soft: #1e293b;
      --card: rgba(255,255,255,0.08);
      --text: #f8fafc;
      --muted: #cbd5e1;
      --accent: #f59e0b;
      --accent-dark: #d97706;
      --border: rgba(255,255,255,0.12);
      --shadow: 0 20px 60px rgba(0,0,0,0.35);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      font-family: Arial, Helvetica, sans-serif;
      background:
        linear-gradient(135deg, rgba(15,23,42,0.96), rgba(30,41,59,0.96)),
        url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
      color: var(--text);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .maintenance-container {
      width: 100%;
      max-width: 760px;
    }

    .maintenance-card {
      background: var(--card);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid var(--border);
      border-radius: 24px;
      box-shadow: var(--shadow);
      padding: 40px 28px;
      text-align: center;
    }

    .badge {
      display: inline-block;
      background: rgba(245, 158, 11, 0.16);
      color: #fde68a;
      border: 1px solid rgba(245, 158, 11, 0.35);
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 14px;
      margin-bottom: 22px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .logo {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 12px;
      letter-spacing: 0.4px;
    }

    h1 {
      font-size: 38px;
      line-height: 1.15;
      margin-bottom: 18px;
    }

    p {
      font-size: 18px;
      line-height: 1.7;
      color: var(--muted);
      max-width: 620px;
      margin: 0 auto 16px;
    }

    .highlight {
      color: #fff;
      font-weight: 700;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      justify-content: center;
      margin-top: 30px;
    }

    .btn {
      text-decoration: none;
      padding: 14px 22px;
      border-radius: 12px;
      font-weight: 700;
      transition: 0.25s ease;
      display: inline-block;
    }

    .btn-primary {
      background: var(--accent);
      color: #111827;
    }

    .btn-primary:hover {
      background: var(--accent-dark);
      color: #fff;
      transform: translateY(-2px);
    }

    .btn-secondary {
      border: 1px solid rgba(255,255,255,0.2);
      color: var(--text);
      background: rgba(255,255,255,0.04);
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.1);
      transform: translateY(-2px);
    }

    .info-box {
      margin-top: 28px;
      padding: 18px;
      border-radius: 16px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.08);
    }

    .info-box p {
      margin-bottom: 8px;
      font-size: 15px;
    }

    .footer-note {
      margin-top: 18px;
      font-size: 14px;
      color: #94a3b8;
    }

    @media (max-width: 640px) {
      .maintenance-card {
        padding: 30px 20px;
      }

      h1 {
        font-size: 29px;
      }

      p {
        font-size: 16px;
      }

      .logo {
        font-size: 24px;
      }

      .actions {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <main class="maintenance-container">
    <section class="maintenance-card">
      <div class="badge">Site temporairement en maintenance</div>

      <div class="logo">Eduardo De Sul Immobilier</div>

      <h1>Nous préparons une version plus claire, plus rapide et plus efficace du site.</h1>

      <p>
        Le site est actuellement en cours d’amélioration pour mieux accompagner
        les propriétaires et acquéreurs sur <span class="highlight">Bordeaux et sa métropole</span>.
      </p>

      <p>
        Revenez très bientôt. En attendant, vous pouvez toujours nous contacter
        directement pour une question, une estimation ou un rendez-vous.
      </p>

      <div class="actions">
        <a href="tel:+33600000000" class="btn btn-primary">Appeler maintenant</a>
        <a href="mailto:contact@eduardodesul.fr" class="btn btn-secondary">Envoyer un email</a>
      </div>

      <div class="info-box">
        <p><strong>Disponible pendant la maintenance :</strong></p>
        <p>• Demande d’estimation</p>
        <p>• Prise de rendez-vous</p>
        <p>• Questions sur un projet de vente ou d’achat</p>
      </div>

      <div class="footer-note">
        Merci de votre compréhension.
      </div>
    </section>
  </main>
</body>
</html>