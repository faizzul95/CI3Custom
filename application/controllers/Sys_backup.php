<?php

defined('BASEPATH') or exit('No direct script access allowed');

use App\services\BackupSystem as BackupSystem;
use App\services\google\GoogleDrive as GD;

class Sys_backup extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        model('SystemBackupDb_model');
    }

    public function index()
    {
        show_404();
    }

    public function BackupSystem($uploadDrive = NULL)
    {
        $backup = new BackupSystem();
        $response = $backup->backup_folder();

        if (isSuccess($response['code'])) {
            if ($uploadDrive) {
                $drive = $this->BackupDrive($response['path'], 'system');
                if (isSuccess($drive['code'])) {
                    $response['data'] = [
                        'backup_storage_type' => 'google drive',
                        'backup_location' =>  $drive['data']['webViewLink']
                    ];
                }
            }
        }

        dd($response);
    }

    public function BackupDatabase($uploadDrive = NULL)
    {
        $backup = new BackupSystem();
        $response = $backup->backup_database();

        if (isSuccess($response['code'])) {

            $res = [
                'backup_name' => $response['filename'],
                'backup_storage_type' => $response['storage'],
                'backup_location' => $response['path'],
            ];

            if ($uploadDrive) {
                $drive = $this->BackupDrive($response['path'], 'database');
                if (isSuccess($drive['code'])) {
                    $res['backup_storage_type'] = 'google drive';
                    $res['backup_location'] = $drive['data']['webViewLink'];
                }
            }

            $response = $this->SystemBackupDb_model->create($res);
        }

        dd($response);
    }

    public function BackupDrive($path = NULL, $folderType = NULL)
    {
        if (!empty($path)) {
            $drive = new GD($folderType);

            if (file_exists($path)) {
                return $drive->uploadFile($path);
            } else {
                dd('File upload does not exist!');
            }
        } else {
            dd('Please specify the path to upload!');
        }
    }
}
