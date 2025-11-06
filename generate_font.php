<?php
require_once(__DIR__ . "/lib/TCPDF-main/tcpdf.php");

// Simple, renamed TTF file
$ttf_file = __DIR__ . "/lib/TCPDF-main/fonts/NotoSansEthiopic-Regular.ttf";

// Generate TCPDF font definition
$fontname = TCPDF_FONTS::addTTFfont($ttf_file, 'TrueTypeUnicode', '', 32);

if (!$fontname) {
    die("Failed to generate font definition.");
}

echo "Font generated: " . $fontname;
