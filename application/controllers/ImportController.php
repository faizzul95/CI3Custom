<?php
defined('BASEPATH') or exit('No direct script access allowed');

use OnlyPHP\Codeigniter3CSVImporter\CSVImportProcessor;

class ImportController extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        model('User_model');
    }

    public function index()
    {
        render('upload/user', [
            'title' => 'Upload User',
            'currentSidebar' => null,
            'currentSubSidebar' => null,
            'permission' => permission(
                [
                    'upload-user-view'
                ]
            )
        ]);
    }

    public function importUsers()
    {
        // Default return
        $response = ['code' => '422', 'message' => 'No file uploaded or an upload error occurred.', 'data' => NULL];

        // Key files (easy to change)
        $keyFiles = 'file_upload';

        try {
            // Check if a file is uploaded and no upload error
            if (isset($_FILES[$keyFiles]) && $_FILES[$keyFiles]['error'] === UPLOAD_ERR_OK) {

                // Check file size (5MB limit in this example)
                if ($_FILES[$keyFiles]["size"] > 5000000) {
                    throw new Exception('Sorry, your file is too large. Maximum size is 5MB');
                }

                // Allow certain file formats (in this example, allow only CSV files)
                $fileExtension = strtolower(pathinfo($_FILES[$keyFiles]['name'], PATHINFO_EXTENSION));
                if (!in_array($fileExtension, ['csv'])) {
                    throw new Exception('Sorry, only CSV files are allowed');
                }

                // Call upload function
                $upload = upload($_FILES[$keyFiles], 'public/upload/csv');

                if (isSuccess($upload['status'])) {
                    $importer = new CSVImportProcessor();
                    $jobId = $importer
                        ->setFileBelongsTo(currentUserID())
                        ->setCallback(function ($row, $rowIndex, $models = []) {
                            try {

                                $userModel = $models['User_model'];

                                $data = [
                                    'name' => $row[0],
                                    'user_preferred_name' => $row[1],
                                    'email' => $row[2],
                                    'user_contact_no' => $row[3],
                                    'user_gender' => $row[4],
                                    'username' => (string) $row[5],
                                    'password' => password_hash('qwerty1234!', PASSWORD_DEFAULT),
                                    'user_status' => 1,
                                ];

                                log_message('error', '==============================');
                                log_message('error', 'Start process ' . $rowIndex . ' - ' . json_encode($data));
                                $saveUser = $userModel->create($data);
                                // $saveUser = $userModel->ignoreValidation()->create($data);
                                log_message('error', 'End process ' . $rowIndex . ' - ' . json_encode($saveUser));
                                log_message('error', '==============================');

                                if (in_array($saveUser['code'], [200, 201])) {
                                    return ['code' => $saveUser['code'], 'action' => $saveUser['action']];
                                } else {
                                    return ['code' => 422, 'action' => $saveUser['action'], 'error' => $saveUser];
                                }
                            } catch (Exception $e) {
                                return $e->getMessage();
                            }
                        })
                        ->setCallbackModel(['User_model'])
                        ->process($upload['data']['files_path'], $upload['data']['files_original_name']);

                    $response = ['code' => '200', 'message' => 'Processed added to queue.', 'data' => $importer->getStatus($jobId)];
                } else {
                    throw new Exception($upload['error']);
                }
            } else {
                throw new Exception('No file uploaded or an upload error occurred.');
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        // Return the response in JSON format
        echo json_encode($response);
    }

    // STATUS CHECK

    public function listAllJob()
    {
        $importer = new CSVImportProcessor();
        $data = $importer->getStatusByOwner(currentUserID());
        echo json_encode($data);
    }

    public function getProgressJob($jobID)
    {
        $importer = new CSVImportProcessor();
        $data = $importer->getStatus($jobID);
        echo json_encode($data);
    }

    public function getFailError($jobID)
    {
        $importer = new CSVImportProcessor();
        $data = $importer->getStatus($jobID);
        echo json_encode($data);
    }

    public function deleteJob($jobID)
    {
        $importer = new CSVImportProcessor();
    }

    public function getFailedJob($jobID)
    {
        $importer = new CSVImportProcessor();
    }
}
