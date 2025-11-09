<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Event\Runtime\PHP;

class ProcessCsvUpload implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $fileUploadId;
    public $timeout = 300;

    public function __construct($fileUploadId)
    {
        $this->fileUploadId = $fileUploadId;
    }

    public function handle()
    {
        $upload = FileUpload::find($this->fileUploadId);
        if (!$upload) {
            return;
        }

        $upload->update(['status' => 'processing']);

        try {
            $path = $upload->path;
            $stream = Storage::path($path);
            if (!file_exists($stream)) {
                throw new Exception('File missing: ' . $path);
            }

            $handle = fopen($stream, 'r');
            if (!$handle) {
                throw new Exception('Unable to open file');
            }

            // Read header row
            $header = null;
            $map = null;

            $batch = [];
            $batchSize = 500; // tune as needed

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if ($header === null) {
                    // Normalize header (trim, uppercase)
                    $header = array_map(function ($h) {
                        return trim(strtoupper($h));
                    }, $row);

                    // Expected mapping to fields:
                    // UNIQUE_KEY, PRODUCT_TITLE, PRODUCT_DESCRIPTION, STYLE#, SANMAR_MAINFRAME_COLOR, SIZE, COLOR_NAME, PIECE_PRICE
                    continue;
                }

                // convert row to associative by header
                $assoc = [];
                foreach ($header as $i => $colName) {
                    $value = $row[$i] ?? null;
                    // Clean non-UTF-8 chars
                    if ($value !== null) {
                        $value = $this->cleanUtf8($value);
                    }
                    $assoc[$colName] = $value;
                }

                // map to DB columns
                $uniqueKey = $assoc['UNIQUE_KEY'] ?? null;
                if (!$uniqueKey) {
                    // skip rows without unique key
                    continue;
                }

                $productData = [
                    'unique_key' => $uniqueKey,
                    'product_title' => $assoc['PRODUCT_TITLE'] ?? null,
                    'product_description' => $assoc['PRODUCT_DESCRIPTION'] ?? null,
                    'style' => $assoc['STYLE#'] ?? ($assoc['STYLE'] ?? null),
                    'sanmar_mainframe_color' => $assoc['SANMAR_MAINFRAME_COLOR'] ?? null,
                    'size' => $assoc['SIZE'] ?? null,
                    'color_name' => $assoc['COLOR_NAME'] ?? null,
                    'piece_price' => $this->parseDecimal($assoc['PIECE_PRICE'] ?? null),
                ];

                $batch[] = $productData;

                if (count($batch) >= $batchSize) {
                    $this->upsertBatch($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                $this->upsertBatch($batch);
            }

            fclose($handle);

            $upload->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
        } catch (Exception $e) {
            $upload->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Upsert a batch of product rows
     * 
     * @param array $rows
     * @return void
     */
    protected function upsertBatch(array $rows): void
    {
        Product::upsert(
            $rows,
            ['unique_key'], // unique key constraint
            [
                'product_title',
                'product_description',
                'style',
                'sanmar_mainframe_color',
                'size',
                'color_name',
                'piece_price',
                'updated_at',
            ]
        );
    }

    /**
     * Clean a string to ensure it is valid UTF-8
     * 
     * @param string $value
     * @return string
     */
    protected function cleanUtf8($value): string
    {
        // Convert to UTF-8, strip invalid chars
        // First try to detect encoding
        $clean = @iconv(mb_detect_encoding($value, mb_detect_order(), true) ?: 'UTF-8', 'UTF-8//IGNORE', $value);
        if ($clean === false) {
            $clean = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        // Remove non-printable characters (control chars)
        $clean = preg_replace('/[^\P{C}\n\r\t]+/u', '', $clean);
        return trim($clean);
    }

    /**
     * Parse decimal from string, removing currency symbols and commas
     * 
     * @param string|null $value
     * @return float|null
     */
    protected function parseDecimal($value): float|null
    {
        if ($value === null) return null;
        // remove currency symbols and commas
        $v = preg_replace('/[^\d\.\-]/', '', $value);
        if ($v === '') return null;
        return (float)$v;
    }
}
