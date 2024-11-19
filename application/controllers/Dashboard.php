<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        render('dashboard/main', [
            'title' => 'Dashoard',
            'currentSidebar' => null,
            'currentSubSidebar' => null,
            'permission' => permission(
                [
                    'dashboard-view',
                    'dashboard-update'
                ]
            )
        ]);
    }

    public function save()
    {
        // Get data with safe from XSS attack
        $data = $this->request->all();

        // Default messaege
        $response = ['code' => 400, 'message' => 'Failed to save user'];

        // Get the info from the form
        $userID = hasData($data, 'user_id', true);
        $profileID = hasData($data, 'profile_id', true);

        if (!empty($userID) && hasData($data, 'password')) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); // only update data for update and has the password fields exist
        }

        // Insert or update user data
        $saveUser = $this->User_model->insertOrUpdate(['id' => $userID], $data);

        if (isSuccess($saveUser['code'])) {
            // Insert or update user profile data
            $saveProfile = $this->User_model->insertOrUpdate(['id' => $profileID, 'user_id' => $saveUser['id']], $data);

            if (isSuccess($saveProfile['code'])) {
                $response = ['code' => 200, 'message' => 'Save'];
            } else {
                $response = ['code' => 400, 'message' => 'Failed to save profile'];

                // Only delete the user if this is the first time register
                if ($saveUser['action'] == 'create') {
                    $this->User_model->destroy($saveUser['id']); // rollback if user is not register successfully
                }
            }
        }

        jsonResponse($response);
    }
}
