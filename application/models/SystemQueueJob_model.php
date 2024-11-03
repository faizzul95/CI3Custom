<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemQueueJob_model extends MY_Model
{
    public $table = 'system_queue_job';
    public $primaryKey = 'id';

    public $fillable = [
        'uuid',
        'type',
        'payload',
        'attempt',
        'status',
        'message'
    ];

    public $_validationRules = [
        'uuid' => ['field' => 'uuid', 'label' => 'Uuid', 'rules' => 'required|trim|max_length[250]'],
        'type' => ['field' => 'type', 'label' => 'Type', 'rules' => 'required|trim|max_length[250]'],
        'payload' => ['field' => 'payload', 'label' => 'Payload', 'rules' => 'required|trim'],
        'attempt' => ['field' => 'attempt', 'label' => 'Attempt', 'rules' => 'required|trim|integer'],
        'status' => ['field' => 'status', 'label' => 'Status', 'rules' => 'required|trim'],
        'message' => ['field' => 'message', 'label' => 'Message', 'rules' => 'required|trim']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
