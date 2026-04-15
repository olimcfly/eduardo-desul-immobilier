<?php

$data = $data ?? [];

$label = $data['label'] ?? '';
$title = $data['title'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$backgroundImage = $data['background_image'] ?? '';

$primaryLabel = $data['primary_button_label'] ?? '';
$primaryUrl = $data['primary_button_url'] ?? '/estimation';

$secondaryLabel = $data['secondary_button_label'] ?? '';
$secondaryUrl = $data['secondary_button_url'] ?? '/biens';

$pillars = is_array($data['pillars'] ?? null) ? $data['pillars'] : [];