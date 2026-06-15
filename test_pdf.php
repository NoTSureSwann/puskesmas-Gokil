<?php
ini_set('memory_limit', '4G');
require 'vendor/autoload.php';
$p = new Smalot\PdfParser\Parser();
try {
    $pdf = $p->parseFile('storage/app/ai_training_journals/Negative symptoms in schizophrenia.pdf');
    echo substr($pdf->getText(), 0, 500);
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
