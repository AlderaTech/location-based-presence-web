<?php

namespace App\Support;

class PdfReportService
{
    private const WIDTH = 842;

    private const HEIGHT = 595;

    private const MARGIN = 32;

    private const HEADER_HEIGHT = 30;

    private const MIN_ROW_HEIGHT = 24;

    private const LINE_HEIGHT = 9;

    private const TABLE_BOTTOM = 50;

    /**
     * @param  array<int, array<int, scalar|null>>  $rows
     * @param  array{judul?: string, metadata?: array<string, scalar|null>}  $options
     */
    public static function make(array $rows, array $options = []): string
    {
        $judul = (string) ($options['judul'] ?? 'Rekap Presensi');
        $metadata = is_array($options['metadata'] ?? null) ? $options['metadata'] : [];
        $header = array_values(array_shift($rows) ?? ['Data']);
        $columns = self::columns(count($header));
        $pages = self::paginate($rows, $columns, count($metadata));

        if ($pages === []) {
            $pages = [[
                'rows' => [],
                'heights' => [],
            ]];
        }

        $streams = [];
        foreach ($pages as $index => $pageRows) {
            $streams[] = self::page(
                $judul,
                $metadata,
                $header,
                $columns,
                $pageRows['rows'],
                $pageRows['heights'],
                $index + 1,
                count($pages)
            );
        }

        return self::document($streams);
    }

    /**
     * @param  array<string, scalar|null>  $metadata
     * @param  array<int, scalar|null>  $header
     * @param  array<int, array{x: int, width: int}>  $columns
     * @param  array<int, array<int, scalar|null>>  $rows
     * @param  array<int, int>  $rowHeights
     */
    private static function page(string $judul, array $metadata, array $header, array $columns, array $rows, array $rowHeights, int $page, int $totalPages): string
    {
        $stream = '';
        $stream .= self::rect(0, 0, self::WIDTH, self::HEIGHT, [255, 255, 255]);
        $stream .= self::text(self::MARGIN, 558, $judul, 15, 'F2', [15, 23, 42]);
        $stream .= self::text(self::MARGIN, 540, 'Laporan presensi guru berdasarkan filter yang dipilih.', 8, 'F1', [100, 116, 139]);

        $metadataIndex = 0;
        foreach ($metadata as $label => $value) {
            $row = intdiv($metadataIndex, 2);
            $x = $metadataIndex % 2 === 0 ? self::MARGIN : 430;
            $y = 518 - ($row * 14);
            $stream .= self::text($x, $y, $label.':', 8, 'F2', [51, 65, 85]);
            $stream .= self::text($x + 80, $y, (string) $value, 8, 'F1', [71, 85, 105]);
            $metadataIndex++;
        }

        $tableTop = 518 - max(1, (int) ceil(count($metadata) / 2)) * 14 - 18;
        $tableWidth = self::WIDTH - (self::MARGIN * 2);
        $headerY = $tableTop - self::HEADER_HEIGHT;
        $stream .= self::rect(self::MARGIN, $headerY, $tableWidth, self::HEADER_HEIGHT, [15, 23, 42]);

        foreach ($columns as $index => $column) {
            $label = (string) ($header[$index] ?? '');
            $stream .= self::multilineText($column['x'] + 4, $headerY + self::HEADER_HEIGHT - 11, self::wrap($label, $column['width'], 7), 7, 'F2', [255, 255, 255]);
        }

        if ($rows === []) {
            $y = $headerY - self::MIN_ROW_HEIGHT;
            $stream .= self::rect(self::MARGIN, $y, $tableWidth, self::MIN_ROW_HEIGHT, [248, 250, 252]);
            $stream .= self::text(self::MARGIN + 8, $y + 8, 'Tidak ada data sesuai filter.', 8, 'F1', [100, 116, 139]);
            $stream .= self::line(self::MARGIN, $y, self::MARGIN + $tableWidth, $y, [226, 232, 240]);
        }

        $currentY = $headerY;
        foreach ($rows as $rowIndex => $row) {
            $height = $rowHeights[$rowIndex] ?? self::rowHeight($row, $columns);
            $y = $currentY - $height;
            $fill = $rowIndex % 2 === 0 ? [255, 255, 255] : [248, 250, 252];
            $stream .= self::rect(self::MARGIN, $y, $tableWidth, $height, $fill);
            $stream .= self::line(self::MARGIN, $y, self::MARGIN + $tableWidth, $y, [226, 232, 240]);

            foreach ($columns as $index => $column) {
                $value = (string) ($row[$index] ?? '');
                $stream .= self::multilineText($column['x'] + 4, $y + $height - 11, self::wrap($value, $column['width'], 7), 7, 'F1', [30, 41, 59]);
            }

            $currentY = $y;
        }

        $bottom = $rows === [] ? $headerY - self::MIN_ROW_HEIGHT : $currentY;
        $stream .= self::line(self::MARGIN, $headerY, self::MARGIN, $bottom, [203, 213, 225]);
        $stream .= self::line(self::MARGIN + $tableWidth, $headerY, self::MARGIN + $tableWidth, $bottom, [203, 213, 225]);
        $stream .= self::text(self::MARGIN, 24, 'Presensi Guru', 8, 'F2', [100, 116, 139]);
        $stream .= self::text(self::WIDTH - self::MARGIN - 82, 24, 'Halaman '.$page.' dari '.$totalPages, 8, 'F1', [100, 116, 139]);

        return $stream;
    }

    /**
     * @param  array<int, string>  $streams
     */
    private static function document(array $streams): string
    {
        $pages = [];
        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '',
            '3 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj',
        ];

        foreach ($streams as $index => $stream) {
            $pageObjectId = 5 + ($index * 2);
            $contentObjectId = $pageObjectId + 1;
            $pages[] = $pageObjectId.' 0 R';
            $objects[] = $pageObjectId.' 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::WIDTH.' '.self::HEIGHT.'] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$contentObjectId.' 0 R >> endobj';
            $objects[] = $contentObjectId.' 0 obj << /Length '.strlen($stream)." >> stream\n".$stream."\nendstream endobj";
        }

        $objects[1] = '2 0 obj << /Type /Pages /Kids ['.implode(' ', $pages).'] /Count '.count($pages).' >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF";

        return $pdf;
    }

    /**
     * @return array<int, array{x: int, width: int}>
     */
    private static function columns(int $count): array
    {
        $widths = $count === 10
            ? [60, 112, 74, 62, 48, 48, 78, 82, 96, 118]
            : array_fill(0, max(1, $count), (int) floor((self::WIDTH - (self::MARGIN * 2)) / max(1, $count)));

        $x = self::MARGIN;

        return array_map(function (int $width) use (&$x): array {
            $column = ['x' => $x, 'width' => $width];
            $x += $width;

            return $column;
        }, $widths);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function text(int $x, int $y, string $text, int $size, string $font, array $rgb): string
    {
        return self::color($rgb, false).' BT /'.$font.' '.$size.' Tf 1 0 0 1 '.$x.' '.$y.' Tm ('.self::escape($text).") Tj ET\n";
    }

    /**
     * @param  array<int, string>  $lines
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function multilineText(int $x, int $y, array $lines, int $size, string $font, array $rgb): string
    {
        $stream = '';
        foreach ($lines as $index => $line) {
            $stream .= self::text($x, $y - ($index * self::LINE_HEIGHT), $line, $size, $font, $rgb);
        }

        return $stream;
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function rect(int $x, int $y, int $width, int $height, array $rgb): string
    {
        return 'q '.self::color($rgb, false).' '.$x.' '.$y.' '.$width.' '.$height." re f Q\n";
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function line(int $x1, int $y1, int $x2, int $y2, array $rgb): string
    {
        return 'q '.self::color($rgb, true).' 0.6 w '.$x1.' '.$y1.' m '.$x2.' '.$y2." l S Q\n";
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function color(array $rgb, bool $stroke): string
    {
        $operator = $stroke ? 'RG' : 'rg';

        return sprintf('%.3F %.3F %.3F %s', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255, $operator);
    }

    /**
     * @param  array<int, array<int, scalar|null>>  $rows
     * @param  array<int, array{x: int, width: int}>  $columns
     * @return array<int, array{rows: array<int, array<int, scalar|null>>, heights: array<int, int>}>
     */
    private static function paginate(array $rows, array $columns, int $metadataCount): array
    {
        $headerY = self::tableHeaderY($metadataCount);
        $availableHeight = $headerY - self::TABLE_BOTTOM;
        $pages = [];
        $pageRows = [];
        $pageHeights = [];
        $usedHeight = 0;

        foreach ($rows as $row) {
            $height = self::rowHeight($row, $columns);

            if ($pageRows !== [] && $usedHeight + $height > $availableHeight) {
                $pages[] = [
                    'rows' => $pageRows,
                    'heights' => $pageHeights,
                ];
                $pageRows = [];
                $pageHeights = [];
                $usedHeight = 0;
            }

            $pageRows[] = $row;
            $pageHeights[] = $height;
            $usedHeight += $height;
        }

        if ($pageRows !== []) {
            $pages[] = [
                'rows' => $pageRows,
                'heights' => $pageHeights,
            ];
        }

        return $pages;
    }

    private static function tableHeaderY(int $metadataCount): int
    {
        $tableTop = 518 - max(1, (int) ceil($metadataCount / 2)) * 14 - 18;

        return $tableTop - self::HEADER_HEIGHT;
    }

    /**
     * @param  array<int, scalar|null>  $row
     * @param  array<int, array{x: int, width: int}>  $columns
     */
    private static function rowHeight(array $row, array $columns): int
    {
        $maxLines = 1;

        foreach ($columns as $index => $column) {
            $maxLines = max($maxLines, count(self::wrap((string) ($row[$index] ?? ''), $column['width'], 7)));
        }

        return max(self::MIN_ROW_HEIGHT, 14 + ($maxLines * self::LINE_HEIGHT));
    }

    /**
     * @return array<int, string>
     */
    private static function wrap(string $value, int $width, int $fontSize): array
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        if ($value === '') {
            return [''];
        }

        $limit = max(4, (int) floor(($width - 8) / ($fontSize * 0.52)));
        $lines = [];
        $line = '';

        foreach (explode(' ', $value) as $word) {
            if (strlen($word) > $limit) {
                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }

                foreach (str_split($word, $limit) as $part) {
                    $lines[] = $part;
                }

                continue;
            }

            $candidate = $line === '' ? $word : $line.' '.$word;
            if (strlen($candidate) <= $limit) {
                $line = $candidate;

                continue;
            }

            $lines[] = $line;
            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines !== [] ? $lines : [''];
    }

    private static function escape(string $value): string
    {
        $converted = function_exists('iconv') ? @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) : false;
        $value = $converted === false ? $value : $converted;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
