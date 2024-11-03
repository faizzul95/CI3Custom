<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemQueueFailedJob_model extends MY_Model
{
    public $table = 'system_queue_failed_job';
    public $primaryKey = 'id';

    public $fillable = [
        'uuid',
        'type',
        'payload',
        'exception',
        'failed_at'
    ];

    public $_validationRules = [
        'uuid' => ['field' => 'uuid', 'label' => 'Uuid', 'rules' => 'required|trim|max_length[250]'],
        'type' => ['field' => 'type', 'label' => 'Type', 'rules' => 'required|trim|max_length[250]'],
        'payload' => ['field' => 'payload', 'label' => 'Payload', 'rules' => 'required|trim'],
        'exception' => ['field' => 'exception', 'label' => 'Exception', 'rules' => 'required|trim'],
        'failed_at' => ['field' => 'failed_at', 'label' => 'Failed At', 'rules' => 'required|trim|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
