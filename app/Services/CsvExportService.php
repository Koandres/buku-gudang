<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public function download(array $headers, iterable $rows, string $nama): StreamedResponse
    {
        $nama = preg_replace('/[^A-Za-z0-9_-]+/', '_', $nama) ?: 'laporan';

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    fn (mixed $value): string => $this->nilaiAman($value),
                    array_values($row),
                ));
            }

            fclose($handle);
        }, $nama.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function nilaiAman(mixed $value): string
    {
        if (! is_string($value)) {
            return is_scalar($value) ? (string) $value : '';
        }

        if (is_numeric($value)) {
            return $value;
        }

        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
