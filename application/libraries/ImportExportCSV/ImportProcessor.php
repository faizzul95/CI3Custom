<?php

namespace App\Libraries\ImportExportCSV;

use Exception;
use RuntimeException;

/**
 * ImportProcessor - Class for handling CSV import operations
 * Extends BaseProcessor with import-specific functionality
 */
class ImportProcessor extends BaseProcessor
{
    protected $processDir;

    public function __construct()
    {
        parent::__construct();
        $this->processDir = APPPATH . 'cache/import_processes/';
        if (!file_exists($this->processDir)) {
            mkdir($this->processDir, 0755, true);
        }
    }

    /**
     * Import CSV file with callback processing
     * @param string $filepath Path to CSV file
     * @param callable $callback Callback function to process each row
     * @return array Process result
     * @throws RuntimeException If import fails
     */
    public function import($filepath, callable $callback)
    {
        try {
            $this->validateFile($filepath);

            $fileInfo = [
                'file_name' => basename($filepath),
                'file_size' => filesize($filepath),
                'file_format' => pathinfo($filepath, PATHINFO_EXTENSION),
                'upload_timestamp' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->ci->db->trans_start();
            $this->ci->db->insert($this->table, $fileInfo);
            $processId = $this->ci->db->insert_id();
            $this->ci->db->trans_complete();

            if (!$processId) {
                throw new RuntimeException("Failed to create process record");
            }

            return $this->isBackground ?
                $this->processImportBackground($filepath, $callback, $processId) :
                $this->processImportForeground($filepath, $callback, $processId);
        } catch (Exception $e) {
            if (isset($processId)) {
                $this->updateProcessError($processId, $e->getMessage());
            }
            throw new RuntimeException("Import failed: " . $e->getMessage());
        }
    }

    /**
     * Process import in background using shell command
     * @param string $filepath File path
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @return array Process status
     * @throws RuntimeException If background processing fails
     */
    private function processImportBackground($filepath, $callback, $processId)
    {
        try {
            // Prepare the command to run the process in background
            $callbackFile = $this->storeCallbackInCache($callback, $processId);

            // Create a unique temporary file name
            $tempFile = FCPATH . '/background_process_' . $processId . '.php';

            $class = get_called_class();
            $className = basename(str_replace('\\', '/', __CLASS__));

            // Create the content for the temporary file, which will start the background process
            $tempFileContent = "<?php
                use $class;
                
                \$importer = new $className;
                \$importer->startBackgroundProcess('{$filepath}', '{$callbackFile}');
            ?>";

            // Write the content to the temporary file
            file_put_contents($tempFile, $tempFileContent);

            // Run the temporary PHP file as a background process
            $command = "php {$tempFile} > /dev/null 2>&1 &";
            exec($command);

            // Optionally, remove the temporary file after starting the background process
            sleep(5);

            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            $this->updateProcessStatus($processId, 'processing');

            return ['process_id' => $processId, 'status' => 'processing'];
        } catch (Exception $e) {
            $this->updateProcessError($processId, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store callback function in cache and return the file path
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @return string File path for the callback
     */
    private function storeCallbackInCache($callback, $processId)
    {
        $callbackFile = $this->processDir . 'callback_' . $processId . '.json';
        file_put_contents($callbackFile, json_encode($callback));
        return $callbackFile;
    }

    /**
     * Process import in foreground (synchronous)
     * @param string $filepath File path
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @return array Process result
     */
    private function processImportForeground($filepath, $callback, $processId)
    {
        $handle = null;

        try {
            $this->updateProcessStatus($processId, 'processing');

            $handle = fopen($filepath, 'r');
            if ($handle === false) {
                throw new RuntimeException("Could not open file for reading");
            }

            // Skip header rows
            for ($i = 0; $i < $this->skipHeaderRows; $i++) {
                if (fgetcsv($handle) === false) {
                    throw new RuntimeException("Could not skip header row $i");
                }
            }

            // Process data
            $columnNames = $this->generateColumnNames(count(fgetcsv($handle)));
            rewind($handle);

            // Skip headers again after counting columns
            for ($i = 0; $i < $this->skipHeaderRows; $i++) {
                fgetcsv($handle);
            }

            $totalRows = 0;
            $successful = 0;
            $failed = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== FALSE) {
                $totalRows++;

                // Skip empty rows
                if (empty(array_filter($row, function ($value) {
                    return $value !== null && $value !== '';
                }))) {
                    continue;
                }

                try {
                    // Ensure row has the same number of elements as column names
                    $rowCount = count($row);
                    $columnCount = count($columnNames);

                    if ($rowCount < $columnCount) {
                        // Pad the row with empty values if it's shorter
                        $row = array_pad($row, $columnCount, '');
                    } elseif ($rowCount > $columnCount) {
                        // Trim the row if it's longer
                        $row = array_slice($row, 0, $columnCount);
                    }

                    $data = array_combine($columnNames, $row);
                    if ($data === false) {
                        throw new RuntimeException("Failed to combine column names with data");
                    }

                    $result = $callback($data);
                    $successful += $result ? 1 : 0;
                    $failed += $result ? 0 : 1;
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = "Row {$totalRows}: " . $e->getMessage();
                }

                if ($totalRows % 100 == 0) {
                    $this->updateProgress($processId, $totalRows, $successful, $failed, $errors);
                }
            }

            $this->updateProgress($processId, $totalRows, $successful, $failed, $errors);
            $this->updateProcessStatus($processId, 'completed');

            return [
                'process_id' => $processId,
                'total_rows' => $totalRows,
                'successful' => $successful,
                'failed' => $failed,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $this->updateProcessError($processId, $e->getMessage());
            throw $e;
        } finally {
            if ($handle !== null) {
                fclose($handle);
            }
        }
    }

    /**
     * Execute background process for CSV import
     * This is called from the background process initiated earlier
     * @param string $filepath File path
     * @param string $callbackFile Path to the stored callback
     */
    public function startBackgroundProcess($filepath, $callbackFile)
    {
        $callbackData = file_get_contents($callbackFile);
        $callbacks = json_decode($callbackData, true);

        $processId = basename($callbackFile, '.json');
        $callback = $callbacks[$processId];

        try {
            $this->processImportForeground($filepath, $callback, $processId);
            // Remove the callback file upon completion
            unlink($callbackFile);
        } catch (Exception $e) {
            error_log("Background process failed for process ID {$processId}: " . $e->getMessage());
            unlink($callbackFile);
        }
    }

    /**
     * Generate alphabetical column names (A, B, C, ..., AA, AB, etc.)
     * @param int $count Number of columns
     * @return array
     */
    private function generateColumnNames($count)
    {
        $columns = [];
        for ($i = 0; $i < $count; $i++) {
            $columnName = '';
            $num = $i;
            do {
                $columnName = chr(65 + ($num % 26)) . $columnName;
                $num = floor($num / 26) - 1;
            } while ($num >= 0);
            $columns[] = $columnName;
        }
        return $columns;
    }

    /**
     * Validate file properties
     * @param string $filepath File path
     * @throws RuntimeException If file validation fails
     */
    private function validateFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new RuntimeException("File not found: {$filepath}");
        }

        if (!is_readable($filepath)) {
            throw new RuntimeException("File is not readable: {$filepath}");
        }

        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new RuntimeException("Invalid file extension. Allowed: " . implode(', ', $this->allowedExtensions));
        }

        if (filesize($filepath) > $this->maxFileSize) {
            throw new RuntimeException("File size exceeds the maximum allowed: " . $this->maxFileSize);
        }
    }

    /**
     * Set number of header rows to skip
     * @param int $rows Number of rows to skip
     * @return self
     */
    public function skipHeader($rows = 1)
    {
        $this->skipHeaderRows = max(0, intval($rows));
        return $this;
    }
}