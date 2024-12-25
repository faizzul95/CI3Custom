<?php

namespace App\Libraries\ImportExportCSV;

use Exception;
use RuntimeException;

/**
 * ExportProcessor - Class for handling CSV export operations
 * Extends BaseProcessor with export-specific functionality
 */
class ExportProcessor extends BaseProcessor
{
    private $headers = [];
    private $filename;
    private $generatePath;

    /**
     * Set CSV headers for export
     * @param array $headers Array of header names
     * @return self
     * @throws RuntimeException If headers are invalid
     */
    public function setHeaders(array $headers)
    {
        if (empty($headers) || !is_array($headers)) {
            throw new RuntimeException("Headers must be a non-empty array");
        }
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set custom filename for export
     * @param string $filename Custom filename without extension
     * @return self
     * @throws RuntimeException If filename is invalid
     */
    public function setFilename($filename)
    {
        if (empty($filename) || !preg_match('/^[\w\-. ]+$/', $filename)) {
            throw new RuntimeException("Invalid filename format");
        }
        $this->filename = $filename;
        return $this;
    }

    /**
     * Set path for generated files
     * @param string $path Path where files will be generated
     * @return self
     * @throws RuntimeException If path is invalid
     */
    public function setGeneratePath($path)
    {
        if (!is_dir($path) || !is_writable($path)) {
            throw new RuntimeException("Directory does not exist or is not writable: {$path}");
        }
        $this->generatePath = rtrim($path, '/') . '/';
        return $this;
    }

    /**
     * Export data to CSV with callback processing
     * @param mixed $model Model instance or query builder
     * @param callable $callback Callback function to process each row
     * @return array Process result
     * @throws RuntimeException If export fails
     */
    public function export($model, callable $callback)
    {
        try {
            if (empty($this->generatePath)) {
                throw new RuntimeException("Generate path not set");
            }

            $filename = $this->filename ?? 'export_' . date('YmdHis') . '.csv';
            if (!preg_match('/\.csv$/', $filename)) {
                $filename .= '.csv';
            }

            $filepath = $this->generatePath . $filename;

            $fileInfo = [
                'file_name' => $filename,
                'file_path' => $filepath,
                'file_format' => 'csv',
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
                $this->processExportBackground($model, $callback, $processId, $filepath) :
                $this->processExportForeground($model, $callback, $processId, $filepath);
        } catch (Exception $e) {
            if (isset($processId)) {
                $this->updateProcessError($processId, $e->getMessage());
            }
            throw new RuntimeException("Export failed: " . $e->getMessage());
        }
    }

    /**
     * Process export in background
     * @param mixed $model Model instance
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @param string $filepath Output file path
     * @return array Process status
     */
    private function processExportBackground($model, $callback, $processId, $filepath)
    {
        try {
            $this->updateProcessStatus($processId, 'processing');

            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new RuntimeException("Could not fork process");
            } else if ($pid) {
                // Parent process
                $this->ci->db->where('id', $processId)->update($this->table, [
                    'process_pid' => $pid
                ]);
                return ['process_id' => $processId, 'status' => 'processing'];
            } else {
                // Child process
                try {
                    $this->ci->db = $this->ci->load->database('default', TRUE);
                    $this->processExportData($model, $callback, $processId, $filepath);
                    exit(0);
                } catch (Exception $e) {
                    error_log("Export process error: " . $e->getMessage());
                    $this->updateProcessError($processId, $e->getMessage());
                    exit(1);
                }
            }
        } catch (Exception $e) {
            $this->updateProcessError($processId, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process export in foreground
     * @param mixed $model Model instance
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @param string $filepath Output file path
     * @return array Process result
     */
    private function processExportForeground($model, $callback, $processId, $filepath)
    {
        $handle = null;
        $tempFile = $filepath . '.tmp';

        try {
            $this->updateProcessStatus($processId, 'processing');

            $handle = fopen($tempFile, 'w');
            if ($handle === false) {
                throw new RuntimeException("Could not open file for writing");
            }

            // Write headers if specified
            if (!empty($this->headers)) {
                if (fputcsv($handle, $this->headers) === false) {
                    throw new RuntimeException("Failed to write headers");
                }
            }

            $offset = 0;
            $totalRows = 0;
            $successful = 0;
            $failed = 0;
            $errors = [];

            while (true) {
                $records = $model->limit($this->chunkSize)->offset($offset)->get()->result();
                if (empty($records)) break;

                foreach ($records as $record) {
                    try {
                        $row = $callback($record);
                        if ($row && is_array($row)) {
                            if (fputcsv($handle, $row) !== false) {
                                $successful++;
                            } else {
                                throw new RuntimeException("Failed to write row");
                            }
                        } else {
                            $failed++;
                            $errors[] = "Row {$totalRows}: Invalid row data returned";
                        }
                    } catch (Exception $e) {
                        $failed++;
                        $errors[] = "Row {$totalRows}: " . $e->getMessage();
                    }
                    $totalRows++;

                    if ($totalRows % 100 == 0) {
                        $this->updateProgress($processId, $totalRows, $successful, $failed, $errors);
                    }
                }

                $offset += $this->chunkSize;
            }

            fclose($handle);
            $handle = null;

            if (!rename($tempFile, $filepath)) {
                throw new RuntimeException("Failed to move temporary file to final destination");
            }

            $this->updateProgress($processId, $totalRows, $successful, $failed, $errors);
            $this->ci->db->where('id', $processId)->update($this->table, [
                'file_size' => filesize($filepath),
                'status' => 'completed',
                'completion_timestamp' => date('Y-m-d H:i:s')
            ]);

            return [
                'process_id' => $processId,
                'total_rows' => $totalRows,
                'successful' => $successful,
                'failed' => $failed,
                'errors' => $errors,
                'file_path' => $filepath
            ];
        } catch (Exception $e) {
            $this->updateProcessError($processId, $e->getMessage());
            throw $e;
        } finally {
            if ($handle !== null) {
                fclose($handle);
            }
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Process export data for background processing
     * @param mixed $model Model instance
     * @param callable $callback Callback function
     * @param int $processId Process ID
     * @param string $filepath Output file path
     */
    private function processExportData($model, callable $callback, $processId, $filepath)
    {
        $handle = null;
        $tempFile = $filepath . '.tmp';

        try {
            $handle = fopen($tempFile, 'w');
            if ($handle === false) {
                throw new RuntimeException("Could not open temporary file for writing");
            }

            if (!empty($this->headers)) {
                if (fputcsv($handle, $this->headers) === false) {
                    throw new RuntimeException("Failed to write headers");
                }
            }

            $offset = 0;
            $totalRows = 0;
            $successful = 0;
            $failed = 0;
            $errors = [];
            $lastUpdateTime = microtime(true);

            $totalCount = $model->count_all_results();
            $this->ci->db->where('id', $processId)->update($this->table, ['total_data' => $totalCount]);

            while (true) {
                $records = $model->limit($this->chunkSize)->offset($offset)->get()->result();
                if (empty($records)) break;

                foreach ($records as $record) {
                    try {
                        $row = $callback($record);
                        if ($row && is_array($row)) {
                            if (fputcsv($handle, $row) !== false) {
                                $successful++;
                            } else {
                                throw new RuntimeException("Failed to write row");
                            }
                        } else {
                            $failed++;
                            $errors[] = "Invalid row data returned";
                        }
                    } catch (Exception $e) {
                        $failed++;
                        $errors[] = $e->getMessage();
                    }
                    $totalRows++;

                    if ((microtime(true) - $lastUpdateTime) >= 1.0) {
                        $this->updateProgress($processId, $totalRows, $successful, $failed, array_slice($errors, -100));
                        $lastUpdateTime = microtime(true);
                    }
                }

                $offset += $this->chunkSize;
            }

            fclose($handle);
            $handle = null;

            if (!rename($tempFile, $filepath)) {
                throw new RuntimeException("Failed to move temporary file to final destination");
            }

            $this->ci->db->where('id', $processId)->update($this->table, [
                'file_size' => filesize($filepath),
                'status' => 'completed',
                'completion_timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            throw new RuntimeException("Export processing failed: " . $e->getMessage());
        } finally {
            if ($handle !== null) {
                fclose($handle);
            }
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
