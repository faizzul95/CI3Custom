<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Request
{
    protected $CI;
    protected static $data;
    protected static $files;
    protected $secureInput = true;

    /**
     * Constructor
     * 
     * Initializes the Request object and loads necessary CodeIgniter resources
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('security');
        self::$data = $this->sanitizeInput(array_merge(
            $this->CI->input->get(NULL, TRUE),
            $this->CI->input->post(NULL, TRUE),
            $this->CI->input->method() === 'put' ? $this->sanitizeInput($this->CI->input->input_stream()) : []
        ));
        self::$files = $this->processUploadedFiles($_FILES);
    }

    /**
     * Disable input sanitization
     * 
     * @return $this
     */
    public function unsafe()
    {
        $this->secureInput = false;
        self::$data = $this->sanitizeInput(array_merge(
            $this->CI->input->get(NULL, FALSE),
            $this->CI->input->post(NULL, FALSE),
            $this->CI->input->method() === 'put' ? $this->CI->input->input_stream() : []
        ));
        return $this;
    }

    /**
     * Sanitize input data recursively
     * 
     * @param mixed $input
     * @return mixed
     */
    private function sanitizeInput($input)
    {
        if (!$this->secureInput) {
            return $input;
        }

        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitizeInput($value);
            }
        } else {
            $input = $this->CI->security->xss_clean($input);
        }

        return $input;
    }

    /**
     * Process uploaded files
     * 
     * @param array $files
     * @return array
     */
    private function processUploadedFiles($files)
    {
        $processed = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $processed[$key] = [];
                for ($i = 0; $i < count($file['name']); $i++) {
                    $processed[$key][] = [
                        'name' => $this->sanitizeInput($file['name'][$i]),
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i]
                    ];
                }
            } else {
                $processed[$key] = [
                    'name' => $this->sanitizeInput($file['name']),
                    'type' => $file['type'],
                    'tmp_name' => $file['tmp_name'],
                    'error' => $file['error'],
                    'size' => $file['size']
                ];
            }
        }
        return $processed;
    }

    /**
     * Get all input data
     * 
     * @return array All input data
     */
    public function all()
    {
        return array_merge(self::$data, ['files' => self::$files]);
    }

    /**
     * Get a specific input item
     * 
     * @param string $key The key of the input item
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The value of the input item or the default value
     */
    public function input($key, $default = null)
    {
        // If no segments provided, just check if the data contains the key directly
        if (strpos($key, '.') === false) {
            return self::$data[$key] ?? $default;
        }

        // Split the key by dots to handle nested arrays
        $segments = explode('.', $key);
        $data = self::$data;

        foreach ($segments as $segment) {
            // If the segment is an asterisk, replace it with a regex wildcard
            if ($segment === '*') {
                $wildcardData = [];
                foreach ($data as $item) {
                    if (is_array($item)) {
                        $wildcardData = array_merge($wildcardData, $item);
                    }
                }
                $data = $wildcardData;
            } else if (isset($data[$segment])) {
                $data = $data[$segment]; // If the segment exists, go deeper
            } else {
                // If the segment doesn't exist, return the default value
                return $default;
            }
        }

        return $data ?? $default;
    }

    /**
     * Get information about uploaded files
     * 
     * @param string|null $key
     * @return array|null
     */
    public function files($key = null)
    {
        if ($key === null) {
            return self::$files;
        }

        return self::$files[$key] ?? null;
    }

    /**
     * Check if an input item exists
     * 
     * @param string $key The key of the input item
     * @return bool True if the item exists, false otherwise
     */
    public function has($key)
    {
        return isset(self::$data[$key]);
    }

    /**
     * Get only specified input items
     * 
     * @param array|string $keys The keys to retrieve
     * @return array The specified input items
     */
    public function only($keys)
    {
        if (!is_array($keys) && !is_string($keys)) {
            throw new \InvalidArgumentException('Parameter $keys must be an array or a string.');
        }

        $keys = is_array($keys) ? $keys : func_get_args();
        $result = array_intersect_key(self::$data, array_flip($keys));
        foreach ($keys as $key) {
            if (isset(self::$files[$key])) {
                $result[$key] = self::$files[$key];
            }
        }
        return $result;
    }

    /**
     * Get all input items except the specified ones
     * 
     * @param array|string $keys The keys to exclude
     * @return array All input items except the specified ones
     */
    public function except($keys)
    {
        if (!is_array($keys) && !is_string($keys)) {
            throw new \InvalidArgumentException('Parameter $keys must be an array or a string.');
        }

        $keys = is_array($keys) ? $keys : func_get_args();
        $result = array_diff_key(self::$data, array_flip($keys));
        foreach (self::$files as $key => $value) {
            if (!in_array($key, $keys)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Retrieve a header from the request
     *
     * @param string $key The header key
     * @param mixed $default The default value if header does not exist
     * @return mixed The header value
     */
    public static function header($key, $default = null)
    {
        $CI = &get_instance();
        return $CI->input->get_request_header($key, TRUE) ?? $default;
    }

    /**
     * Check if the request has a specific header
     *
     * @param string $key The header key
     * @return bool True if header exists, false otherwise
     */
    public static function hasHeader($key)
    {
        $CI = &get_instance();
        return $CI->input->get_request_header($key, TRUE) !== NULL;
    }

    /**
     * Determine if the request is via AJAX
     * 
     * @return bool True if the request is via AJAX, false otherwise
     */
    public function ajax()
    {
        return $this->CI->input->is_ajax_request();
    }

    /**
     * Get the request method
     * 
     * @return string The request method (GET, POST, etc.)
     */
    public function method()
    {
        return $this->CI->input->method();
    }

    /**
     * Check if the request method is GET
     * 
     * @return bool True if the method is GET, false otherwise
     */
    public function isGet()
    {
        return $this->method() === 'get';
    }

    /**
     * Check if the request method is POST
     * 
     * @return bool True if the method is POST, false otherwise
     */
    public function isPost()
    {
        return $this->method() === 'post';
    }

    /**
     * Get the request URI
     * 
     * @return string The request URI
     */
    public function uri()
    {
        return $this->CI->uri->uri_string();
    }

    /**
     * Get the request URL
     * 
     * @return string The full request URL
     */
    public function url()
    {
        return $this->CI->config->site_url($this->uri());
    }

    /**
     * Get the full request URL with query string
     * 
     * @return string The full request URL with query string
     */
    public function fullUrl()
    {
        $query_string = $this->CI->input->server('QUERY_STRING');
        return $this->url() . ($query_string ? '?' . $query_string : '');
    }

    /**
     * Get the IP address of the request
     * 
     * @return string The IP address
     */
    public function ip()
    {
        return $this->CI->input->ip_address();
    }

    /**
     * Get the user agent string
     * 
     * @return string The user agent string
     */
    public function userAgent()
    {
        return $this->CI->input->user_agent();
    }
}
