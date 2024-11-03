<?php
defined('BASEPATH') or exit('No direct script access allowed');

use App\middleware\core\traits\SecurityHeadersTrait;

class MY_Controller extends CI_Controller
{
    use SecurityHeadersTrait;

    public $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->set_security_headers();

        // Load the custom libraries
        $this->load->library('Request');

        // Assign the libraries to class properties
        $this->request = new Request();
    }
}
