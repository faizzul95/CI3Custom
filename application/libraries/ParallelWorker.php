<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ParallelWorker
{
    private $pid;
    private $callback;
    private $tmpFile;
    private $isWindows;
    private $timeout;
    private $startTime;

    public function __construct(callable $callback, $tempDir = NULL, $timeout = 3600)
    {
        $this->callback = $callback;
        $this->timeout = $timeout;
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $this->createTempFile($tempDir);
    }

    private function createTempFile($dir = NULL)
    {
        $folder = FCPATH . "application" . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "ParallelWorker";
        $tempDir = empty($dir) ? $folder : $folder . DIRECTORY_SEPARATOR . $dir;

        // Check if the directory exists and is writable
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            // Attempt to create the directory if it doesn't exist
            if (!mkdir($tempDir, 0644, true)) {
                throw new RuntimeException("Failed to create temporary directory: $tempDir");
            }
        }

        // Create the temporary file
        $this->tmpFile = tempnam($tempDir, 'worker_');
        if ($this->tmpFile === false) {
            throw new RuntimeException("Failed to create temporary file in: $tempDir");
        }

        // Ensure the file is readable and writable
        if (!chmod($this->tmpFile, 0644)) {
            throw new RuntimeException("Failed to set permissions on temporary file: $this->tmpFile");
        }
    }

    public function start()
    {
        $this->startTime = time();
        if ($this->isWindows) {
            $this->windowsStart();
        } else {
            $this->linuxStart();
        }
    }

    private function windowsStart()
    {
        $result = $this->executeCallback();
        $this->writeResult($result);
    }

    private function linuxStart()
    {
        if (!function_exists('pcntl_fork')) {
            $this->windowsStart();
            return;
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new RuntimeException('Could not fork process');
        } elseif ($pid) {
            $this->pid = $pid;
        } else {
            $result = $this->executeCallback();
            $this->writeResult($result);
            exit(0);
        }
    }

    private function executeCallback()
    {
        try {
            return call_user_func($this->callback);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function writeResult($result)
    {
        $serialized = serialize($result);
        if (file_put_contents($this->tmpFile, $serialized) === false) {
            throw new RuntimeException("Failed to write to temporary file: $this->tmpFile");
        }
    }

    public function getResult()
    {
        if ($this->isWindows) {
            return $this->windowsGetResult();
        } else {
            return $this->linuxGetResult();
        }
    }

    private function windowsGetResult()
    {
        $this->waitForCompletion();
        return $this->readAndCleanup();
    }

    private function linuxGetResult()
    {
        if (!function_exists('pcntl_waitpid')) {
            return $this->windowsGetResult();
        }

        $status = 0;
        while (true) {
            $res = pcntl_waitpid($this->pid, $status, WNOHANG);
            if ($res == -1 || $res > 0) {
                break;
            }
            if ($this->hasTimedOut()) {
                posix_kill($this->pid, SIGKILL);
                throw new RuntimeException("Process timed out after {$this->timeout} seconds");
            }
            usleep(100000); // Sleep for 100ms to prevent CPU hogging
        }

        return $this->readAndCleanup();
    }

    private function waitForCompletion()
    {
        while (!file_exists($this->tmpFile) || filesize($this->tmpFile) == 0) {
            if ($this->hasTimedOut()) {
                throw new RuntimeException("Process timed out after {$this->timeout} seconds");
            }
            usleep(100000); // Sleep for 100ms to prevent CPU hogging
        }
    }

    private function hasTimedOut()
    {
        return (time() - $this->startTime) > $this->timeout;
    }

    private function readAndCleanup()
    {
        if (!file_exists($this->tmpFile)) {
            throw new RuntimeException("Temporary file not found: $this->tmpFile");
        }

        $content = file_get_contents($this->tmpFile);
        if ($content === false) {
            throw new RuntimeException("Failed to read from temporary file: $this->tmpFile");
        }

        $result = unserialize($content);

        if (!unlink($this->tmpFile)) {
            error_log("Failed to delete temporary file: $this->tmpFile");
        }

        if (isset($result['error'])) {
            throw new RuntimeException("Worker process error: " . $result['error']);
        }

        return $result;
    }

    public function __destruct()
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }
}
