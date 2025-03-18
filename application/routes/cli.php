<?php

/**
 * CLI Routes
 *
 * This routes only will be available under a CLI environment
 */

// To enable Luthier-CI built-in cli commands
// uncomment the followings lines:

// Luthier\Cli::maker();
// Luthier\Cli::migrations();

// Backup Database to Google Drive
Route::cli('cron/database/{upload?}', 'Sys_backup@BackupDatabase');
Route::cli('cron/system/{upload?}', 'Sys_backup@BackupSystem');

Route::cli('optimize', function () {
    echo shell_exec('php struck clear optimize');
});

Route::cli('clear/{type}', function ($type) {
    $folders = [
        'cache' => APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'ci_session',
        'view' => APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'blade_cache',
        'log' => APPPATH . 'logs'
    ];

    // Define function for colored terminal output
    function coloredMessage($message, $type = 'info')
    {
        $color = $type === 'error' ? "\033[31m" : "\033[34m"; // Red for error, blue for info
        $resetColor = "\033[0m";
        $type = strtoupper($type);
        echo "{$color}[{$type}]{$resetColor} $message\n\n";
    }

    if (in_array($type, ['all', 'cache', 'view', 'views', 'log', 'logs', 'optimize'])) {
        // Set the folders to clear based on type
        $clearFolders = match ($type) {
            'cache' => [$folders['cache']],
            'view', 'views' => [$folders['view']],
            'log', 'logs' => [$folders['log']],
            'all' => array_values($folders),
            'optimize' => [$folders['view'], $folders['log']],
            default => []
        };

        foreach ($clearFolders as $path) {
            if (is_dir($path)) {
                deleteFolder($path);
            }
        }

        $message = ucfirst($type) . ' folder(s) cleared successfully';
        coloredMessage($message, 'info');
    } else {
        $message = "Error: Unsupported type '$type'. Use 'cache', 'view', 'log', 'all', or 'optimize'.";
        coloredMessage($message, 'error');
    }
});

// schedule
Route::cli('schedule:run', function () {
    $CI = &get_instance();
    $CI->load->config('customs/scheduler');
    $allNamespace = $CI->config->item('commands');

    if (hasData($allNamespace)) {
        $scheduler = cronScheduler();

        $scheduler->clearJobs(); // clear previous jobs
        foreach ($allNamespace as $namspaces) {
            app($namspaces)->handle($scheduler);
        }

        echo "Task Scheduling is running . . \n\n";

        // Reset the scheduler after a previous run
        $scheduler->resetRun()->run(); // now we can run it again
    } else {
        echo "No task/command to execute\n\n";
    }
});

Route::cli('schedule:list', function () {
    dd(cronScheduler()->getExecutedJobs());
});

Route::cli('schedule:fail', function () {
    $scheduler = cronScheduler();

    // get all failed jobs and select first
    $failedJob = $scheduler->getFailedJobs()[0];

    // exception that occurred during job
    $exception = $failedJob->getException();

    // job that failed
    $job = $failedJob->getJob();

    dd($failedJob, $exception, $job);
});

Route::cli('schedule:work', function () {
    $scheduler = cronScheduler();

    $CI = &get_instance();
    $CI->load->config('customs/scheduler');
    $allNamespace = $CI->config->item('commands');

    if (hasData($allNamespace)) {
        $scheduler = cronScheduler();

        $scheduler->clearJobs(); // clear previous jobs
        foreach ($allNamespace as $namspaces) {
            app($namspaces)->handle($scheduler);
        }

        echo "Task Scheduling is running . . \n\n";
        $scheduler->work();
    } else {
        echo "No task/command to execute\n\n";
    }
});

Route::cli('maintenance/{type}', function ($type = 'on') {
    if (in_array($type, ['on', 'off'])) {
        $filename = 'maintenance.flag';

        if ($type == 'on') {
            if (!file_exists($filename)) {
                fopen($filename, 'w');
            };
            print "[" . timestamp('d/m/Y h:i A') . "]: System is currently offline!\n\n";
        } else if ($type == 'off') {
            if (file_exists($filename)) {
                unlink($filename);
            }
            print "[" . timestamp('d/m/Y h:i A') . "]: System is back online!\n\n";
        }
    }
});
