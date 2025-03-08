<?php

/**
 * Debug Class
 *
 * A comprehensive utility class for debugging PHP applications with various output options.
 *
 * @category  Helpers
 * @package   CT
 * @author    Mohd Fahmy Izwan Zulkhafri <faizzul14@gmail.com>
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0.0
 */
class Debug
{
    /**
     * Print data in readable format and terminate execution.
     * Accepts multiple parameters.
     */
    public function dd()
    {
        array_map(function ($param) {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }, func_get_args());
        die;
    }

    /**
     * Print data in readable format without terminating execution.
     * Accepts multiple parameters.
     */
    public function dump()
    {
        array_map(function ($param) {
            echo '<pre>';
            var_dump($param);
            echo '</pre>';
        }, func_get_args());
    }

    /**
     * Print data with print_r without terminating execution.
     * Accepts multiple parameters.
     */
    public function pr()
    {
        array_map(function ($param) {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }, func_get_args());
    }

    /**
     * Format execution time with detailed precision.
     *
     * @param float $executionTime Execution time in seconds
     * @return string Formatted execution time
     */
    public function formatExecutionTime($executionTime)
    {
        // Calculate nanoseconds using microtime
        $microtime = microtime(true);
        $nanoseconds = (int) (($microtime - floor($microtime)) * 1000000000);

        // Calculate milliseconds and other time units
        $milliseconds = round(($executionTime - floor($executionTime)) * 1000, 2);
        $totalSeconds = floor($executionTime);
        $seconds = $totalSeconds % 60;
        $minutes = floor(($totalSeconds % 3600) / 60);
        $hours = floor($totalSeconds / 3600);

        // Format the execution time with nanoseconds
        $formattedExecutionTime = sprintf("%dh %dm %ds %dms %dns", $hours, $minutes, $seconds, $milliseconds, $nanoseconds);

        // Handle cases where some time units are zero
        $formattedExecutionTime = preg_replace('/^0+h /', '', $formattedExecutionTime);
        $formattedExecutionTime = preg_replace('/^0+m /', '', $formattedExecutionTime);
        $formattedExecutionTime = preg_replace('/^0+s /', '', $formattedExecutionTime);
        $formattedExecutionTime = preg_replace('/^0+ms /', '', $formattedExecutionTime);

        return $formattedExecutionTime;
    }

    /**
     * Format execution time between two points.
     *
     * @param float $start Start time (from microtime(true))
     * @param float $end End time (from microtime(true), defaults to current time)
     * @param bool $echo Whether to echo the result (default: true)
     * @param bool $detailed Whether to use detailed formatting (default: false)
     * @return string Formatted time
     */
    public function executionTime($start, $end = null, $echo = true, $detailed = false)
    {
        $end = $end ?: microtime(true);
        $time = $end - $start;

        if ($detailed) {
            $formatted = $this->formatExecutionTime($time);
        } else {
            if ($time < 0.001) {
                $formatted = round($time * 1000000) . ' μs';
            } elseif ($time < 1) {
                $formatted = round($time * 1000, 3) . ' ms';
            } else {
                $formatted = round($time, 3) . ' s';
            }
        }

        if ($echo) {
            echo '<div style="background-color:#e9f7ef; padding:5px 10px; border-left:3px solid #27ae60; margin:5px 0;">';
            echo "Execution time: {$formatted}";
            echo '</div>';
        }

        return $formatted;
    }

    /**
     * Print memory usage information.
     *
     * @param bool $peak Whether to show peak memory usage (default: false)
     * @param bool $echo Whether to echo the result (default: true)
     * @param bool $detailed Whether to use detailed formatting (default: false)
     * @return string Memory usage information
     */
    public function memoryUsage($peak = false, $echo = true, $detailed = false)
    {
        if ($peak) {
            $memory = memory_get_peak_usage(true);
        } else {
            $memory = memory_get_usage(true);
        }

        if ($detailed) {
            $formatted = $this->formatBytes($memory);
        } else {
            if ($memory < 1024) {
                $formatted = $memory . ' bytes';
            } elseif ($memory < 1048576) {
                $formatted = round($memory / 1024, 2) . ' KB';
            } else {
                $formatted = round($memory / 1048576, 2) . ' MB';
            }
        }

        if ($echo) {
            echo '<div style="background-color:#e9f7ef; padding:5px 10px; border-left:3px solid #27ae60; margin:5px 0;">';
            echo "Memory usage" . ($peak ? ' (peak)' : '') . ": {$formatted}";
            echo '</div>';
        }

        return $formatted;
    }

    /**
     * Generate backtrace with formatting.
     *
     * @param bool $html Whether to format output as HTML (default: true)
     * @param bool $echo Whether to echo the result (default: true)
     * @param int $limit Limit number of stack frames (default: 0 = unlimited)
     * @return string Formatted backtrace
     */
    public function backtrace($html = true, $echo = true, $limit = 0)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit ?: 0);
        array_shift($trace); // Remove call to this method

        $output = '';

        if ($html) {
            $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
            $output .= '<h3 style="margin-top:0;">Debug Backtrace:</h3>';
            $output .= '<ol>';

            foreach ($trace as $i => $t) {
                $file = isset($t['file']) ? $t['file'] : '[internal function]';
                $line = isset($t['line']) ? $t['line'] : '';
                $class = isset($t['class']) ? $t['class'] . $t['type'] : '';
                $function = $t['function'];

                $output .= '<li>';
                $output .= "<strong>{$class}{$function}()</strong> ";
                $output .= "in <em>{$file}</em>";
                if ($line) {
                    $output .= " on line <strong>{$line}</strong>";
                }
                $output .= '</li>';
            }

            $output .= '</ol></div>';
        } else {
            $output = "Debug Backtrace:\n";

            foreach ($trace as $i => $t) {
                $file = isset($t['file']) ? $t['file'] : '[internal function]';
                $line = isset($t['line']) ? $t['line'] : '';
                $class = isset($t['class']) ? $t['class'] . $t['type'] : '';
                $function = $t['function'];

                $output .= "#" . $i . " {$class}{$function}() ";
                $output .= "in {$file}";
                if ($line) {
                    $output .= " on line {$line}";
                }
                $output .= "\n";
            }
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display formatted error information for any type of error or exception.
     *
     * @param mixed $error The error to display (Exception, Throwable, string, array, or any other type)
     * @param bool $die Whether to terminate execution (default: true)
     * @return void
     */
    public function exception($error, $die = true)
    {
        $output = '<div style="background-color:#f8f8f8; padding:15px; border-left:5px solid #dc3545; margin:15px 0; font-family:sans-serif;">';

        // Handle objects that implement Throwable (Exception, Error, etc.)
        if ($error instanceof \Throwable) {
            $output .= '<h2 style="color:#dc3545; margin-top:0;">' . get_class($error) . '</h2>';
            $output .= '<p style="font-size:16px; margin-bottom:15px;"><strong>Message:</strong> ' . $error->getMessage() . '</p>';
            $output .= '<p><strong>File:</strong> ' . $error->getFile() . '</p>';
            $output .= '<p><strong>Line:</strong> ' . $error->getLine() . '</p>';
            if ($error->getCode() != 0)
                $output .= '<p><strong>Code:</strong> ' . $error->getCode() . '</p>';
            $output .= '<h3>Stack Trace:</h3>';
            $output .= '<pre style="background-color:#f1f1f1; padding:10px; overflow:auto;">' . $error->getTraceAsString() . '</pre>';
        }
        // Handle PHP errors from error_get_last() or similar
        else if (is_array($error) && isset($error['type'])) {
            $errorTypes = [
                E_ERROR             => 'Fatal Error',
                E_WARNING           => 'Warning',
                E_PARSE             => 'Parse Error',
                E_NOTICE            => 'Notice',
                E_CORE_ERROR        => 'Core Error',
                E_CORE_WARNING      => 'Core Warning',
                E_COMPILE_ERROR     => 'Compile Error',
                E_COMPILE_WARNING   => 'Compile Warning',
                E_USER_ERROR        => 'User Error',
                E_USER_WARNING      => 'User Warning',
                E_USER_NOTICE       => 'User Notice',
                E_STRICT            => 'Strict Standards',
                E_RECOVERABLE_ERROR => 'Recoverable Error',
                E_DEPRECATED        => 'Deprecated',
                E_USER_DEPRECATED   => 'User Deprecated',
                E_ALL               => 'All Errors'
            ];

            $errorType = isset($errorTypes[$error['type']]) ? $errorTypes[$error['type']] : 'Unknown Error';

            $output .= '<h2 style="color:#dc3545; margin-top:0;">' . $errorType . '</h2>';
            $output .= '<p style="font-size:16px; margin-bottom:15px;"><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';

            if (isset($error['file'])) {
                $output .= '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
            }

            if (isset($error['line'])) {
                $output .= '<p><strong>Line:</strong> ' . $error['line'] . '</p>';
            }

            // No stack trace available for regular PHP errors
            $output .= '<p><em>No stack trace available for this type of error.</em></p>';
        }
        // Handle string error messages
        else if (is_string($error)) {
            $output .= '<h2 style="color:#dc3545; margin-top:0;">Error Message</h2>';
            $output .= '<p style="font-size:16px; margin-bottom:15px;">' . htmlspecialchars($error) . '</p>';

            // Get debug backtrace if available
            $trace = debug_backtrace();
            if (!empty($trace)) {
                $output .= '<h3>Debug Backtrace:</h3>';
                $output .= '<pre style="background-color:#f1f1f1; padding:10px; overflow:auto;">';
                foreach ($trace as $i => $step) {
                    $file = isset($step['file']) ? $step['file'] : '[internal function]';
                    $line = isset($step['line']) ? $step['line'] : '';
                    $function = isset($step['function']) ? $step['function'] : '';
                    $class = isset($step['class']) ? $step['class'] . (isset($step['type']) ? $step['type'] : '->') : '';

                    $output .= "#$i $file($line): $class$function()\n";
                }
                $output .= '</pre>';
            }
        }
        // Handle any other type of error object/value
        else {
            $output .= '<h2 style="color:#dc3545; margin-top:0;">Unspecified Error</h2>';
            $output .= '<p style="font-size:16px; margin-bottom:15px;"><strong>Error Data:</strong></p>';
            $output .= '<pre style="background-color:#f1f1f1; padding:10px; overflow:auto;">' . htmlspecialchars(print_r($error, true)) . '</pre>';

            // Get debug backtrace
            $trace = debug_backtrace();
            if (!empty($trace)) {
                $output .= '<h3>Debug Backtrace:</h3>';
                $output .= '<pre style="background-color:#f1f1f1; padding:10px; overflow:auto;">';
                foreach ($trace as $i => $step) {
                    $file = isset($step['file']) ? $step['file'] : '[internal function]';
                    $line = isset($step['line']) ? $step['line'] : '';
                    $function = isset($step['function']) ? $step['function'] : '';
                    $class = isset($step['class']) ? $step['class'] . (isset($step['type']) ? $step['type'] : '->') : '';

                    $output .= "#$i $file($line): $class$function()\n";
                }
                $output .= '</pre>';
            }
        }

        $output .= '</div>';

        echo $output;

        if ($die) {
            die();
        }
    }

    /**
     * Private storage for timers
     */
    private static $timers = [];

    /**
     * Start a timer for performance testing.
     *
     * @param string $name Timer name
     * @return float Start time
     */
    public function startTimer($name = 'default')
    {
        return self::$timers[$name] = microtime(true);
    }

    /**
     * End a timer and get the elapsed time.
     *
     * @param string $name Timer name
     * @param bool $echo Whether to echo the result (default: true)
     * @param bool $detailed Whether to use detailed formatting (default: false)
     * @return float|string Elapsed time or formatted time string
     */
    public function endTimer($name = 'default', $echo = true, $detailed = false)
    {
        if (!isset(self::$timers[$name])) {
            return 'Timer not started';
        }

        $elapsed = microtime(true) - self::$timers[$name];
        unset(self::$timers[$name]);

        if ($detailed) {
            $formatted = $this->formatExecutionTime($elapsed);
        } else {
            if ($elapsed < 0.001) {
                $formatted = round($elapsed * 1000000) . ' μs';
            } elseif ($elapsed < 1) {
                $formatted = round($elapsed * 1000, 3) . ' ms';
            } else {
                $formatted = round($elapsed, 3) . ' s';
            }
        }

        if ($echo) {
            echo '<div style="background-color:#e9f7ef; padding:5px 10px; border-left:3px solid #27ae60; margin:5px 0;">';
            echo "Timer '{$name}': {$formatted}";
            echo '</div>';
        }

        return $echo ? $formatted : $elapsed;
    }

    /**
     * Format and display SQL query with optional highlighting.
     *
     * @param string $sql SQL query to format
     * @param array $params Parameters for the query (optional)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted SQL
     */
    public function formatSql($sql, array $params = [], $echo = true)
    {
        $keywords = [
            'SELECT',
            'FROM',
            'WHERE',
            'JOIN',
            'LEFT JOIN',
            'RIGHT JOIN',
            'INNER JOIN',
            'ORDER BY',
            'GROUP BY',
            'HAVING',
            'LIMIT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'CREATE',
            'ALTER',
            'DROP',
            'AND',
            'OR',
            'AS',
            'ON',
            'IN',
            'NOT',
            'BETWEEN'
        ];

        // Parameter substitution for both positional (?) and named (:param, :0, :1, etc.)
        if (!empty($params)) {
            $paramIndex = 0; // Track index-based parameters

            // Replace positional placeholders (?)
            $sql = preg_replace_callback('/\?/', function () use (&$paramIndex, $params) {
                if (!isset($params[$paramIndex])) {
                    return '?'; // Leave as ? if param is missing
                }
                $value = $params[$paramIndex++];
                return is_string($value) ? "'" . addslashes($value) . "'" : $value;
            }, $sql);

            // Replace named placeholders (:param, :0, :1, etc.)
            foreach ($params as $key => $value) {
                if (is_string($key) || is_int($key)) {
                    $value = is_string($value) ? "'" . addslashes($value) . "'" : $value;
                    $sql = str_replace(':' . $key, $value, $sql);
                }
            }
        }

        // Highlight SQL keywords
        foreach ($keywords as $keyword) {
            $sql = preg_replace('/\b' . preg_quote($keyword, '/') . '\b/i', '<strong style="color:#0066cc;">' . strtoupper($keyword) . '</strong>', $sql);
        }

        // Format SQL with indentation for readability
        $sql = preg_replace('/(FROM|WHERE|JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|ORDER BY|GROUP BY|HAVING|LIMIT)/i', '<br>&nbsp;&nbsp;$1', $sql);

        // Wrap in a styled div
        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; font-family:monospace; overflow-x:auto;">' . $sql . '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Create a highlight around specific HTML to draw attention to it.
     *
     * @param string $content Content to highlight
     * @param string $color Highlight color (default: yellow)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Highlighted HTML
     */
    public function highlight($content, $color = 'yellow', $echo = true)
    {
        $output = '<span style="background-color:' . $color . '; padding:2px 4px;">' . $content . '</span>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display information about a variable.
     *
     * @param mixed $var Variable to inspect
     * @param string $varName Variable name (optional)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted variable information
     */
    public function inspect($var, $varName = null, $echo = true)
    {
        $type = gettype($var);
        $isObj = is_object($var);
        $isArr = is_array($var);

        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';

        if ($varName) {
            $output .= '<strong style="color:#333;">Variable:</strong> ' . $varName . '<br>';
        }

        $output .= '<strong style="color:#333;">Type:</strong> ' . $type . '<br>';

        if ($isObj) {
            $output .= '<strong style="color:#333;">Class:</strong> ' . get_class($var) . '<br>';
        }

        if ($isArr || $isObj) {
            $count = $isArr ? count($var) : count(get_object_vars($var));
            $output .= '<strong style="color:#333;">Count:</strong> ' . $count . '<br>';
        }

        $output .= '<strong style="color:#333;">Value:</strong><br><pre>';
        $output .= htmlspecialchars(print_r($var, true));
        $output .= '</pre>';
        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display a nice error message.
     *
     * @param string $message Error message
     * @param string $title Error title (optional)
     * @param bool $die Whether to terminate execution (default: false)
     * @return void
     */
    public function error($message, $title = 'Error', $die = false)
    {
        $output = '<div style="background-color:#fff8f8; padding:15px; border-left:5px solid #dc3545; margin:15px 0; font-family:sans-serif;">';
        $output .= '<h3 style="color:#dc3545; margin-top:0;">' . $title . '</h3>';
        $output .= '<p>' . $message . '</p>';
        $output .= '<p style="color:#666; font-size:12px;">File: ' . debug_backtrace()[0]['file'] . ' (Line: ' . debug_backtrace()[0]['line'] . ')</p>';
        $output .= '</div>';

        echo $output;

        if ($die) {
            die();
        }
    }

    /**
     * Display a warning message.
     *
     * @param string $message Warning message
     * @param string $title Warning title (optional)
     * @return void
     */
    public function warning($message, $title = 'Warning')
    {
        $output = '<div style="background-color:#fff3cd; padding:15px; border-left:5px solid #ffc107; margin:15px 0; font-family:sans-serif;">';
        $output .= '<h3 style="color:#856404; margin-top:0;">' . $title . '</h3>';
        $output .= '<p style="color:#856404;">' . $message . '</p>';
        $output .= '</div>';

        echo $output;
    }

    /**
     * Display a success message.
     *
     * @param string $message Success message
     * @param string $title Success title (optional)
     * @return void
     */
    public function success($message, $title = 'Success')
    {
        $output = '<div style="background-color:#f1f9f7; padding:15px; border-left:5px solid #28a745; margin:15px 0; font-family:sans-serif;">';
        $output .= '<h3 style="color:#28a745; margin-top:0;">' . $title . '</h3>';
        $output .= '<p>' . $message . '</p>';
        $output .= '</div>';

        echo $output;
    }

    /**
     * Log a variable to browser console using JavaScript.
     * 
     * @param mixed $data Data to log
     * @param string $label Label for the console log (optional)
     * @return void
     */
    public function console($data, $label = null)
    {
        $json = json_encode($data);

        if ($label) {
            echo "<script>console.log('$label:', $json);</script>";
        } else {
            echo "<script>console.log($json);</script>";
        }
    }

    /**
     * Create a simple table from array data.
     *
     * @param array $data Array data to display (array of arrays)
     * @param array $headers Table headers (optional)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string HTML table
     */
    public function table(array $data, array $headers = [], $echo = true)
    {
        if (empty($data)) {
            return '<p>No data to display</p>';
        }

        $output = '<table style="border-collapse: collapse; width: 100%; margin: 15px 0; font-family: sans-serif;">';

        // Add headers
        if (!empty($headers)) {
            $output .= '<thead><tr>';
            foreach ($headers as $header) {
                $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">' . $header . '</th>';
            }
            $output .= '</tr></thead>';
        } elseif (is_array($data[0])) {
            $output .= '<thead><tr>';
            foreach (array_keys($data[0]) as $header) {
                $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">' . $header . '</th>';
            }
            $output .= '</tr></thead>';
        }

        // Add data rows
        $output .= '<tbody>';
        foreach ($data as $row) {
            $output .= '<tr>';
            foreach ($row as $cell) {
                $cellContent = is_array($cell) || is_object($cell) ? '<pre>' . print_r($cell, true) . '</pre>' : $cell;
                $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $cellContent . '</td>';
            }
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display all defined constants.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted constants
     */
    public function constants($echo = true)
    {
        $constants = get_defined_constants(true);

        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
        $output .= '<h3>Defined Constants</h3>';

        foreach ($constants as $category => $consts) {
            $output .= '<details>';
            $output .= '<summary style="cursor:pointer; padding:5px; background-color:#f2f2f2;">' . $category . ' (' . count($consts) . ')</summary>';
            $output .= '<table style="width:100%; border-collapse:collapse; margin-top:5px;">';
            $output .= '<tr><th style="text-align:left; padding:5px; border:1px solid #ddd;">Name</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th></tr>';

            foreach ($consts as $name => $value) {
                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $name . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . (is_array($value) ? 'Array' : (is_object($value) ? 'Object' : htmlspecialchars((string)$value))) . '</td>';
                $output .= '</tr>';
            }

            $output .= '</table>';
            $output .= '</details>';
        }

        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display all loaded PHP extensions.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted extensions list
     */
    public function extensions($echo = true)
    {
        // Get and sort extensions
        $extensions = get_loaded_extensions();
        sort($extensions);

        // Define categories for extensions
        $categories = [
            'Database' => ['pdo_mysql', 'pdo_pgsql', 'mysqli', 'pgsql', 'sqlite3', 'mongodb', 'pdo_sqlite'],
            'Networking' => ['curl', 'sockets', 'openssl'],
            'Data Processing' => ['json', 'xml', 'simplexml', 'dom', 'mbstring'],
            'Compression' => ['zlib', 'bz2', 'zip'],
            'Graphics' => ['gd', 'imagick', 'exif'],
            'Security' => ['openssl', 'hash', 'sodium'],
            'Caching' => ['apcu', 'memcached', 'redis', 'opcache'],
            'Others' => []
        ];

        // Categorize extensions dynamically
        $categorizedExtensions = [];
        foreach ($extensions as $extension) {
            $found = false;
            foreach ($categories as $category => $list) {
                if (in_array(strtolower($extension), $list)) {
                    $categorizedExtensions[$category][] = $extension;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $categorizedExtensions['Others'][] = $extension;
            }
        }

        // Start output with a modern UI
        $output = '<div style="background:#ffffff; padding:20px; border-radius:10px; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1); font-family:sans-serif; max-width: 900px; margin: 20px auto;">';
        $output .= '<h2 style="text-align:center; color:#333;">🔌 PHP Extensions (' . count($extensions) . ')</h2>';
        $output .= '<input type="text" id="extensionSearch" onkeyup="filterExtensions()" placeholder="🔍 Search extensions..." style="width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:5px; font-size:16px;">';

        // JavaScript for live search filtering
        $output .= '<script>
            function filterExtensions() {
                var input = document.getElementById("extensionSearch").value.toLowerCase();
                var sections = document.getElementsByClassName("ext-category");
                for (var i = 0; i < sections.length; i++) {
                    var items = sections[i].getElementsByTagName("li");
                    var match = false;
                    for (var j = 0; j < items.length; j++) {
                        if (items[j].innerText.toLowerCase().indexOf(input) > -1) {
                            items[j].style.display = "";
                            match = true;
                        } else {
                            items[j].style.display = "none";
                        }
                    }
                    sections[i].style.display = match ? "" : "none";
                }
            }
        </script>';

        // Loop through categorized extensions and display them
        foreach ($categorizedExtensions as $category => $extList) {
            if (!empty($extList)) {
                $output .= '<h3 style="background:#007BFF; color:#fff; padding:10px; margin-top:20px; border-radius:5px;">' . $category . '</h3>';
                $output .= '<ul class="ext-category" style="list-style:none; padding:10px; columns:2;">';
                foreach ($extList as $ext) {
                    $version = phpversion($ext);
                    $output .= '<li style="padding:5px; border-bottom:1px solid #ddd;">🔹 <strong>' . $ext . '</strong> <small style="color:#888;">' . ($version ? '(' . $version . ')' : '') . '</small></li>';
                }
                $output .= '</ul>';
            }
        }

        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Display function call parameters.
     *
     * @param array $args Arguments to display (from func_get_args())
     * @param bool $backtrace Whether to include backtrace info (default: true)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted parameters
     */
    public function params(array $args, $backtrace = true, $echo = true)
    {
        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';

        if ($backtrace) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $caller = isset($trace[1]) ? $trace[1] : $trace[0];

            $function = isset($caller['function']) ? $caller['function'] : '';
            $class = isset($caller['class']) ? $caller['class'] . $caller['type'] : '';

            $output .= '<h3>Parameters for ' . $class . $function . '()</h3>';
        } else {
            $output .= '<h3>Function Parameters</h3>';
        }

        $output .= '<ol>';
        foreach ($args as $i => $arg) {
            $output .= '<li>';
            $output .= '<strong>Type:</strong> ' . gettype($arg);
            if (is_object($arg)) {
                $output .= ' (' . get_class($arg) . ')';
            }
            $output .= '<br>';
            $output .= '<strong>Value:</strong> <pre>' . print_r($arg, true) . '</pre>';
            $output .= '</li>';
        }
        $output .= '</ol>';
        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Record and display execution flow.
     *
     * @param string $message Message to record
     * @param bool $withTime Whether to include timestamp (default: true)
     * @return void
     */
    private static $flowLog = [];

    public function flow($message, $withTime = true)
    {
        // Only record time if $withTime is true
        $time = $withTime ? microtime(true) : 0;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $caller = $trace[0];

        $file = isset($caller['file']) ? basename($caller['file']) : '';
        $line = isset($caller['line']) ? $caller['line'] : '';

        $entry = [
            'time' => $time,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'withTime' => $withTime
        ];

        self::$flowLog[] = $entry;
    }

    /**
     * Display the execution flow log.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted flow log
     */
    public function showFlow($echo = true)
    {
        if (empty(self::$flowLog)) {
            return 'No flow log entries.';
        }

        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
        $output .= '<h3>Execution Flow Log</h3>';
        $output .= '<table style="width:100%; border-collapse:collapse;">';
        $output .= '<tr>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">#</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Time</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Message</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Location</th>';
        $output .= '</tr>';

        // Find the first entry with time
        $firstTimeEntry = null;
        foreach (self::$flowLog as $entry) {
            if (isset($entry['withTime']) && $entry['withTime'] && $entry['time'] > 0) {
                $firstTimeEntry = $entry;
                break;
            }
        }

        $startTime = $firstTimeEntry ? $firstTimeEntry['time'] : 0;
        $prevTime = $startTime;

        foreach (self::$flowLog as $i => $entry) {
            // Check if time tracking is enabled for this entry
            $hasTime = isset($entry['withTime']) ? $entry['withTime'] : true; // Default to true for backward compatibility

            // Time difference calculations
            $diff = ($hasTime && $i > 0 && $prevTime > 0) ? ($entry['time'] - $prevTime) * 1000 : 0;
            $totalDiff = ($hasTime && $startTime > 0) ? ($entry['time'] - $startTime) * 1000 : 0;
            $diffFormatted = ($hasTime && $i > 0 && $prevTime > 0) ? '+' . number_format($diff, 2) . ' ms' : '';

            $output .= '<tr>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . ($i + 1) . '</td>';

            // Time cell formatting
            if ($hasTime && $entry['time'] > 0) {
                $milliseconds = sprintf('%03d', round(($entry['time'] - floor($entry['time'])) * 1000));
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' .
                    date('H:i:s', (int)$entry['time']) . '.' . $milliseconds .
                    ' ' . $diffFormatted .
                    ' (Total: ' . number_format($totalDiff, 2) . ' ms)</td>';
                // Update prevTime only if this entry has time
                $prevTime = $entry['time'];
            } else {
                $output .= '<td style="padding:5px; border:1px solid #ddd;">No timestamp</td>';
            }

            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . htmlspecialchars($entry['message']) . '</td>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $entry['file'] . ':' . $entry['line'] . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';
        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Clear the execution flow log.
     *
     * @return void
     */
    public function clearFlow()
    {
        self::$flowLog = [];
    }

    /**
     * Display detailed server, request, and system information.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @param bool $includeEnv Whether to include environment variables (default: false)
     * @return string Formatted server information
     */
    public function serverInfo($echo = true, $includeEnv = false)
    {
        // Function to add table rows
        $addRow = function ($label, $value, $important = false) {
            $style = $important ? 'background-color:#fff8e1;' : '';
            return "<tr style='{$style}'>
                <td style='padding:8px; border-bottom:1px solid #ddd; font-weight:bold; width:35%; background:#f4f4f4;'>"
                . htmlspecialchars($label) .
                "</td>
                <td style='padding:8px; border-bottom:1px solid #ddd; color:#333;'>"
                . htmlspecialchars($value) .
                "</td>
            </tr>";
        };

        // Function to test if a PHP extension is loaded
        $checkExtension = function ($name) {
            return extension_loaded($name) ? 'Enabled' : 'Disabled';
        };

        // Start output with better styling
        $output = '<div style="background-color:#ffffff; padding:20px; border:1px solid #ccc; border-radius:10px; font-family:sans-serif; width: 90%; max-width: 900px; margin: 20px auto; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);">';
        $output .= '<h2 style="text-align:center; color:#444; margin-bottom:15px;">🌐 Server & System Diagnostics</h2>';

        // Add a timestamp
        $output .= '<p style="text-align:center; color:#666; margin-bottom:20px;">Generated on: ' . date('Y-m-d H:i:s T') . '</p>';

        // Add navigation menu
        $output .= '<div style="margin-bottom:20px; text-align:center;">';
        $output .= '<a href="#server-info" style="display:inline-block; margin:5px; padding:8px 15px; background:#007BFF; color:#fff; text-decoration:none; border-radius:5px;">🛠️ Server</a>';
        $output .= '<a href="#os-hardware" style="display:inline-block; margin:5px; padding:8px 15px; background:#28A745; color:#fff; text-decoration:none; border-radius:5px;">💻 OS & Hardware</a>';
        $output .= '<a href="#php-info" style="display:inline-block; margin:5px; padding:8px 15px; background:#6610f2; color:#fff; text-decoration:none; border-radius:5px;">🐘 PHP</a>';
        $output .= '<a href="#performance" style="display:inline-block; margin:5px; padding:8px 15px; background:#FFC107; color:#fff; text-decoration:none; border-radius:5px;">🚀 Performance</a>';
        $output .= '<a href="#storage" style="display:inline-block; margin:5px; padding:8px 15px; background:#DC3545; color:#fff; text-decoration:none; border-radius:5px;">💾 Storage</a>';
        $output .= '<a href="#request" style="display:inline-block; margin:5px; padding:8px 15px; background:#17A2B8; color:#fff; text-decoration:none; border-radius:5px;">🌐 Request</a>';
        $output .= '<a href="#security" style="display:inline-block; margin:5px; padding:8px 15px; background:#E83E8C; color:#fff; text-decoration:none; border-radius:5px;">🛡️ Security</a>';
        $output .= '</div>';

        $output .= '<table style="width:100%; border-collapse:collapse; background:#fff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05); border-radius:5px; overflow:hidden;">';

        // 🖥️ Server Info
        $output .= '<tr><th colspan="2" id="server-info" style="background:#007BFF; color:#fff; padding:10px; text-align:left;">🛠️ Server Information</th></tr>';
        $output .= $addRow('Server Name', $_SERVER['SERVER_NAME'] ?? 'N/A');
        $output .= $addRow('Server IP', $_SERVER['SERVER_ADDR'] ?? 'N/A');
        $output .= $addRow('Server Port', $_SERVER['SERVER_PORT'] ?? 'N/A');
        $output .= $addRow('Server Software', $_SERVER['SERVER_SOFTWARE'] ?? 'N/A');
        $output .= $addRow('Server Protocol', $_SERVER['SERVER_PROTOCOL'] ?? 'N/A');
        $output .= $addRow('Document Root', $_SERVER['DOCUMENT_ROOT'] ?? 'N/A');
        $output .= $addRow('Server Admin', $_SERVER['SERVER_ADMIN'] ?? 'N/A');
        $output .= $addRow('Server Time', date('Y-m-d H:i:s T'));
        $output .= $addRow('Server Timezone', date_default_timezone_get());
        $output .= $addRow('Server Uptime', $this->getServerUptime());

        // 💻 OS & Hardware
        $output .= '<tr><th colspan="2" id="os-hardware" style="background:#28A745; color:#fff; padding:10px; text-align:left;">💻 OS & Hardware</th></tr>';
        $output .= $addRow('Operating System', php_uname('s') . ' ' . php_uname('r'));
        $output .= $addRow('Hostname', php_uname('n'));
        $output .= $addRow('CPU Architecture', php_uname('m'));
        $output .= $addRow('Kernel Version', php_uname('v'));

        // Get CPU info
        if (function_exists('shell_exec')) {
            if (PHP_OS_FAMILY === 'Windows') {
                $cpuInfo = getenv("PROCESSOR_IDENTIFIER") ?: 'Unknown';
                $cpuCores = getenv("NUMBER_OF_PROCESSORS") ?: 'Unknown';
            } else {
                $cpuModel = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | head -1 | cut -d':' -f2")) ?: 'Unknown';
                $cpuCores = trim(shell_exec('nproc')) ?: 'Unknown';
                $cpuInfo = $cpuModel;
            }
            $output .= $addRow('CPU Info', $cpuInfo);
            $output .= $addRow('CPU Cores', $cpuCores);
        }

        // Get RAM info
        if (function_exists('shell_exec') && PHP_OS_FAMILY !== 'Windows') {
            $ramTotal = trim(shell_exec("grep 'MemTotal' /proc/meminfo | awk '{print $2}'"));
            $ramFree = trim(shell_exec("grep 'MemFree' /proc/meminfo | awk '{print $2}'"));
            $ramAvailable = trim(shell_exec("grep 'MemAvailable' /proc/meminfo | awk '{print $2}'"));

            if ($ramTotal) {
                $output .= $addRow('Total RAM', number_format($ramTotal / 1024, 2) . ' MB');
                $output .= $addRow('Free RAM', number_format($ramFree / 1024, 2) . ' MB');
                $output .= $addRow('Available RAM', number_format($ramAvailable / 1024, 2) . ' MB');

                // Calculate RAM usage percentage
                $ramUsed = $ramTotal - $ramAvailable;
                $ramUsagePercent = round(($ramUsed / $ramTotal) * 100, 2);

                // Highlight high memory usage
                $important = $ramUsagePercent > 80;
                $output .= $addRow('RAM Usage', $ramUsagePercent . '%', $important);
            }
        }

        // Load averages (Linux/Unix only)
        if (function_exists('sys_getloadavg') && PHP_OS_FAMILY !== 'Windows') {
            $loadAverage = sys_getloadavg();
            if ($loadAverage) {
                $loadAverageString = $loadAverage[0] . ' (1 min), ' . $loadAverage[1] . ' (5 min), ' . $loadAverage[2] . ' (15 min)';
                $important = $loadAverage[0] > $cpuCores;  // Highlight if load exceeds CPU cores
                $output .= $addRow('Load Average', $loadAverageString, $important);
            }
        }

        // 🐘 PHP Information
        $output .= '<tr><th colspan="2" id="php-info" style="background:#6610f2; color:#fff; padding:10px; text-align:left;">🐘 PHP Information</th></tr>';
        $output .= $addRow('PHP Version', phpversion());
        $output .= $addRow('PHP SAPI', php_sapi_name());
        $output .= $addRow('Zend Version', zend_version());
        $output .= $addRow('OPcache', $checkExtension('opcache'));
        $output .= $addRow(
            'Database Extensions',
            'MySQL: ' . $checkExtension('mysqli') .
                ', PostgreSQL: ' . $checkExtension('pgsql') .
                ', SQLite: ' . $checkExtension('sqlite3')
        );
        $output .= $addRow(
            'Caching Extensions',
            'APCu: ' . $checkExtension('apcu') .
                ', Memcached: ' . $checkExtension('memcached') .
                ', Redis: ' . $checkExtension('redis')
        );
        $output .= $addRow(
            'Image Processing',
            'GD: ' . $checkExtension('gd') .
                ', ImageMagick: ' . $checkExtension('imagick')
        );
        $output .= $addRow(
            'Compression',
            'Zip: ' . $checkExtension('zip') .
                ', Zlib: ' . $checkExtension('zlib')
        );

        // 🚀 Performance & Limits
        $output .= '<tr><th colspan="2" id="performance" style="background:#FFC107; color:#fff; padding:10px; text-align:left;">🚀 Performance & Limits</th></tr>';
        $memLimit = ini_get('memory_limit');
        $output .= $addRow('Memory Limit', $memLimit, (intval($memLimit) < 128));
        $exeTime = ini_get('max_execution_time');
        $output .= $addRow('Max Execution Time', $exeTime . ' seconds', ($exeTime < 30));
        $output .= $addRow('Max Input Vars', ini_get('max_input_vars'));
        $output .= $addRow('Max Upload Filesize', ini_get('upload_max_filesize'));
        $output .= $addRow('Post Max Size', ini_get('post_max_size'));
        $output .= $addRow('Max Input Time', ini_get('max_input_time') . ' seconds');
        $output .= $addRow('Default Socket Timeout', ini_get('default_socket_timeout') . ' seconds');
        $output .= $addRow('Output Buffering', ini_get('output_buffering'));
        $output .= $addRow('Display Errors', ini_get('display_errors'));
        $output .= $addRow('Error Reporting Level', $this->getErrorReportingLevel());

        // 📤 Storage
        $output .= '<tr><th colspan="2" id="storage" style="background:#DC3545; color:#fff; padding:10px; text-align:left;">💾 Storage</th></tr>';
        if (function_exists('disk_total_space')) {
            $totalDisk = disk_total_space("/");
            $freeDisk = disk_free_space("/");
            $usedDisk = $totalDisk - $freeDisk;
            $diskUsagePercent = round(($usedDisk / $totalDisk) * 100, 2);

            $output .= $addRow('Total Disk Space', number_format($totalDisk / 1073741824, 2) . ' GB');
            $output .= $addRow('Disk Space Used', number_format($usedDisk / 1073741824, 2) . ' GB');
            $output .= $addRow('Free Disk Space', number_format($freeDisk / 1073741824, 2) . ' GB');
            $output .= $addRow('Disk Usage', $diskUsagePercent . '%', ($diskUsagePercent > 90));
        }

        $output .= $addRow('Temporary Directory', sys_get_temp_dir());
        $output .= $addRow('Upload Temp Directory', ini_get('upload_tmp_dir') ?: 'System Default');

        // Check if temp directory is writable
        $tempDir = sys_get_temp_dir();
        $tempDirWritable = is_writable($tempDir) ? 'Yes' : 'No';
        $output .= $addRow('Temp Directory Writable', $tempDirWritable, ($tempDirWritable === 'No'));

        // 🌐 Request Information
        $output .= '<tr><th colspan="2" id="request" style="background:#17A2B8; color:#fff; padding:10px; text-align:left;">🌐 Request Information</th></tr>';
        $output .= $addRow('Request Time', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()));
        $output .= $addRow('Request Method', $_SERVER['REQUEST_METHOD'] ?? 'N/A');
        $output .= $addRow('Request URI', $_SERVER['REQUEST_URI'] ?? 'N/A');
        $output .= $addRow('Query String', $_SERVER['QUERY_STRING'] ?? 'N/A');
        $output .= $addRow('HTTP Host', $_SERVER['HTTP_HOST'] ?? 'N/A');
        $output .= $addRow('Remote Address', $_SERVER['REMOTE_ADDR'] ?? 'N/A');
        $output .= $addRow('Remote Port', $_SERVER['REMOTE_PORT'] ?? 'N/A');
        $output .= $addRow('User Agent', $_SERVER['HTTP_USER_AGENT'] ?? 'N/A');
        $output .= $addRow('Accept Language', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'N/A');
        $output .= $addRow('Accept Encoding', $_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'N/A');
        $output .= $addRow('Referrer', $_SERVER['HTTP_REFERER'] ?? 'N/A');
        $output .= $addRow('HTTPS Enabled', isset($_SERVER['HTTPS']) ? 'Yes' : 'No');

        // Get current memory usage
        $memUsage = memory_get_usage();
        $memPeakUsage = memory_get_peak_usage();
        $output .= $addRow('Current Memory Usage', number_format($memUsage / 1048576, 2) . ' MB');
        $output .= $addRow('Peak Memory Usage', number_format($memPeakUsage / 1048576, 2) . ' MB');

        // 🛡️ Security & Sessions
        $output .= '<tr><th colspan="2" id="security" style="background:#E83E8C; color:#fff; padding:10px; text-align:left;">🛡️ Security & Sessions</th></tr>';
        if (function_exists('session_status')) {
            $sessionStatus = session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive';
            $output .= $addRow('Session Status', $sessionStatus);
        }
        $output .= $addRow('Session Save Path', ini_get('session.save_path') ?: 'Default');
        $output .= $addRow('Session Save Handler', ini_get('session.save_handler'));
        $output .= $addRow('Session Cookie Lifetime', ini_get('session.cookie_lifetime') . ' seconds');
        $output .= $addRow('Session GC Maxlifetime', ini_get('session.gc_maxlifetime') . ' seconds');

        $output .= $addRow('Open Basedir', ini_get('open_basedir') ?: 'Not Restricted');
        $output .= $addRow('Disabled Functions', ini_get('disable_functions') ?: 'None');
        $output .= $addRow('Allow URL Fopen', ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled');
        $output .= $addRow('Expose PHP', ini_get('expose_php') ? 'Enabled' : 'Disabled', ini_get('expose_php'));

        // Environment variables (optional)
        if ($includeEnv) {
            $output .= '<tr><th colspan="2" id="environment" style="background:#6f42c1; color:#fff; padding:10px; text-align:left;">🔄 Environment Variables</th></tr>';
            foreach ($_ENV as $key => $value) {
                // Filter out sensitive information
                if (!$this->isSensitiveEnvVar($key)) {
                    $output .= $addRow($key, $value);
                }
            }
        }

        $output .= '</table>';

        // Add export options and footer
        $output .= '<div style="text-align:center; margin-top:20px;">';
        $output .= '<button onclick="window.print();" style="padding:8px 15px; background:#444; color:#fff; border:none; border-radius:5px; cursor:pointer; margin:5px;">🖨️ Print</button>';
        $output .= '<button onclick="copyToClipboard();" style="padding:8px 15px; background:#444; color:#fff; border:none; border-radius:5px; cursor:pointer; margin:5px;">📋 Copy</button>';
        $output .= '</div>';

        // JavaScript for export functionality
        $output .= '<script>
            function copyToClipboard() {
                const el = document.createElement("textarea");
                el.value = document.documentElement.innerText;
                document.body.appendChild(el);
                el.select();
                document.execCommand("copy");
                document.body.removeChild(el);
                alert("Server information copied to clipboard!");
            }
        </script>';

        $output .= '<p style="text-align:center; margin-top:20px; color:#666; font-size:12px;">Generated by ServerInfo Tool • ' . date('Y-m-d H:i:s') . '</p>';
        $output .= '</div>';

        // Echo or return
        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Get the current error reporting level as a readable string
     *
     * @return string Error reporting level description
     */
    private function getErrorReportingLevel()
    {
        $level = error_reporting();
        $levels = [];

        if ($level & E_ERROR) $levels[] = 'E_ERROR';
        if ($level & E_WARNING) $levels[] = 'E_WARNING';
        if ($level & E_PARSE) $levels[] = 'E_PARSE';
        if ($level & E_NOTICE) $levels[] = 'E_NOTICE';
        if ($level & E_CORE_ERROR) $levels[] = 'E_CORE_ERROR';
        if ($level & E_CORE_WARNING) $levels[] = 'E_CORE_WARNING';
        if ($level & E_COMPILE_ERROR) $levels[] = 'E_COMPILE_ERROR';
        if ($level & E_COMPILE_WARNING) $levels[] = 'E_COMPILE_WARNING';
        if ($level & E_USER_ERROR) $levels[] = 'E_USER_ERROR';
        if ($level & E_USER_WARNING) $levels[] = 'E_USER_WARNING';
        if ($level & E_USER_NOTICE) $levels[] = 'E_USER_NOTICE';
        if ($level & E_STRICT) $levels[] = 'E_STRICT';
        if ($level & E_RECOVERABLE_ERROR) $levels[] = 'E_RECOVERABLE_ERROR';
        if ($level & E_DEPRECATED) $levels[] = 'E_DEPRECATED';
        if ($level & E_USER_DEPRECATED) $levels[] = 'E_USER_DEPRECATED';
        if ($level & E_ALL) $levels[] = 'E_ALL';

        if (empty($levels)) {
            return "None ($level)";
        }

        return implode(', ', $levels) . " ($level)";
    }

    /**
     * Check if an environment variable name might contain sensitive information
     * 
     * @param string $varName The name of the environment variable
     * @return bool True if potentially sensitive
     */
    private function isSensitiveEnvVar($varName)
    {
        $sensitivePatterns = [
            '/pass/i',
            '/key/i',
            '/secret/i',
            '/token/i',
            '/auth/i',
            '/pwd/i',
            '/credential/i',
            '/secure/i',
            '/private/i',
            '/access/i'
        ];

        foreach ($sensitivePatterns as $pattern) {
            if (preg_match($pattern, $varName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display all defined variables in current scope.
     *
     * @param bool $globals Whether to include global variables (default: false)
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Formatted variables
     */
    public function variables($globals = false, $echo = true)
    {
        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
        $output .= '<h3>Defined Variables</h3>';

        // Get variables in the calling scope
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        $frame = $trace[0];
        $vars = isset($frame['args'][0]) ? $frame['args'][0] : [];

        // Add local variables
        $output .= '<details open>';
        $output .= '<summary style="cursor:pointer; padding:5px; background-color:#f2f2f2;">Local Variables</summary>';

        if (empty($vars)) {
            $output .= '<p>No local variables available.</p>';
        } else {
            $output .= '<table style="width:100%; border-collapse:collapse; margin-top:5px;">';
            $output .= '<tr>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd; width:20%;">Name</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd; width:15%;">Type</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th>';
            $output .= '</tr>';

            foreach ($vars as $name => $value) {
                $type = gettype($value);
                $isObj = is_object($value);

                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . $name . '</strong></td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $type . ($isObj ? ' (' . get_class($value) . ')' : '') . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><pre>' . print_r($value, true) . '</pre></td>';
                $output .= '</tr>';
            }

            $output .= '</table>';
        }

        $output .= '</details>';

        // Add global variables
        if ($globals) {
            $output .= '<details>';
            $output .= '<summary style="cursor:pointer; padding:5px; background-color:#f2f2f2; margin-top:10px;">Global Variables</summary>';
            $output .= '<table style="width:100%; border-collapse:collapse; margin-top:5px;">';
            $output .= '<tr>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd; width:20%;">Name</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd; width:15%;">Type</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th>';
            $output .= '</tr>';

            foreach ($GLOBALS as $name => $value) {
                // Skip superglobals
                if (in_array($name, ['_GET', '_POST', '_COOKIE', '_FILES', '_SERVER', '_ENV', '_REQUEST', '_SESSION', 'GLOBALS'])) {
                    continue;
                }

                $type = gettype($value);
                $isObj = is_object($value);

                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . $name . '</strong></td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $type . ($isObj ? ' (' . get_class($value) . ')' : '') . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><pre>' . print_r($value, true) . '</pre></td>';
                $output .= '</tr>';
            }

            $output .= '</table>';
            $output .= '</details>';
        }

        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Debug a specific section of code with timing and context.
     *
     * @param callable $callback Function to debug
     * @param array $args Arguments to pass to the function
     * @param bool $echo Whether to echo the result (default: true)
     * @return mixed Result of the callback function
     */
    public function debugSection(callable $callback, array $args = [], $echo = true)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $functionName = '';

        // Try to get function info
        $reflection = is_array($callback) ? new \ReflectionMethod($callback[0], $callback[1]) : new \ReflectionFunction($callback);

        if (is_array($callback) && isset($callback[0], $callback[1]) && is_object($callback[0])) {
            $functionName = get_class($callback[0]) . '::' . $callback[1];
        } else if ($reflection instanceof \ReflectionFunctionAbstract) {
            $functionName = $reflection->getName();
        }

        $file = $reflection->getFileName();
        $line = $reflection->getStartLine();

        // Execute the callback
        try {
            $result = call_user_func_array($callback, $args);
            $success = true;
        } catch (\Exception $e) {
            $result = null;
            $error = $e;
            $success = false;
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = $endTime - $startTime;
        $memoryUsage = $endMemory - $startMemory;

        if ($echo) {
            $output = '<div style="background-color:' . ($success ? '#f1f9f7' : '#fff8f8') . '; padding:15px; border-left:5px solid ' . ($success ? '#28a745' : '#dc3545') . '; margin:15px 0; font-family:sans-serif;">';
            $output .= '<h3 style="margin-top:0;">Debug Section: ' . htmlspecialchars($functionName) . '</h3>';
            $output .= '<p><strong>File:</strong> ' . $file . ' (line ' . $line . ')</p>';
            $output .= '<p><strong>Execution Time:</strong> ' . $this->formatExecutionTime($executionTime) . '</p>';
            $output .= '<p><strong>Memory Usage:</strong> ' . $this->formatBytes($memoryUsage) . '</p>';

            if (!empty($args)) {
                $output .= '<h4>Arguments:</h4>';
                $output .= '<ol>';
                foreach ($args as $arg) {
                    $output .= '<li><pre>' . print_r($arg, true) . '</pre></li>';
                }
                $output .= '</ol>';
            }

            if ($success) {
                $output .= '<h4>Result:</h4>';
                $output .= '<pre>' . print_r($result, true) . '</pre>';
            } else {
                $output .= '<h4>Error:</h4>';
                $output .= '<p style="color:#dc3545;"><strong>' . get_class($error) . ':</strong> ' . $error->getMessage() . '</p>';
                $output .= '<p>In ' . $error->getFile() . ' on line ' . $error->getLine() . '</p>';
            }

            $output .= '</div>';

            echo $output;
        }

        if (!$success && isset($error)) {
            throw $error;
        }

        return $result;
    }

    /**
     * Generate a visual representation of call stack.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @param int $limit Limit the number of stack frames (default: 0, unlimited)
     * @return string HTML representation of call stack
     */
    public function callStack($echo = true, $limit = 0)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit ?: 0);
        array_shift($trace); // Remove call to this method

        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
        $output .= '<h3>Call Stack</h3>';
        $output .= '<table style="width:100%; border-collapse:collapse;">';
        $output .= '<tr>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Level</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Function</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Location</th>';
        $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Arguments</th>';
        $output .= '</tr>';

        foreach ($trace as $i => $t) {
            $file = isset($t['file']) ? $t['file'] : '[internal function]';
            $line = isset($t['line']) ? $t['line'] : '';
            $class = isset($t['class']) ? $t['class'] . $t['type'] : '';
            $function = $t['function'];

            $args = isset($t['args']) ? $t['args'] : [];
            $argsOutput = '';

            if (!empty($args)) {
                $argsOutput = '<ol>';
                foreach ($args as $arg) {
                    $argType = gettype($arg);
                    $argValue = '';

                    if (is_string($arg)) {
                        $argValue = '"' . (strlen($arg) > 50 ? substr($arg, 0, 50) . '...' : $arg) . '"';
                    } elseif (is_array($arg)) {
                        $argValue = 'Array(' . count($arg) . ')';
                    } elseif (is_object($arg)) {
                        $argValue = get_class($arg) . ' Object';
                    } elseif (is_resource($arg)) {
                        $argValue = 'Resource: ' . get_resource_type($arg);
                    } elseif (is_null($arg)) {
                        $argValue = 'NULL';
                    } elseif (is_bool($arg)) {
                        $argValue = $arg ? 'TRUE' : 'FALSE';
                    } else {
                        $argValue = (string) $arg;
                    }

                    $argsOutput .= '<li>' . $argType . ': ' . $argValue . '</li>';
                }
                $argsOutput .= '</ol>';
            }

            $output .= '<tr>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $i . '</td>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . $class . $function . '()</strong></td>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $file . ($line ? ' : ' . $line : '') . '</td>';
            $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $argsOutput . '</td>';
            $output .= '</tr>';
        }

        $output .= '</table>';
        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Analyze a variable with detailed type information and structure.
     *
     * @param mixed $var Variable to analyze
     * @param string $name Variable name
     * @param bool $echo Whether to echo the result (default: true)
     * @return string Detailed analysis
     */
    public function analyze($var, $name = 'Variable', $echo = true)
    {
        $type = gettype($var);
        $output = '<div style="background-color:#f8f8f8; padding:10px; border:1px solid #ddd; margin:10px 0; font-family:monospace;">';
        $output .= '<h3>Variable Analysis: ' . htmlspecialchars($name) . '</h3>';
        $output .= '<p><strong>Type:</strong> ' . $type . '</p>';

        switch ($type) {
            case 'object':
                $output .= $this->_analyzeObject($var);
                break;

            case 'array':
                $output .= $this->_analyzeArray($var);
                break;

            case 'string':
                $output .= $this->_analyzeString($var);
                break;

            case 'integer':
            case 'double':
                $output .= $this->_analyzeNumber($var);
                break;

            case 'boolean':
                $output .= '<p><strong>Value:</strong> ' . ($var ? 'TRUE' : 'FALSE') . '</p>';
                break;

            case 'NULL':
                $output .= '<p><strong>Value:</strong> NULL</p>';
                break;

            case 'resource':
                $output .= '<p><strong>Resource Type:</strong> ' . get_resource_type($var) . '</p>';
                break;
        }

        $output .= '</div>';

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Analyze an object with detailed information.
     *
     * @param object $obj Object to analyze
     * @return string HTML output with analysis
     */
    private function _analyzeObject($obj)
    {
        $className = get_class($obj);
        $reflection = new \ReflectionClass($obj);

        $output = '<p><strong>Class:</strong> ' . $className . '</p>';

        // Parent class
        if ($parent = $reflection->getParentClass()) {
            $output .= '<p><strong>Parent Class:</strong> ' . $parent->getName() . '</p>';
        }

        // Interfaces
        $interfaces = $reflection->getInterfaceNames();
        if (!empty($interfaces)) {
            $output .= '<p><strong>Implements:</strong> ' . implode(', ', $interfaces) . '</p>';
        }

        // Properties
        $properties = $reflection->getProperties();
        if (!empty($properties)) {
            $output .= '<h4>Properties</h4>';
            $output .= '<table style="width:100%; border-collapse:collapse;">';
            $output .= '<tr>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Visibility</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Name</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Type</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th>';
            $output .= '</tr>';

            foreach ($properties as $prop) {
                $prop->setAccessible(true);
                $visibility = $prop->isPrivate() ? 'private' : ($prop->isProtected() ? 'protected' : 'public');
                $static = $prop->isStatic() ? ' static' : '';

                $value = $prop->isInitialized($obj) ? $prop->getValue($obj) : 'uninitialized';
                $valueType = is_object($value) ? get_class($value) : gettype($value);

                if (is_array($value)) {
                    $valueOutput = 'Array(' . count($value) . ')';
                } elseif (is_object($value)) {
                    $valueOutput = 'Object of class ' . get_class($value);
                } elseif (is_resource($value)) {
                    $valueOutput = 'Resource: ' . get_resource_type($value);
                } elseif (is_string($value) && strlen($value) > 100) {
                    $valueOutput = htmlspecialchars(substr($value, 0, 100)) . '...';
                } elseif (is_string($value)) {
                    $valueOutput = htmlspecialchars($value);
                } elseif (is_bool($value)) {
                    $valueOutput = $value ? 'TRUE' : 'FALSE';
                } elseif (is_null($value)) {
                    $valueOutput = 'NULL';
                } else {
                    $valueOutput = (string) $value;
                }

                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $visibility . $static . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . $prop->getName() . '</strong></td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $valueType . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $valueOutput . '</td>';
                $output .= '</tr>';
            }

            $output .= '</table>';
        }

        // Methods
        $methods = $reflection->getMethods();
        if (!empty($methods)) {
            $output .= '<h4>Methods</h4>';
            $output .= '<table style="width:100%; border-collapse:collapse;">';
            $output .= '<tr>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Visibility</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Name</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Parameters</th>';
            $output .= '</tr>';

            foreach ($methods as $method) {
                $visibility = $method->isPrivate() ? 'private' : ($method->isProtected() ? 'protected' : 'public');
                $static = $method->isStatic() ? ' static' : '';
                $abstract = $method->isAbstract() ? ' abstract' : '';
                $final = $method->isFinal() ? ' final' : '';

                $params = [];
                foreach ($method->getParameters() as $param) {
                    $paramStr = '';
                    if ($param->hasType()) {
                        $paramStr .= $param->getType() . ' ';
                    }
                    $paramStr .= '$' . $param->getName();
                    if ($param->isOptional()) {
                        $paramStr .= ' = ';
                        if ($param->isDefaultValueAvailable()) {
                            $default = $param->getDefaultValue();
                            if (is_array($default)) {
                                $paramStr .= 'array()';
                            } elseif (is_null($default)) {
                                $paramStr .= 'null';
                            } elseif (is_string($default)) {
                                $paramStr .= '"' . $default . '"';
                            } elseif (is_bool($default)) {
                                $paramStr .= $default ? 'true' : 'false';
                            } else {
                                $paramStr .= $default;
                            }
                        }
                    }
                    $params[] = $paramStr;
                }

                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $visibility . $static . $abstract . $final . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . $method->getName() . '</strong></td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . implode(', ', $params) . '</td>';
                $output .= '</tr>';
            }

            $output .= '</table>';
        }

        return $output;
    }

    /**
     * Analyze an array with detailed information.
     *
     * @param array $arr Array to analyze
     * @return string HTML output with analysis
     */
    private function _analyzeArray($arr)
    {
        $count = count($arr);
        $output = '<p><strong>Count:</strong> ' . $count . '</p>';

        if ($count > 0) {
            $output .= '<h4>Elements</h4>';
            $output .= '<table style="width:100%; border-collapse:collapse;">';
            $output .= '<tr>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Key</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Type</th>';
            $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Value</th>';
            $output .= '</tr>';

            $i = 0;
            foreach ($arr as $key => $value) {
                $valueType = is_object($value) ? get_class($value) : gettype($value);

                if (is_array($value)) {
                    $valueOutput = 'Array(' . count($value) . ')';
                } elseif (is_object($value)) {
                    $valueOutput = 'Object of class ' . get_class($value);
                } elseif (is_resource($value)) {
                    $valueOutput = 'Resource: ' . get_resource_type($value);
                } elseif (is_string($value) && strlen($value) > 100) {
                    $valueOutput = htmlspecialchars(substr($value, 0, 100)) . '...';
                } elseif (is_string($value)) {
                    $valueOutput = htmlspecialchars($value);
                } elseif (is_bool($value)) {
                    $valueOutput = $value ? 'TRUE' : 'FALSE';
                } elseif (is_null($value)) {
                    $valueOutput = 'NULL';
                } else {
                    $valueOutput = (string) $value;
                }

                $output .= '<tr>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;"><strong>' . htmlspecialchars((string)$key) . '</strong></td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $valueType . '</td>';
                $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $valueOutput . '</td>';
                $output .= '</tr>';

                // Limit to 100 items to prevent huge outputs
                if (++$i >= 100 && count($arr) > 100) {
                    $output .= '<tr><td colspan="3" style="padding:5px; border:1px solid #ddd;"><em>... and ' .
                        (count($arr) - 100) . ' more elements</em></td></tr>';
                    break;
                }
            }

            $output .= '</table>';
        }

        // Array structure info
        $isAssoc = false;
        $isList = true;
        $i = 0;

        foreach ($arr as $key => $val) {
            if ($key !== $i++) {
                $isList = false;
            }

            if (!is_int($key)) {
                $isAssoc = true;
            }
        }

        $output .= '<p><strong>Structure:</strong> ';
        if ($isAssoc) {
            $output .= 'Associative Array';
        } elseif ($isList) {
            $output .= 'Sequential List';
        } else {
            $output .= 'Mixed Key Array';
        }
        $output .= '</p>';

        return $output;
    }

    /**
     * Analyze a string with detailed information.
     *
     * @param string $str String to analyze
     * @return string HTML output with analysis
     */
    private function _analyzeString($str)
    {
        $length = strlen($str);
        $output = '<p><strong>Length:</strong> ' . $length . ' characters</p>';

        // Show string preview
        if ($length > 0) {
            $preview = $length > 1000 ? htmlspecialchars(substr($str, 0, 1000)) . '...' : htmlspecialchars($str);
            $output .= '<p><strong>Value:</strong> <pre style="background-color:#f0f0f0; padding:5px; overflow:auto; max-height:300px;">' . $preview . '</pre></p>';
        }

        // Character frequency analysis
        if ($length > 0 && $length <= 10000) { // Don't analyze huge strings
            $charCount = count_chars($str, 1);
            ksort($charCount);

            if (!empty($charCount)) {
                $output .= '<p><strong>Character Analysis:</strong></p>';
                $output .= '<table style="width:100%; border-collapse:collapse;">';
                $output .= '<tr>';
                $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Character</th>';
                $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">ASCII</th>';
                $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Count</th>';
                $output .= '<th style="text-align:left; padding:5px; border:1px solid #ddd;">Percentage</th>';
                $output .= '</tr>';

                // Show top 20 most frequent characters
                $i = 0;
                arsort($charCount);
                foreach ($charCount as $char => $count) {
                    $char = chr($char);
                    $displayChar = ($char === ' ') ? '[space]' : ($char === "\n" ? '[newline]' : ($char === "\t" ? '[tab]' : ($char === "\r" ? '[return]' : htmlspecialchars($char))));

                    $output .= '<tr>';
                    $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $displayChar . '</td>';
                    $output .= '<td style="padding:5px; border:1px solid #ddd;">' . ord($char) . '</td>';
                    $output .= '<td style="padding:5px; border:1px solid #ddd;">' . $count . '</td>';
                    $output .= '<td style="padding:5px; border:1px solid #ddd;">' .
                        round(($count / $length) * 100, 2) . '%</td>';
                    $output .= '</tr>';

                    if (++$i >= 20) break;
                }

                $output .= '</table>';
            }
        }

        // Encoding detection
        $encoding = mb_detect_encoding($str, "UTF-8, ASCII, ISO-8859-1, Windows-1252", true);
        $output .= '<p><strong>Encoding:</strong> ' . ($encoding ?: 'Unknown') . '</p>';

        // Check if it's a serialized string
        if (@unserialize($str) !== false) {
            $output .= '<p><strong>Format:</strong> Serialized PHP data</p>';
        }

        // Check if it's JSON
        json_decode($str);
        if (json_last_error() === JSON_ERROR_NONE) {
            $output .= '<p><strong>Format:</strong> JSON</p>';
        }

        // Check if it's base64 encoded
        $decoded = base64_decode($str, true);
        if ($decoded !== false && base64_encode($decoded) === $str) {
            $output .= '<p><strong>Format:</strong> Base64 encoded</p>';
        }

        return $output;
    }

    /**
     * Analyze a number with detailed information.
     *
     * @param int|float $num Number to analyze
     * @return string HTML output with analysis
     */
    private function _analyzeNumber($num)
    {
        $type = is_int($num) ? 'Integer' : 'Float';
        $output = '<p><strong>Value:</strong> ' . $num . '</p>';

        if (is_int($num)) {
            // Integer analysis
            $output .= '<p><strong>Binary:</strong> ' . decbin($num) . '</p>';
            $output .= '<p><strong>Octal:</strong> ' . decoct($num) . '</p>';
            $output .= '<p><strong>Hexadecimal:</strong> 0x' . dechex($num) . '</p>';

            // Check for common flags or bitmasks
            $powers = [];
            for ($i = 0; $i < 32; $i++) {
                $power = pow(2, $i);
                if (($num & $power) === $power) {
                    $powers[] = $power . ' (2<sup>' . $i . '</sup>)';
                }
            }

            if (!empty($powers)) {
                $output .= '<p><strong>Contains bits:</strong> ' . implode(', ', $powers) . '</p>';
            }
        } else {
            // Float analysis
            $parts = explode('.', (string)$num);
            $output .= '<p><strong>Integer part:</strong> ' . $parts[0] . '</p>';
            $output .= '<p><strong>Fractional part:</strong> ' . (isset($parts[1]) ? $parts[1] : '0') . '</p>';

            // Scientific notation
            $output .= '<p><strong>Scientific notation:</strong> ' . sprintf('%e', $num) . '</p>';

            // Precision analysis
            $precision = 0;
            $strVal = (string)$num;
            if (strpos($strVal, '.') !== false) {
                $parts = explode('.', $strVal);
                $precision = strlen($parts[1]);
            }
            $output .= '<p><strong>Precision:</strong> ' . $precision . ' decimal places</p>';
        }

        return $output;
    }

    /**
     * Display comprehensive information about PHP environment with improved UI/UX.
     *
     * @param bool $echo Whether to echo the result (default: true)
     * @param bool $showDetailedExtensions Show detailed information about extensions (default: true)
     * @return string Formatted PHP info
     */
    public function phpInfo($echo = true, $showDetailedExtensions = true)
    {
        // Function to add table rows
        $addRow = function ($icon, $label, $value = null, $secure = true, $important = false) {
            // Check if $value is a Closure, Array, or Object
            if ($value instanceof \Closure) {
                $value = 'Closure';
            } elseif (is_array($value) || is_object($value)) {
                ob_start(); // Start output buffering
                print_r($value);
                $value = '<pre>' . htmlspecialchars(ob_get_clean(), ENT_QUOTES | ENT_SUBSTITUTE) . '</pre>';
            } elseif ($secure && !empty($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE);
                $label = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE);
            }

            $style = $important ? 'background-color:#fff8e1;' : '';

            return '<tr style="' . $style . '">
                <td style="padding:10px; border-bottom:1px solid #ddd; background-color:#f4f4f4; font-weight:bold; width: 35%;">
                    ' . $icon . ' ' . $label . '
                </td>
                <td style="padding:10px; border-bottom:1px solid #ddd; color:#333;">' . $value . '</td>
            </tr>';
        };

        // Function to check if extension is loaded
        $checkExtension = function ($ext) {
            return extension_loaded($ext) ?
                '<span style="color:#28A745">✅ Enabled</span>' :
                '<span style="color:#DC3545">❌ Disabled</span>';
        };

        // Function to create progress bar
        $createProgressBar = function ($percent, $label = '') {
            $color = '#4CAF50'; // Default green
            if ($percent > 75) $color = '#FFC107'; // Yellow for > 75%
            if ($percent > 90) $color = '#DC3545'; // Red for > 90%

            return '<div style="position:relative; width:100%; height:20px; background-color:#e0e0e0; border-radius:10px; overflow:hidden;">
                    <div style="position:absolute; width:' . $percent . '%; height:20px; background-color:' . $color . '; border-radius:10px;"></div>
                    <div style="position:absolute; width:100%; text-align:center; line-height:20px; color:#000; font-weight:bold;">' .
                $percent . '% ' . $label .
                '</div>
                </div>';
        };

        // Start output
        $output = '<div style="background-color:#ffffff; padding:20px; border:1px solid #ccc; border-radius:10px; font-family:sans-serif; width: 90%; max-width: 900px; margin: 20px auto; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);">';
        $output .= '<h2 style="text-align:center; color:#444; margin-bottom:5px;">🐘 PHP Environment Diagnostics</h2>';
        $output .= '<p style="text-align:center; color:#666; margin-bottom:20px;">Generated on: ' . date('Y-m-d H:i:s T') . '</p>';

        // Add navigation menu
        $output .= '<div style="margin-bottom:20px; text-align:center; display:flex; flex-wrap:wrap; justify-content:center; gap:5px;">';
        $output .= '<a href="#php-info" style="padding:8px 12px; background:#007BFF; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">🛠️ PHP Core</a>';
        $output .= '<a href="#os-hardware" style="padding:8px 12px; background:#28A745; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">💻 OS & Hardware</a>';
        $output .= '<a href="#performance" style="padding:8px 12px; background:#FFC107; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">🚀 Performance</a>';
        $output .= '<a href="#storage" style="padding:8px 12px; background:#DC3545; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">💾 Storage</a>';
        $output .= '<a href="#file-uploads" style="padding:8px 12px; background:#17A2B8; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">📤 File Uploads</a>';
        $output .= '<a href="#time-settings" style="padding:8px 12px; background:#6F42C1; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">⏰ Time</a>';
        $output .= '<a href="#database" style="padding:8px 12px; background:#E83E8C; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">🗄️ Database</a>';
        $output .= '<a href="#security" style="padding:8px 12px; background:#FD7E14; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">🔒 Security</a>';
        $output .= '<a href="#extensions" style="padding:8px 12px; background:#343A40; color:#fff; text-decoration:none; border-radius:5px; display:inline-block;">🧩 Extensions</a>';
        $output .= '</div>';

        $output .= '<div style="max-height:600px; overflow-y:auto; padding-right:10px;">';
        $output .= '<table style="width:100%; border-collapse:collapse; background:#fff; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05); border-radius:5px; overflow:hidden;">';

        // General PHP & Server Details
        $output .= '<tr><th colspan="2" id="php-info" style="background:#007BFF; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">🛠️ PHP Core Information</th></tr>';
        $output .= $addRow('🐘', 'PHP Version', phpversion() . ' (' . php_uname('s') . ')');
        $output .= $addRow('⚡', 'PHP SAPI (Server API)', php_sapi_name());
        $output .= $addRow('🏗️', 'Zend Engine Version', zend_version());
        $output .= $addRow('🖥️', 'Server Software', $_SERVER['SERVER_SOFTWARE'] ?? 'Not available');
        $output .= $addRow('🌍', 'Hostname', gethostname());
        $output .= $addRow('📂', 'Document Root', $_SERVER['DOCUMENT_ROOT'] ?? 'Not available');
        $output .= $addRow('🔄', 'PHP Script Owner', function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'N/A (Windows or posix unavailable)');
        $output .= $addRow('💼', 'PHP.ini Location', php_ini_loaded_file() ?: 'Unknown');
        $output .= $addRow('📝', 'Additional .ini Files', function () {
            $files = php_ini_scanned_files();
            return $files ? str_replace(',', '<br>', $files) : 'None';
        });
        $output .= $addRow('🔄', 'PHP Self Path', $_SERVER['PHP_SELF'] ?? 'N/A');

        // OS & Hardware Details
        $output .= '<tr><th colspan="2" id="os-hardware" style="background:#28A745; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">💻 Operating System & Hardware</th></tr>';
        $output .= $addRow('🖥️', 'Operating System', php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('v'));
        $output .= $addRow('💻', 'Architecture', php_uname('m'));
        $output .= $addRow('🏢', 'Server Machine Name', php_uname('n'));

        // Get CPU info
        if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
            if (PHP_OS_FAMILY === 'Windows') {
                $cpuInfo = getenv("PROCESSOR_IDENTIFIER") ?: 'Unknown';
                $output .= $addRow('🔌', 'CPU Info', $cpuInfo);
            } else {
                $cpuModel = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | head -1 | cut -d':' -f2")) ?: 'Unknown';
                $cpuCores = trim(shell_exec('nproc')) ?: 'Unknown';
                $output .= $addRow('🔌', 'CPU Model', $cpuModel);
                $output .= $addRow('🧮', 'CPU Cores', $cpuCores);
            }
        }

        if (function_exists('sys_getloadavg')) {
            $loadavg = sys_getloadavg();
            $output .= $addRow(
                '📊',
                'Server Load (1m, 5m, 15m)',
                '<span title="1 minute">' . round($loadavg[0], 2) . '</span> / ' .
                    '<span title="5 minutes">' . round($loadavg[1], 2) . '</span> / ' .
                    '<span title="15 minutes">' . round($loadavg[2], 2) . '</span>',
                false,
                ($loadavg[0] > 2)
            );
        }

        // Memory & Performance
        $output .= '<tr><th colspan="2" id="performance" style="background:#FFC107; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">🚀 Performance & Resources</th></tr>';

        $memLimit = ini_get('memory_limit');
        $output .= $addRow('🧠', 'Memory Limit', $memLimit, true, ($memLimit == '-1' || intval($memLimit) < 64));

        $execTime = ini_get('max_execution_time');
        $output .= $addRow('⏱️', 'Max Execution Time', $execTime . ' seconds', true, ($execTime == '0' || $execTime < 30));

        $currMemUsage = memory_get_usage(true);
        $peakMemUsage = memory_get_peak_usage(true);
        $memLimitBytes = $this->returnBytes($memLimit);

        if ($memLimitBytes > 0) {
            $usagePercent = round(($currMemUsage / $memLimitBytes) * 100, 2);
            $peakPercent = round(($peakMemUsage / $memLimitBytes) * 100, 2);

            $output .= $addRow(
                '📈',
                'Current Memory Usage',
                number_format($currMemUsage / 1048576, 2) . ' MB / ' . $this->formatBytes($memLimitBytes) .
                    '<br>' . $createProgressBar($usagePercent, 'used'),
                false
            );

            $output .= $addRow(
                '📊',
                'Peak Memory Usage',
                number_format($peakMemUsage / 1048576, 2) . ' MB / ' . $this->formatBytes($memLimitBytes) .
                    '<br>' . $createProgressBar($peakPercent, 'used'),
                false,
                ($peakPercent > 75)
            );
        } else {
            $output .= $addRow('📈', 'Current Memory Usage', number_format($currMemUsage / 1048576, 2) . ' MB');
            $output .= $addRow('📊', 'Peak Memory Usage', number_format($peakMemUsage / 1048576, 2) . ' MB');
        }

        $output .= $addRow('🧮', 'Max Input Vars', ini_get('max_input_vars'), true, (ini_get('max_input_vars') < 1000));
        $output .= $addRow('📝', 'Input Time', ini_get('max_input_time') . ' seconds');
        $output .= $addRow('📑', 'Default Socket Timeout', ini_get('default_socket_timeout') . ' seconds');

        // OPcache status
        if (function_exists('opcache_get_status') && !in_array('opcache_get_status', explode(',', ini_get('disable_functions')))) {
            try {
                $opcache = opcache_get_status(false);
                $output .= $addRow('⚡', 'OPcache Status', $opcache['opcache_enabled'] ? '<span style="color:green">Enabled</span>' : '<span style="color:red">Disabled</span>', false);

                if ($opcache['opcache_enabled']) {
                    $memoryUsed = $opcache['memory_usage']['used_memory'];
                    $memoryTotal = $opcache['memory_usage']['total_memory'];
                    $memPercent = round(($memoryUsed / $memoryTotal) * 100, 2);

                    $output .= $addRow(
                        '🧠',
                        'OPcache Memory Usage',
                        $this->formatBytes($memoryUsed) . ' / ' . $this->formatBytes($memoryTotal) .
                            '<br>' . $createProgressBar($memPercent),
                        false
                    );

                    $hitRate = 0;
                    if ($opcache['opcache_statistics']['hits'] > 0 || $opcache['opcache_statistics']['misses'] > 0) {
                        $hitRate = round(($opcache['opcache_statistics']['hits'] /
                            ($opcache['opcache_statistics']['hits'] + $opcache['opcache_statistics']['misses'])) * 100, 2);
                    }

                    $output .= $addRow(
                        '🎯',
                        'OPcache Hit Rate',
                        $hitRate . '%' .
                            '<br>' . $createProgressBar($hitRate),
                        false
                    );
                }
            } catch (\Exception $e) {
                $output .= $addRow('⚡', 'OPcache Status', 'Error retrieving OPcache status');
            }
        } else {
            $output .= $addRow('⚡', 'OPcache', $checkExtension('opcache'), false);
        }

        // Disk Space Info
        $output .= '<tr><th colspan="2" id="storage" style="background:#DC3545; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">💾 Storage</th></tr>';
        if (function_exists('disk_total_space') && function_exists('disk_free_space')) {
            $totalDisk = @disk_total_space(".");
            $freeDisk = @disk_free_space(".");

            if ($totalDisk && $freeDisk) {
                $usedDisk = $totalDisk - $freeDisk;
                $usedPercent = round(($usedDisk / $totalDisk) * 100, 2);

                $output .= $addRow('💽', 'Disk Total', $this->formatBytes($totalDisk));
                $output .= $addRow(
                    '📀',
                    'Disk Space Used',
                    $this->formatBytes($usedDisk) . ' (' . $usedPercent . '%)' .
                        '<br>' . $createProgressBar($usedPercent),
                    false,
                    ($usedPercent > 90)
                );
                $output .= $addRow('🆓', 'Free Disk Space', $this->formatBytes($freeDisk));
            }
        }

        $output .= $addRow('📁', 'Temporary Directory', sys_get_temp_dir());
        $output .= $addRow(
            '✍️',
            'Temp Directory Writable',
            is_writable(sys_get_temp_dir()) ?
                '<span style="color:green">Yes</span>' :
                '<span style="color:red">No</span>',
            false,
            !is_writable(sys_get_temp_dir())
        );

        // File Upload & Post Settings
        $output .= '<tr><th colspan="2" id="file-uploads" style="background:#17A2B8; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">📤 File Uploads</th></tr>';
        $output .= $addRow('📤', 'File Uploads Enabled', ini_get('file_uploads') ? 'Yes' : 'No', true, !ini_get('file_uploads'));
        $output .= $addRow('📎', 'Upload Max Filesize', ini_get('upload_max_filesize'));
        $output .= $addRow('📬', 'Post Max Size', ini_get('post_max_size'));
        $output .= $addRow('🔢', 'Max File Uploads', ini_get('max_file_uploads'));
        $output .= $addRow('📂', 'Upload Temp Directory', ini_get('upload_tmp_dir') ?: sys_get_temp_dir());

        // Check if upload directory is writable
        $uploadDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
        $output .= $addRow(
            '✍️',
            'Upload Directory Writable',
            is_writable($uploadDir) ?
                '<span style="color:green">Yes</span>' :
                '<span style="color:red">No</span>',
            false,
            !is_writable($uploadDir)
        );

        // Timezone & Date
        $output .= '<tr><th colspan="2" id="time-settings" style="background:#6F42C1; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">⏰ Time Settings</th></tr>';
        $output .= $addRow('🕰️', 'Default Timezone', date_default_timezone_get());
        $output .= $addRow('📅', 'Current Server Time', date('Y-m-d H:i:s T'));
        $output .= $addRow('⏱️', 'Server Uptime', $this->getServerUptime());

        // Get timezone offset
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $now = new \DateTime('now', $timezone);
        $offset = $timezone->getOffset($now) / 3600;
        $output .= $addRow('🌐', 'Timezone Offset', 'UTC' . ($offset >= 0 ? '+' : '') . $offset);

        // Database Info
        $output .= '<tr><th colspan="2" id="database" style="background:#E83E8C; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">🗄️ Database Support</th></tr>';

        // MySQL/MariaDB
        $output .= $addRow('🐬', 'MySQL/MariaDB', $checkExtension('mysqli'), false);
        if (extension_loaded('mysqli')) {
            $output .= $addRow('ℹ️', 'MySQL Client Info', mysqli_get_client_info());
        }

        // PostgreSQL
        $output .= $addRow('🐘', 'PostgreSQL', $checkExtension('pgsql'), false);
        if (extension_loaded('pgsql')) {
            $output .= $addRow('ℹ️', 'PostgreSQL Client Info', 'N/A');
        }

        // SQLite
        $output .= $addRow('🔄', 'SQLite', $checkExtension('sqlite3'), false);
        if (extension_loaded('sqlite3')) {
            $sqliteVer = \SQLite3::version();
            $output .= $addRow('ℹ️', 'SQLite Version', $sqliteVer['versionString']);
        }

        // PDO drivers
        if (extension_loaded('pdo')) {
            $pdoDrivers = \PDO::getAvailableDrivers();
            $output .= $addRow('🔌', 'PDO Drivers', implode(', ', $pdoDrivers));
        }

        // Security Settings
        $output .= '<tr><th colspan="2" id="security" style="background:#FD7E14; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">🔒 Security</th></tr>';

        // Check if expose_php is enabled
        $exposePhp = ini_get('expose_php');
        $output .= $addRow(
            '🔍',
            'PHP Signature Exposed',
            $exposePhp ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>',
            false,
            $exposePhp
        );

        // Session settings
        if (function_exists('session_status')) {
            $sessionStatuses = [
                PHP_SESSION_DISABLED => 'Disabled',
                PHP_SESSION_NONE => 'None (Not started)',
                PHP_SESSION_ACTIVE => 'Active'
            ];
            $statusText = $sessionStatuses[session_status()] ?? 'Unknown';
            $output .= $addRow('🔑', 'Session Status', $statusText);
        }

        $output .= $addRow('📝', 'Session Save Path', ini_get('session.save_path') ?: 'Default');
        $output .= $addRow('🔄', 'Session Handler', ini_get('session.save_handler'));
        $output .= $addRow('⏳', 'Session Lifetime', ini_get('session.gc_maxlifetime') . ' seconds');
        $output .= $addRow('🍪', 'Cookie Secure', ini_get('session.cookie_secure') ? 'Yes' : 'No');
        $output .= $addRow('🧱', 'Cookie HttpOnly', ini_get('session.cookie_httponly') ? 'Yes' : 'No');
        $output .= $addRow('🛡️', 'Cookie SameSite', ini_get('session.cookie_samesite') ?: 'Not Set');

        // Error display settings
        $output .= $addRow(
            '⚠️',
            'Display Errors',
            ini_get('display_errors') ?
                '<span style="color:red">On</span>' : '<span style="color:green">Off</span>',
            false,
            ini_get('display_errors') && PHP_SAPI !== 'cli'
        );

        $output .= $addRow('📝', 'Error Log', ini_get('error_log') ?: 'Not configured');

        // Open basedir
        $openBasedir = ini_get('open_basedir');
        $output .= $addRow('📁', 'Open Basedir', $openBasedir ? $openBasedir : 'Not Restricted');

        // Disabled functions
        $disabledFunctions = ini_get('disable_functions');
        $output .= $addRow(
            '⛔',
            'Disabled Functions',
            $disabledFunctions ?
                '<details><summary>Show ' . count(explode(',', $disabledFunctions)) . ' functions</summary><div style="max-height:200px;overflow-y:auto;">' .
                str_replace(',', ', ', $disabledFunctions) . '</div></details>' :
                'None',
            false
        );

        // Check security features
        $output .= $addRow(
            '🌐',
            'allow_url_fopen',
            ini_get('allow_url_fopen') ?
                '<span style="color:red">Enabled</span>' : '<span style="color:green">Disabled</span>',
            false,
            ini_get('allow_url_fopen')
        );

        $output .= $addRow(
            '🔄',
            'allow_url_include',
            ini_get('allow_url_include') ?
                '<span style="color:red">Enabled</span>' : '<span style="color:green">Disabled</span>',
            false,
            ini_get('allow_url_include')
        );

        // PHP Extensions
        $output .= '<tr><th colspan="2" id="extensions" style="background:#343A40; color:#fff; padding:10px; text-align:left; position:sticky; top:0;">🧩 PHP Extensions</th></tr>';

        $extensions = get_loaded_extensions();
        sort($extensions); // Sort alphabetically

        // Count extensions
        $output .= $addRow('🔢', 'Total Extensions', count($extensions));

        // Key extension categories
        $keyExtensions = [
            'Core' => ['Core', 'SPL', 'standard', 'pcre', 'date'],
            'Web Development' => ['curl', 'fileinfo', 'ftp', 'soap', 'xml', 'simplexml', 'dom', 'libxml', 'xmlreader', 'xmlwriter'],
            'Images/Graphics' => ['gd', 'exif', 'imagick'],
            'Compression' => ['zlib', 'zip', 'bz2'],
            'Cryptography' => ['openssl', 'sodium', 'hash', 'mcrypt'],
            'Database' => ['mysqli', 'pdo', 'pdo_mysql', 'pgsql', 'pdo_pgsql', 'sqlite3', 'pdo_sqlite'],
            'Caching' => ['opcache', 'apcu', 'memcached', 'redis'],
            'Performance' => ['opcache', 'intl']
        ];

        foreach ($keyExtensions as $category => $exts) {
            $extStatus = [];
            foreach ($exts as $ext) {
                $status = extension_loaded($ext);
                $version = phpversion($ext);
                $extStatus[] = ($status ?
                    '<span style="color:green">✅ ' . $ext . ($version ? ' (v' . $version . ')' : '') . '</span>' :
                    '<span style="color:#999">❌ ' . $ext . '</span>');
            }

            $output .= $addRow('📦', $category . ' Extensions', implode('<br>', $extStatus), false);
        }

        if ($showDetailedExtensions) {
            $extensionsList = '<div style="columns:3; column-gap:20px; margin-top:10px;">';

            foreach ($extensions as $extension) {
                $version = phpversion($extension);
                $extensionsList .= '<div style="break-inside:avoid-column; padding:5px 0;">
                <span style="color:green;">✅</span> <strong>' . htmlspecialchars($extension) . '</strong>' .
                    ($version ? ' <span style="color:#666;">(v' . $version . ')</span>' : '') .
                    '</div>';
            }

            $extensionsList .= '</div>';

            $output .= $addRow(
                '🔍',
                'All Loaded Extensions',
                '<details>
                <summary style="cursor:pointer; color:#007bff;">Show All Extensions (' . count($extensions) . ')</summary>
                ' . $extensionsList . '
            </details>',
                false
            );
        }

        $output .= '</table>';
        $output .= '</div>'; // End scrollable area

        // Add export options
        $output .= '<div style="text-align:center; margin-top:20px;">';
        $output .= '<button onclick="window.print();" style="padding:8px 15px; background:#444; color:#fff; border:none; border-radius:5px; cursor:pointer; margin:5px;">🖨️ Print</button>';
        $output .= '<button onclick="copyToClipboard();" style="padding:8px 15px; background:#444; color:#fff; border:none; border-radius:5px; cursor:pointer; margin:5px;">📋 Copy</button>';
        $output .= '</div>';

        // JavaScript for export functionality
        $output .= '<script>
            function copyToClipboard() {
                const el = document.createElement("textarea");
                el.value = document.documentElement.innerText;
                document.body.appendChild(el);
                el.select();
                document.execCommand("copy");
                document.body.removeChild(el);
                alert("PHP information copied to clipboard!");
            }
        </script>';

        $output .= '<p style="text-align:center; margin-top:20px; color:#666; font-size:12px;">Generated by PHP Info Diagnostics • ' . date('Y-m-d H:i:s') . '</p>';
        $output .= '</div>';

        // Echo or return the output
        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Convert shorthand memory notation to bytes
     * 
     * @param string $size Memory size string (like 128M, 1G)
     * @return int Size in bytes
     */
    private function returnBytes($size)
    {
        if (empty($size)) return 0;

        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int)$size;

        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes Number of bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    public function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0 || empty($bytes)) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0); // Ensure non-negative bytes
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); // Calculate power of 1024
        $pow = min($pow, count($units) - 1); // Limit to valid unit index

        $bytes /= (1 << (10 * $pow)); // Divide by appropriate factor

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get server uptime (Linux/Unix systems only)
     *
     * @return string Formatted uptime or 'Unknown'
     */
    private function getServerUptime()
    {
        if (PHP_OS_FAMILY === 'Windows' || !function_exists('shell_exec')) {
            return 'Unknown (Windows or shell_exec disabled)';
        }

        $uptime = @shell_exec('uptime -p');

        if ($uptime) {
            return trim($uptime);
        }

        // Alternative approach if 'uptime -p' doesn't work
        $uptime = @shell_exec('cat /proc/uptime');
        if ($uptime) {
            $uptime = explode(' ', $uptime)[0];
            $uptime = (float)$uptime;

            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            $seconds = $uptime % 60;

            $result = '';
            if ($days > 0) $result .= $days . ' days, ';
            if ($hours > 0) $result .= $hours . ' hours, ';
            if ($minutes > 0) $result .= $minutes . ' minutes, ';
            $result .= round($seconds) . ' seconds';

            return $result;
        }

        return 'Unknown';
    }

    /**
     * Checks if server is running on a Linux system
     * 
     * @return bool True if running on Linux
     */
    private function isLinux()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN');
    }

    /**
     * Checks if a PHP extension is available and enabled
     * 
     * @param string $extension The extension name to check
     * @return bool True if extension is loaded
     */
    private function isExtensionLoaded($extension)
    {
        return extension_loaded($extension);
    }

    /**
     * Get detailed version information about PHP and relevant extensions
     * 
     * @return array Version information
     */
    public function getDetailedVersionInfo()
    {
        $info = [
            'php' => [
                'version' => phpversion(),
                'zend_version' => zend_version(),
                'sapi' => php_sapi_name()
            ]
        ];

        // Add key extensions and their versions
        $keyExtensions = ['mysqli', 'pdo', 'curl', 'gd', 'opcache', 'openssl', 'intl', 'mbstring'];
        $info['extensions'] = [];

        foreach ($keyExtensions as $ext) {
            if (extension_loaded($ext)) {
                $info['extensions'][$ext] = phpversion($ext) ?: 'Enabled';
            } else {
                $info['extensions'][$ext] = false;
            }
        }

        return $info;
    }

    /**
     * Get system recommendations based on current configuration
     * 
     * @return array List of recommendations with severity
     */
    public function getRecommendations()
    {
        $recommendations = [];

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            $recommendations[] = [
                'severity' => 'critical',
                'message' => 'PHP version is outdated. Consider upgrading to PHP 7.4 or later.'
            ];
        } else if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $recommendations[] = [
                'severity' => 'warning',
                'message' => 'Consider upgrading to PHP 7.4 or later for improved performance and security.'
            ];
        }

        // Check memory limit
        $memLimit = ini_get('memory_limit');
        $memLimitBytes = $this->returnBytes($memLimit);
        if ($memLimitBytes < 128 * 1024 * 1024) {
            $recommendations[] = [
                'severity' => 'warning',
                'message' => 'Memory limit is too low. Consider increasing to at least 128M.'
            ];
        }

        // Check max execution time
        $maxExecTime = ini_get('max_execution_time');
        if ($maxExecTime < 30 && $maxExecTime != 0) {
            $recommendations[] = [
                'severity' => 'info',
                'message' => 'Max execution time is low. Consider increasing to at least 30 seconds.'
            ];
        }

        // Check for OPcache
        if (!extension_loaded('opcache')) {
            $recommendations[] = [
                'severity' => 'warning',
                'message' => 'OPcache is not enabled. Enabling OPcache can significantly improve PHP performance.'
            ];
        }

        // Check for display_errors in production
        if (ini_get('display_errors') && PHP_SAPI !== 'cli') {
            $recommendations[] = [
                'severity' => 'critical',
                'message' => 'display_errors is enabled. This should be disabled in production environments.'
            ];
        }

        // Check for allow_url_include
        if (ini_get('allow_url_include')) {
            $recommendations[] = [
                'severity' => 'critical',
                'message' => 'allow_url_include is enabled. This poses a security risk and should be disabled.'
            ];
        }

        // Check session security settings
        if (!ini_get('session.cookie_secure')) {
            $recommendations[] = [
                'severity' => 'warning',
                'message' => 'session.cookie_secure is not enabled. Enable for HTTPS sites to improve security.'
            ];
        }

        if (!ini_get('session.cookie_httponly')) {
            $recommendations[] = [
                'severity' => 'warning',
                'message' => 'session.cookie_httponly is not enabled. Enable to improve security against XSS attacks.'
            ];
        }

        return $recommendations;
    }

    /**
     * Generate a simple JSON output of PHP information
     * 
     * @return string JSON encoded PHP info
     */
    public function getJsonInfo()
    {
        $info = [
            'php' => [
                'version' => phpversion(),
                'zend_version' => zend_version(),
                'sapi' => php_sapi_name(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'display_errors' => ini_get('display_errors') ? 'On' : 'Off',
                'error_reporting' => ini_get('error_reporting')
            ],
            'server' => [
                'os' => PHP_OS,
                'hostname' => gethostname(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
            ],
            'extensions' => get_loaded_extensions(),
            'recommendations' => $this->getRecommendations()
        ];

        return json_encode($info, JSON_PRETTY_PRINT);
    }
}
