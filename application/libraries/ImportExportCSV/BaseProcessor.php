<?php

namespace App\Libraries\ImportExportCSV;

use Exception;
use RuntimeException;

/**
 * BaseProcessor - Abstract base class for CSV processing operations
 * Contains common functionality and database operations
 */
abstract class BaseProcessor
{
    protected $ci;
    protected $parallel = 1;
    protected $isBackground = false;
    protected $chunkSize = 1000;
    protected $table = 'system_file_process';
    protected $allowedExtensions = ['csv', 'txt'];
    protected $maxFileSize = 52428800; // 50MB default limit
    protected $skipHeaderRows = 0;

    /**
     * Constructor - Initialize CodeIgniter instance and check table existence
     * @throws RuntimeException If database connection fails
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        if (!$this->ci->db) {
            throw new RuntimeException("Failed to initialize database connection");
        }
        $this->checkAndCreateTable();
    }

    /**
     * Check if required table exists and create if not
     * @throws RuntimeException If table creation fails
     */
    protected function checkAndCreateTable()
    {
        try {
            if (!$this->ci->db->table_exists($this->table)) {
                $this->ci->load->dbforge();

                $fields = [
                    'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE],
                    'file_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
                    'file_size' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                    'file_format' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE],
                    'file_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
                    'total_data' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                    'total_processed' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                    'total_successful' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                    'total_failed' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                    'total_downloads' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                    'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'completed', 'failed'], 'default' => 'pending'],
                    'upload_timestamp' => ['type' => 'DATETIME', 'null' => TRUE],
                    'completion_timestamp' => ['type' => 'DATETIME', 'null' => TRUE],
                    'error_messages' => ['type' => 'TEXT', 'null' => TRUE],
                    'process_pid' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                    'is_deleted' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => TRUE],
                    'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE],
                    'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE]
                ];

                $this->ci->dbforge->add_field($fields);
                $this->ci->dbforge->add_key('id', TRUE);
                $this->ci->dbforge->create_table($this->table, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
            }
        } catch (Exception $e) {
            throw new RuntimeException("Failed to create/check table: " . $e->getMessage());
        }
    }

    /**
     * Set maximum file size limit
     * @param int $sizeMB Size limit in megabytes
     * @return self
     */
    public function setMaxFileSize($sizeMB)
    {
        $this->maxFileSize = $sizeMB * 1048576;
        return $this;
    }

    /**
     * Set chunk size for processing
     * @param int $size Chunk size
     * @return self
     */
    public function setChunkSize($size)
    {
        $this->chunkSize = max(100, min(5000, $size));
        return $this;
    }

    /**
     * Set number of parallel workers for background processing
     * @param int $workers Number of parallel workers (max 10)
     * @return self
     */
    public function setParallel($workers = 2)
    {
        $this->parallel = min(10, max(1, $workers));
        return $this;
    }

    /**
     * Enable background processing
     * @return self
     * @throws RuntimeException If PCNTL is not available
     */
    public function runBackground()
    {
        $this->isBackground = true;
        return $this;
    }

    /**
     * Update process progress
     * @param int $processId Process ID
     * @param int $total Total rows processed
     * @param int $successful Successful rows
     * @param int $failed Failed rows
     * @param array $errors Error messages
     */
    protected function updateProgress($processId, $total, $successful, $failed, $errors)
    {
        $this->ci->db->where('id', $processId)->update($this->table, [
            'total_processed' => $total,
            'total_successful' => $successful,
            'total_failed' => $failed,
            'error_messages' => json_encode($errors),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update process status
     * @param int $processId Process ID
     * @param string $status New status
     */
    protected function updateProcessStatus($processId, $status)
    {
        $this->ci->db->where('id', $processId)->update($this->table, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'completion_timestamp' => $status === 'completed' ? date('Y-m-d H:i:s') : null
        ]);
    }

    /**
     * Update process error
     * @param int $processId Process ID
     * @param string $error Error message
     */
    protected function updateProcessError($processId, $error)
    {
        $this->ci->db->where('id', $processId)->update($this->table, [
            'status' => 'failed',
            'error_messages' => json_encode([$error]),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get process status
     * @param int $processId Process ID
     * @return array Process information
     */
    public function getProcessStatus($processId)
    {
        $process = $this->ci->db->where('id', $processId)->get($this->table)->row_array();
        if (!$process) {
            throw new RuntimeException("Process not found");
        }
        return $process;
    }

    /**
     * Get process status
     * @param int $pid Process ID
     * @return array Process information
     */
    public function getProcessPid($pid)
    {
        $process = $this->ci->db->where('process_pid', $pid)->get($this->table)->row_array();
        if (!$process) {
            throw new RuntimeException("Process pid not found");
        }
        return $process;
    }

    /**
     * Check if a background process is still running
     * @param int $pid Process ID
     * @return bool
     */
    public function isProcessRunning($pid)
    {
        return posix_kill($pid, 0);
    }
}