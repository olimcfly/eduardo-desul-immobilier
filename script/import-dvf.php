<?php

$file = __DIR__ . '/../storage/dvf/dvf.csv';

$handle = fopen($file, 'r');

while (($row = fgetcsv($handle, 1000, ',')) !== false) {
    // traitement ligne
}

fclose($handle);