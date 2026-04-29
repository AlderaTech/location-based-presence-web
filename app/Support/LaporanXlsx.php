<?php

namespace App\Support;

use ZipArchive;

class LaporanXlsx
{
    /**
     * @param  array<int, array<int, scalar|null>>  $rows
     * @param  array{judul?: string, metadata?: array<string, scalar|null>}  $options
     */
    public static function make(array $rows, array $options = []): string
    {
        $path = storage_path('app/report-presensi-'.uniqid().'.xlsx');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/workbook.xml', self::workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($rows, $options));
        $zip->close();

        return $path;
    }

    private static function sheet(array $rows, array $options): string
    {
        $judul = (string) ($options['judul'] ?? 'Rekap Presensi');
        $metadata = is_array($options['metadata'] ?? null) ? $options['metadata'] : [];
        $header = array_values(array_shift($rows) ?? ['Data']);
        $lastColumn = self::column(max(1, count($header)));
        $rowNumber = 1;
        $xmlRows = '';

        $xmlRows .= self::row($rowNumber++, [$judul], [2], 24);
        foreach ($metadata as $label => $value) {
            $xmlRows .= self::row($rowNumber++, [$label, (string) $value], [3, 0]);
        }

        $xmlRows .= self::row($rowNumber++, [''], [0], 6);
        $headerRow = $rowNumber;
        $xmlRows .= self::row($rowNumber++, $header, array_fill(0, count($header), 1), 20);

        foreach ($rows as $rowIndex => $row) {
            $xmlRows .= self::row($rowNumber++, array_values($row), array_fill(0, count($header), $rowIndex % 2 === 0 ? 4 : 5), 32);
        }

        $lastRow = max($headerRow, $rowNumber - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="'.$headerRow.'" topLeftCell="A'.($headerRow + 1).'" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A'.($headerRow + 1).'" sqref="A'.($headerRow + 1).'"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/>'
            .self::cols(count($header))
            .'<sheetData>'.$xmlRows.'</sheetData>'
            .'<autoFilter ref="A'.$headerRow.':'.$lastColumn.$lastRow.'"/>'
            .'<mergeCells count="1"><mergeCell ref="A1:'.$lastColumn.'1"/></mergeCells>'
            .'<pageMargins left="0.35" right="0.35" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'</worksheet>';
    }

    /**
     * @param  array<int, scalar|null>  $values
     * @param  array<int, int>  $styles
     */
    private static function row(int $number, array $values, array $styles, ?int $height = null): string
    {
        $attributes = ' r="'.$number.'"';
        if ($height !== null) {
            $attributes .= ' ht="'.$height.'" customHeight="1"';
        }

        $xml = '<row'.$attributes.'>';
        foreach ($values as $columnIndex => $value) {
            $cell = self::column($columnIndex + 1).$number;
            $style = $styles[$columnIndex] ?? 0;
            $xml .= '<c r="'.$cell.'" t="inlineStr"'.($style > 0 ? ' s="'.$style.'"' : '').'><is><t xml:space="preserve">'.self::xml((string) $value).'</t></is></c>';
        }

        return $xml.'</row>';
    }

    private static function cols(int $count): string
    {
        $default = array_fill(0, max(1, $count), 18);
        $widths = $count === 10
            ? [13, 26, 20, 16, 12, 12, 20, 20, 24, 42]
            : $default;

        $xml = '<cols>';
        foreach ($widths as $index => $width) {
            $column = $index + 1;
            $xml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }

        return $xml.'</cols>';
    }

    private static function column(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $mod = ($number - 1) % 26;
            $column = chr(65 + $mod).$column;
            $number = intdiv($number - $mod, 26);
        }

        return $column;
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Rekap Presensi" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="4">'
            .'<font><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF334155"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="16"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0F172A"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2"><border/><border><left style="thin"><color rgb="FFE2E8F0"/></left><right style="thin"><color rgb="FFE2E8F0"/></right><top style="thin"><color rgb="FFE2E8F0"/></top><bottom style="thin"><color rgb="FFE2E8F0"/></bottom></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" applyFill="1" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
