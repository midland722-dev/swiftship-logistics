<?php
/**
 * ReceiptPDF - self-contained PDF receipt builder.
 *
 * Extends the project's bundled FPDF with the extra helpers expected by
 * ShipmentGenerator::buildReceiptPDF(). No external dependencies.
 */

require_once __DIR__ . '/../deprixa/fpdf/fpdf.php';

class ReceiptPDF extends FPDF {
    public const MM_TO_PT = 72 / 25.4;

    public $marginL = 15;
    public $marginR = 15;
    public $marginT = 20;
    public $marginB = 20;
    public $headerH = 28;
    public $footerH = 18;
    public $contentW = 180;
    public $pageW = 210;
    public $pageH = 297;
    public $cy = 0;

    public $onHeader = null;
    public $onFooter = null;

    public function __construct(string $orientation = 'P', string $format = 'A4') {
        parent::__construct($orientation, 'mm', $format);
        $this->SetAutoPageBreak(true, $this->marginB + $this->footerH);
        $this->contentW = $this->pageW - $this->marginL - $this->marginR;
    }

    public function Header() {
        if ($this->onHeader) {
            ($this->onHeader)($this);
        }
    }

    public function Footer() {
        if ($this->onFooter) {
            ($this->onFooter)($this, $this->PageNo(), $this->AliasNbPages());
        }
    }

    /* ---- Case-insensitive wrappers around FPDF core ---- */

    public function setFillColor($r, $g = null, $b = null) {
        if ($g === null) { parent::SetFillColor($r); }
        else { parent::SetFillColor($r, $g, $b); }
    }

    public function setDrawColor($r, $g = null, $b = null) {
        if ($g === null) { parent::SetDrawColor($r); }
        else { parent::SetDrawColor($r, $g, $b); }
    }

    public function setTextColor($r, $g = null, $b = null) {
        if ($g === null) { parent::SetTextColor($r); }
        else { parent::SetTextColor($r, $g, $b); }
    }

    public function setFont($family, $style = '', $size = 0) {
        parent::SetFont($family, $style, $size);
    }

    public function text($x, $y, $txt) {
        parent::Text($x, $y, $txt);
    }

    public function rect($x, $y, $w, $h, $style = '') {
        parent::Rect($x, $y, $w, $h, $style);
    }

    public function line($x1, $y1, $x2, $y2) {
        parent::Line($x1, $y1, $x2, $y2);
    }

    public function image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '') {
        try {
            parent::Image($file, $x, $y, $w, $h, $type, $link);
            return true;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function output($name = '', $dest = 'S') {
        return parent::Output($name, $dest);
    }

    /* ---- Helpers used by ShipmentGenerator ---- */

    public function textWidth(string $txt): float {
        return $this->GetStringWidth($txt);
    }

    public function wrapText(string $txt, float $maxW): array {
        $words = preg_split('/\s+/', $txt, -1, PREG_SPLIT_NO_EMPTY);
        $lines = [];
        $current = '';
        foreach ($words as $w) {
            $test = $current === '' ? $w : $current . ' ' . $w;
            if ($this->GetStringWidth($test) > $maxW && $current !== '') {
                $lines[] = $current;
                $current = $w;
            } else {
                $current = $test;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return $lines;
    }

    public function writeParagraph(string $txt, ?float $maxW = 0, int $lineHeight = 1): void {
        $maxW = ($maxW !== null && $maxW > 0) ? $maxW : $this->contentW;
        $lines = $this->wrapText($txt, $maxW);
        $lh = ($this->FontSizePt / self::MM_TO_PT) * $lineHeight;
        foreach ($lines as $ln) {
            if ($this->cy + $lh > $this->pageH - $this->marginB - $this->footerH) {
                $this->AddPage();
            }
            $this->text($this->marginL, $this->cy, $ln);
            $this->cy += $lh;
        }
    }

    public function ensureSpace(float $needed): void {
        $available = $this->pageH - $this->marginB - $this->footerH - $this->cy;
        if ($available < $needed) {
            $this->AddPage();
            $this->cy = $this->marginT;
        }
    }
}
