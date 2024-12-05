<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserProfile extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        model('UserProfile_model');
        model('User_model');
    }

    public function index()
    {
        render('profile/account',  [
            'title' => 'Profile',
            'currentSidebar' => 'Profile',
            'currentSubSidebar' => NULL,
            'permission' => permission(
                [
                    'settings-change-password',
                    'settings-view-info',
                ]
            )
        ]);
    }

    public function listDt()
    {
        $paginateData = $this->UserProfile_model
            ->with('user', 'roles')
            ->setAppends(['main_profile_badge'])
            ->safeOutputWithException(['main_profile_badge'])
            ->setPaginateFilterColumn(
                [
                    'user_id',
                ]
            )
            ->paginate_ajax($_POST, $_POST['condition']);

        if (hasData($paginateData, 'data')) {
            foreach ($paginateData['data'] as $key => $member) {

                $actionArr = [];
                $btnSet = '';

                if (permission('user-default-profile')) {
                    $btnSet = '<button type="button" class="btn btn-soft-info btn-sm" onclick="setDefaultProfile(' . $member['id'] . ', ' . $member['user_id'] . ', \'' . $member['roles']['role_name'] . '\')"> Set default </button>';
                }

                $del = permission('user-delete-profile') && $member['is_main'] == 0 ? actionBtn('delete', 'deleteProfileRecord', $member['id'], ['class' => 'btn-soft-danger btn-sm']) : null;

                array_push($actionArr, $del);

                $paginateData['data'][$key] = [
                    $member['roles']['role_name'],
                    hasData($member, 'main_profile_badge', true, $btnSet), // not an actually column, this coming from appends in models or using setAppends() method
                    '<div class="text-center">' . implode(' ', $actionArr) . '</div>'
                ];
            }
        }

        jsonResponse($paginateData);
    }

    public function save()
    {
        $data = $this->request->all();

        $dataUser = $this->UserProfile_model->where('user_id', input('user_id'))->get();

        $data['is_main'] = !hasData($dataUser) ? '1' : $data['is_main'];

        $result = $this->UserProfile_model->create($data);

        jsonResponse($result);
    }

    public function delete($id)
    {
        $deleteData = $this->UserProfile_model->destroy($id);

        jsonResponse($deleteData);
    }

    public function setDefaultProfile()
    {
        $data = $this->request->all();
        $userID = $data['user_id'];
        $profileID = $data['profile_id'];

        $this->UserProfile_model->where('user_id', $userID)->patchAll(['is_main' => 0]);
        jsonResponse($this->UserProfile_model->patch(['is_main' => 1], $profileID));
    }
}
