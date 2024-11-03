<?php

defined('BASEPATH') or exit('No direct script access allowed');

class EntityFile_model extends MY_Model
{
    public $table = 'entity_files';
    public $primaryKey = 'id';

    public $fillable = [
        'files_name',
        'files_original_name',
        'files_type',
        'files_mime',
        'files_extension',
        'files_size',
        'files_compression',
        'files_folder',
        'files_path',
        'files_disk_storage',
        'files_path_is_url',
        'files_description',
        'entity_type',
        'entity_id',
        'entity_file_type',
        'user_id'
    ];

    public $_validationRules = [
        'files_name' => ['field' => 'files_name', 'label' => 'Files Name', 'rules' => 'required|trim|max_length[255]'],
        'files_original_name' => ['field' => 'files_original_name', 'label' => 'Files Original Name', 'rules' => 'required|trim|max_length[255]'],
        'files_type' => ['field' => 'files_type', 'label' => 'Files Type', 'rules' => 'required|trim|max_length[20]'],
        'files_mime' => ['field' => 'files_mime', 'label' => 'Files Mime', 'rules' => 'required|trim|max_length[50]'],
        'files_extension' => ['field' => 'files_extension', 'label' => 'Files Extension', 'rules' => 'required|trim|max_length[10]'],
        'files_size' => ['field' => 'files_size', 'label' => 'Files Size', 'rules' => 'required|trim|integer'],
        'files_compression' => ['field' => 'files_compression', 'label' => 'Files Compression', 'rules' => 'required|trim'],
        'files_folder' => ['field' => 'files_folder', 'label' => 'Files Folder', 'rules' => 'required|trim|max_length[255]'],
        'files_path' => ['field' => 'files_path', 'label' => 'Files Path', 'rules' => 'required|trim|max_length[255]'],
        'files_disk_storage' => ['field' => 'files_disk_storage', 'label' => 'Files Disk Storage', 'rules' => 'required|trim|max_length[20]'],
        'files_path_is_url' => ['field' => 'files_path_is_url', 'label' => 'Files Path Is Url', 'rules' => 'required|trim'],
        'files_description' => ['field' => 'files_description', 'label' => 'Files Description', 'rules' => 'required|trim|max_length[255]'],
        'entity_type' => ['field' => 'entity_type', 'label' => 'Entity Type', 'rules' => 'required|trim|max_length[255]'],
        'entity_id' => ['field' => 'entity_id', 'label' => 'Entity Id', 'rules' => 'required|trim|integer'],
        'entity_file_type' => ['field' => 'entity_file_type', 'label' => 'Entity File Type', 'rules' => 'required|trim|max_length[255]'],
        'user_id' => ['field' => 'user_id', 'label' => 'User Id', 'rules' => 'required|trim|integer']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
