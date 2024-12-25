<?php

use App\Libraries\ImportExportCSV\ImportProcessor;

class ImportExportController extends MY_Controller
{
    private $importer;

    public function __construct()
    {
        parent::__construct();
        model('User_model');
        model('UserProfile_model');

        try {
            $this->importer = new ImportProcessor();
        } catch (Exception $e) {
            log_message('error', 'Failed to initialize ImportProcessor: ' . $e->getMessage());
            show_error('System is currently unavailable. Please try again later.');
        }
    }

    public function index()
    {
        render('upload/import_user', [
            'title' => 'CSV User Import',
            'currentSidebar' => null,
            'currentSubSidebar' => null,
            'permission' => permission(
                [
                    'csv-view'
                ]
            )
        ]);
    }

    /**
     * Example of importing users from CSV
     */
    public function importUsers()
    {
        try {
            // Validate file upload
            if (!isset($_FILES['csv_file'])) {
                throw new RuntimeException("No file uploaded");
            }

            $file = $_FILES['csv_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("Upload failed with error code: " . $file['error']);
            }

            $upload = upload($_FILES['csv_file'], 'public/upload/csv');

            if (isSuccess($upload['status'])) {
                $filepath = $upload['data']['files_path'];

                // The process callback use to filter/transform data and save the data into table. 
                // Need to return TRUE or FALSE for importer to calculate total data process, total successful insert, total failed to insert.
                $logicToInsert = function ($row) {

                    $save = $this->User_model
                        ->ignoreValidation()
                        ->insertOrUpdate(
                            ['email' => $row['C']], // condition to check if exists or not
                            [
                                'name' => $row['A'],
                                'user_preferred_name' => $row['B'],
                                'email' => $row['C'],
                                'user_gender' => $row['D'],
                                'user_contact_no' => $row['E'],
                                'username' => $row['F'],
                                'password' => password_hash($row['G'], PASSWORD_DEFAULT),
                                'created_at' => timestamp()
                            ]
                        );

                    if (isSuccess($save['code'])) {
                        $this->UserProfile_model
                            ->ignoreValidation()
                            ->insertOrUpdate(
                                ['user_id' => $save['id']], // condition to check if exists or not
                                [
                                    'role_id' => '2',
                                    'is_main' => '1',
                                    'profile_status' => '1',
                                    'created_at' => timestamp()
                                ]
                            );
                    }

                    return isSuccess($save['code']);
                };

                # Process the import (foreground) - used for small csv data, less then 100 data
                // $result = $this->importer->skipHeader()->import($filepath, $logicToInsert);

                # Process the import (background - single processing) - use for large csv data, more then 100 less then 1000
                $result = $this->importer->skipHeader()->runBackground()->import($filepath, $logicToInsert);

                # Process the import (background - multi processing) - use for large csv data, more then 1000
                // $result = $this->importer->skipHeader()->setParallel(5)->runBackground()->import($filepath, $logicToInsert);

            } else {
                throw new RuntimeException("Upload failed: " . $upload['status']);
            }

            $response = ['code' => 200, 'message' => 'Process data completed', 'data' => $result];
        } catch (Exception $e) {
            log_message('error', 'Import failed: ' . $e->getMessage());
            $response = ['code' => 422, 'message' => $e->getMessage()];
        }

        jsonResponse($response);
    }

    /**
     * Example of exporting users to CSV
     */
    // public function exportUsers()
    // {
    //     try {
    //         // Configure export settings
    //         $this->csvProcessor->setHeaders([
    //             'ID',
    //             'Name',
    //             'Email',
    //             'Status',
    //             'Last Login',
    //             'Created At'
    //         ])
    //             ->setFilename('users_export_' . date('YmdHis'))
    //             ->setGeneratePath(asset('export/'));

    //         // Get total count for large datasets
    //         $totalUsers = $this->User_model->count_all();

    //         // Use background processing for large datasets
    //         if ($totalUsers > 5000) {
    //             $this->csvProcessor->runBackground();
    //         }

    //         // Get users with eager loading of related data
    //         $users = $this->User_model->with(['profile', 'last_login']);

    //         // Process the export
    //         $result = $this->csvProcessor->export(
    //             $users,
    //             function ($row) {

    //                 dd();

    //                 // return [
    //                 //     $user->id,
    //                 //     $user->name,
    //                 //     $user->email,
    //                 //     $user->status,
    //                 //     $user->last_login ? date('Y-m-d H:i:s', strtotime($user->last_login)) : '',
    //                 //     date('Y-m-d H:i:s', strtotime($user->created_at))
    //                 // ];
    //             }
    //         );

    //         $response = ['success' => true, 'data' => $result];
    //     } catch (Exception $e) {
    //         log_message('error', 'Export failed: ' . $e->getMessage());
    //         $response = ['success' => false, 'error' => $e->getMessage()];
    //     }

    //     jsonResponse($response);
    // }

    /**
     * Get process status
     */
    // public function getStatus($processId)
    // {
    //     try {
    //         $result = $this->csvProcessor->getProcessStatusPid($processId);
    //         $response = ['success' => true, 'data' => $result];
    //     } catch (Exception $e) {
    //         $response = ['success' => false, 'error' => $e->getMessage()];
    //     }

    //     jsonResponse($response);
    // }
}
