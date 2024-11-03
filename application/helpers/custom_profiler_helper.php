<?php

global $profiler;
$profiler = [];

function startProfiler($name = 'default') {
    global $profiler;
    
    $profiler[$name] = [
        'start_time' => microtime(true),
        'start_memory' => [
            'current' => memory_get_usage(false),
            'real' => memory_get_usage(true),
            'peak_current' => memory_get_peak_usage(false),
            'peak_real' => memory_get_peak_usage(true)
        ],
        'start_cpu' => getrusage(),
        'start_datetime' => date('d/m/Y H:i:s.u'),
        'process_info' => [
            'pid' => getmypid(),
            'user' => get_current_user(),
            'php_version' => PHP_VERSION
        ]
    ];
}

function stopProfiler($name = 'default') {
    global $profiler;
    
    if (!isset($profiler[$name])) {
        return [
            'success' => false,
            'message' => "Profiler '$name' was not started"
        ];
    }
    
    $profiler[$name]['end_time'] = microtime(true);
    $profiler[$name]['end_memory'] = [
        'current' => memory_get_usage(false),
        'real' => memory_get_usage(true),
        'peak_current' => memory_get_peak_usage(false),
        'peak_real' => memory_get_peak_usage(true)
    ];
    $profiler[$name]['end_cpu'] = getrusage();
    $profiler[$name]['end_datetime'] = date('d/m/Y H:i:s.u');
    
    return [
        'success' => true,
        'message' => "Profiler '$name' stopped successfully"
    ];
}

function showProfiler($name = 'default') {
    global $profiler;
    
    if (!isset($profiler[$name])) {
        return [
            'success' => false,
            'message' => "Profiler '$name' not found"
        ];
    }
    
    // Time calculations
    $time_diff = $profiler[$name]['end_time'] - $profiler[$name]['start_time'];
    $hours = floor($time_diff / 3600);
    $minutes = floor(($time_diff % 3600) / 60);
    $seconds = floor((int) $time_diff % 60) ?? 0;
    $milliseconds = round(($time_diff - floor($time_diff)) * 1000);
    $microseconds = round(($time_diff - floor($time_diff)) * 1000000) % 1000;

    // CPU calculations
    $start_cpu = $profiler[$name]['start_cpu'];
    $end_cpu = $profiler[$name]['end_cpu'];
    
    // User mode CPU time
    $user_cpu_usage = ($end_cpu["ru_utime.tv_sec"] + $end_cpu["ru_utime.tv_usec"] / 1000000)
                    - ($start_cpu["ru_utime.tv_sec"] + $start_cpu["ru_utime.tv_usec"] / 1000000);
    
    // System mode CPU time
    $system_cpu_usage = ($end_cpu["ru_stime.tv_sec"] + $end_cpu["ru_stime.tv_usec"] / 1000000)
                     - ($start_cpu["ru_stime.tv_sec"] + $start_cpu["ru_stime.tv_usec"] / 1000000);
    
    // Memory calculations
    $memory_diff = [
        'current' => $profiler[$name]['end_memory']['current'] - $profiler[$name]['start_memory']['current'],
        'real' => $profiler[$name]['end_memory']['real'] - $profiler[$name]['start_memory']['real']
    ];

    return [
        'profile_name' => $name,
        'timing' => [
            'start' => $profiler[$name]['start_datetime'],
            'end' => $profiler[$name]['end_datetime'],
            'execution_time' => [
                'formatted' => sprintf('%02d:%02d:%02d.%03d.%03d', 
                    $hours, $minutes, $seconds, $milliseconds, $microseconds),
                'breakdown' => [
                    'hours' => $hours,
                    'minutes' => $minutes,
                    'seconds' => $seconds,
                    'milliseconds' => $milliseconds,
                    'microseconds' => $microseconds
                ],
                'total_seconds' => $time_diff
            ]
        ],
        'memory' => [
            'start' => [
                'current' => formatBytes($profiler[$name]['start_memory']['current']),
                'real' => formatBytes($profiler[$name]['start_memory']['real']),
                'peak_current' => formatBytes($profiler[$name]['start_memory']['peak_current']),
                'peak_real' => formatBytes($profiler[$name]['start_memory']['peak_real'])
            ],
            'end' => [
                'current' => formatBytes($profiler[$name]['end_memory']['current']),
                'real' => formatBytes($profiler[$name]['end_memory']['real']),
                'peak_current' => formatBytes($profiler[$name]['end_memory']['peak_current']),
                'peak_real' => formatBytes($profiler[$name]['end_memory']['peak_real'])
            ],
            'difference' => [
                'current' => formatBytes($memory_diff['current']),
                'real' => formatBytes($memory_diff['real'])
            ]
        ],
        'cpu' => [
            'user_mode' => [
                'seconds' => round($user_cpu_usage, 4),
                'percentage' => round(($user_cpu_usage / $time_diff) * 100, 2)
            ],
            'system_mode' => [
                'seconds' => round($system_cpu_usage, 4),
                'percentage' => round(($system_cpu_usage / $time_diff) * 100, 2)
            ],
            'total' => [
                'seconds' => round($user_cpu_usage + $system_cpu_usage, 4),
                'percentage' => round((($user_cpu_usage + $system_cpu_usage) / $time_diff) * 100, 2)
            ]
        ],
        'process_info' => $profiler[$name]['process_info']
    ];
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}