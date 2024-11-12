<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemBackupDb_model extends MY_Model
{
    public $table = 'system_backup_db';
    public $primaryKey = 'id';

    public $fillable = [
        'backup_name',
        'backup_storage_type',
        'backup_location'
    ];

    public $_validationRules = [
        'backup_name' => ['field' => 'backup_name', 'label' => 'Backup Name', 'rules' => 'required|trim|max_length[255]'],
        'backup_storage_type' => ['field' => 'backup_storage_type', 'label' => 'Backup Storage Type', 'rules' => 'required|trim|max_length[20]'],
        'backup_location' => ['field' => 'backup_location', 'label' => 'Backup Location', 'rules' => 'required|trim|max_length[255]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
