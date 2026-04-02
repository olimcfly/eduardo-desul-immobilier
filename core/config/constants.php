<?php
// ============================================================
// CONSTANTES MÉTIER
// ============================================================

// ── Types de biens ───────────────────────────────────────────
const BIEN_TYPES = [
    'appartement' => 'Appartement',
    'maison'      => 'Maison',
    'terrain'     => 'Terrain',
    'commercial'  => 'Local commercial',
    'garage'      => 'Garage / Parking',
    'immeuble'    => 'Immeuble',
];

// ── Transactions ─────────────────────────────────────────────
const TRANSACTION_TYPES = [
    'vente'     => 'Vente',
    'location'  => 'Location',
];

// ── Statuts biens ────────────────────────────────────────────
const BIEN_STATUTS = [
    'disponible'  => 'Disponible',
    'sous_offre'  => 'Sous offre',
    'vendu'       => 'Vendu',
    'loue'        => 'Loué',
    'archive'     => 'Archivé',
];

// ── Statuts contacts ─────────────────────────────────────────
const CONTACT_STATUTS = [
    'new'          => 'Nouveau',
    'contacted'    => 'Contacté',
    'in_progress'  => 'En cours',
    'won'          => 'Gagné',
    'lost'         => 'Perdu',
];

// ── DPE ──────────────────────────────────────────────────────
const DPE_LABELS = ['A','B','C','D','E','F','G'];
const DPE_COLORS = [
    'A' => '#319834',
    'B' => '#33CC33',
    'C' => '#CBEC00',
    'D' => '#FBEC00',
    'E' => '#FBAD00',
    'F' => '#FB6800',
    'G' => '#FC0100',
];

// ── Réseaux sociaux ──────────────────────────────────────────
const SOCIAL_PLATFORMS = [
    'facebook'  => 'Facebook',
    'instagram' => 'Instagram',
    'linkedin'  => 'LinkedIn',
];

// ── Emails ───────────────────────────────────────────────────
const SMTP = [
    'host'     => 'arbre.o2switch.net',
    'port'     => 587,
    'secure'   => 'tls',
    'user'     => 'contact@eduardo-desul-immobilier.fr',
    'pass'     => 'h2rq71k8fdl5q5a9',
    'from'     => 'contact@eduardo-desul-immobilier.fr',
    'name'     => 'Eduardo Desul Immobilier',
];

// ── Clés API (à remplir) ─────────────────────────────────────
const API_KEYS = [
    'openai'        => '',
    'google_maps'   => '',
    'google_places' => '',
    'fb_page_id'    => '',
    'fb_token'      => '',
    'ig_token'      => '',
    'li_token'      => '',
];
