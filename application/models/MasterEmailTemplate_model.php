<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MasterEmailTemplate_model extends MY_Model
{
    public $table = 'master_email_templates';
    public $primaryKey = 'id';

    public $fillable = [
        'email_type',
        'email_subject',
        'email_body',
        'email_footer',
        'email_cc',
        'email_bcc',
        'email_status',
        'company_id'
    ];

    public $_validationRules = [
        'email_type' => ['field' => 'email_type', 'label' => 'Email Type', 'rules' => 'required|trim|max_length[255]'],
        'email_subject' => ['field' => 'email_subject', 'label' => 'Email Subject', 'rules' => 'required|trim|max_length[255]'],
        'email_body' => ['field' => 'email_body', 'label' => 'Email Body', 'rules' => 'required|trim'],
        'email_footer' => ['field' => 'email_footer', 'label' => 'Email Footer', 'rules' => 'required|trim|max_length[255]'],
        'email_cc' => ['field' => 'email_cc', 'label' => 'Email Cc', 'rules' => 'required|trim'],
        'email_bcc' => ['field' => 'email_bcc', 'label' => 'Email Bcc', 'rules' => 'required|trim'],
        'email_status' => ['field' => 'email_status', 'label' => 'Email Status', 'rules' => 'required|trim'],
        'company_id' => ['field' => 'company_id', 'label' => 'Company Id', 'rules' => 'required|trim|integer']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
