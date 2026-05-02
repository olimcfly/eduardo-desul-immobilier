-- Séquence CRM e-mails « financement » (1er mail J+2 via cron, pour ne pas doublonner l’accusé IA)
INSERT INTO crm_sequences (name, description, status) VALUES
('Séquence Financement — 3 relances', 'J+2 rappel, J+5 suite dossier, J+9 clôture douce', 'active');

INSERT INTO crm_sequence_steps (sequence_id, step_order, delay_days, email_subject, email_body_html, cta_label, cta_url)
SELECT s.id, 1, 0,
 'Votre demande de financement — point sur le dossier',
 CONCAT(
   '<p>Bonjour [PRENOM],</p>',
   '<p>Suite à votre demande sur le site, je fais un premier point : avez-vous une idée arrêtée de budget, ',
   'd’apport et de calendrier ? Je peux vous aider à prioriser les prochaines étapes (dossier, interlocuteurs, timing).</p>',
   '<p><a href="[RDV_URL]">Planifier un échange</a></p>',
   '<p>[ADVISOR_NAME]</p>'
 ),
 'Planifier un échange', '[RDV_URL]'
FROM crm_sequences s WHERE s.name = 'Séquence Financement — 3 relances' LIMIT 1;

INSERT INTO crm_sequence_steps (sequence_id, step_order, delay_days, email_subject, email_body_html, cta_label, cta_url)
SELECT s.id, 2, 3,
 'Préparer votre demande de prêt',
 CONCAT(
   '<p>Bonjour [PRENOM],</p>',
   '<p>Pour avancer sereinement, les pièces souvent demandées vont de l’avis d’imposition aux derniers relevés,',
   ' en passant par la situation des crédits en cours. Si un point bloque, répondez simplement à ce message.</p>',
   '<p>[ADVISOR_NAME]</p>'
 ),
 'Nous contacter', '[RDV_URL]'
FROM crm_sequences s WHERE s.name = 'Séquence Financement — 3 relances' LIMIT 1;

INSERT INTO crm_sequence_steps (sequence_id, step_order, delay_days, email_subject, email_body_html, cta_label, cta_url)
SELECT s.id, 3, 4,
 'Où en est votre projet de financement ?',
 CONCAT(
   '<p>Bonjour [PRENOM],</p>',
   '<p>Je me permets un dernier relais : souhaitez-vous un appel court pour cadrer délai, budget et secteur recherché ? ',
   'Sans engagement.</p>',
   '<p><a href="[RDV_URL]">Prendre contact</a></p>',
   '<p>[ADVISOR_NAME]</p>'
 ),
 'Prendre contact', '[RDV_URL]'
FROM crm_sequences s WHERE s.name = 'Séquence Financement — 3 relances' LIMIT 1;
