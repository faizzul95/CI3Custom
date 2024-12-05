<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        model('User_model');
        model('UserProfile_model');
        model('EntityFile_model');
    }

    public function index()
    {
        render('user/list',  [
            'title' => 'Directory',
            'currentSidebar' => 'Directory',
            'currentSubSidebar' => 'List users',
            'permission' => permission(['user-view', 'user-create'])
        ]);
    }

    public function show($id)
    {
        $dataUser = $this->User_model->with('main_profile', 'main_profile.roles')->find($id);

        jsonResponse($dataUser);
    }

    public function listDt()
    {
        if (hasData($_POST, 'condition.filter_roles', true)) {
            $this->User_model->whereHas('profile', function ($query) {
                $query->where('role_id', $_POST['condition']['filter_roles']);
            });
        }

        unset($_POST['condition']['filter_roles']); // remove from $_POST

        $paginateData = $this->User_model->with('profile', 'profile.roles', 'department')
            ->setAppends(['status_badge']) // appends the badge status
            ->safeOutputWithException(['status_badge'])
            ->setPaginateFilterColumn(
                [
                    'name',
                    'email',
                    'created_at',
                    'user_status'
                ]
            )->paginate_ajax($_POST, $_POST['condition']);

        if (hasData($paginateData, 'data')) {
            foreach ($paginateData['data'] as $key => $member) {

                $actionArr = [];
                $profileArr = [];
                $hasSuperadminProfile = false;
                $currentUserLogin = currentUserID() == $member['id'] ? true : false;

                if (hasData($member, 'profile')) {

                    $badge = [
                        '1' => '<span class="badge badge-soft-info">%role_name% %main_profile%</span>',
                        '2' => '<span class="badge badge-soft-secondary">%role_name% %main_profile%</span>',
                        '3' => '<span class="badge badge-soft-success">%role_name% %main_profile%</span>',
                        '4' => '<span class="badge badge-soft-primary">%role_name% %main_profile%</span>',
                        '5' => '<span class="badge badge-soft-danger">%role_name% %main_profile%</span>',
                        '6' => '<span class="badge badge-soft-warning">%role_name% %main_profile%</span>',
                    ];

                    foreach ($member['profile'] as $profile) {
                        $mainProfile = ($profile['is_main'] == 1) ? ' &nbsp; <i class="ri-star-fill" style="color:orange" title="Main profile"></i>' : '';
                        $profileBadge = replaceTextWithData(
                            hasData($badge, $profile['role_id'], true, '<span class="badge badge-soft-secondary">%role_name% %main_profile%</span>'),
                            [
                                'role_name' => $profile['roles']['role_name'],
                                'main_profile' => $mainProfile
                            ]
                        );

                        if ($profile['role_id'] == 1) {
                            $hasSuperadminProfile = true;
                        }

                        array_push($profileArr, $profileBadge);
                    }
                }

                $del = permission('user-delete') && !$hasSuperadminProfile && !$currentUserLogin ? actionBtn('delete', 'deleteRecord', $member['id'], ['class' => 'btn-sm btn-soft-danger']) : null;
                $edit = permission('user-update') ? actionBtn('edit', 'editRecord', $member['id'], ['class' => 'btn-sm btn-soft-success']) : null;
                $profile = permission('user-assign-role') ? '<button class="btn btn-soft-secondary btn-sm" onclick="profileRecord(' . $member['id'] . ', \'' . $member['name'] . '\')" title="Profile"><i class="ri-shield-user-line"></i> </button>' : null;
                $reset = isSuperadmin() || isAdmin() ? '<button class="btn btn-soft-dark btn-sm" onclick="resetPassword(' . $member['id'] . ', \'' . $member['name'] . '\')" title="Reset Password"><i class="ri-key-line"></i> </button>' : null;

                array_push($actionArr, $profile, $reset, $edit, $del);

                // Replace the data with formated data
                $paginateData['data'][$key] = [
                    $member['name'],
                    $member['email'],
                    hasData($profileArr) ? '<ul><li>' . implode('</li><li>', $profileArr) . '</li></ul>' : '<small><i>(Tiada peranan terdaftar)</i></small>',
                    $member['status_badge'], // not an actually column, this coming from appends in models or using setAppends() method
                    '<div class="text-center">' . implode(' ', $actionArr) . '</div>'
                ];
            }
        }

        jsonResponse($paginateData);
    }

    public function save()
    {
        // Get data with safe from XSS attack
        $data = $this->request->all();

        // Get the info from the form
        $userID = hasData($data, 'user_id', true);

        if (!hasData($userID)) {
            $data['user_status'] = '1';
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); // only update data for update and has the password fields exist
        }

        // Insert or update user data
        $saveUser = $this->User_model
            ->setPatchValidationRules([
                'email' => ['field' => 'email', 'label' => 'Emel', 'rules' => 'required|trim|valid_email', 'errors' => ['valid_email' => 'Please enter valid email.']],
                'username' => ['field' => 'username', 'label' => 'Nama Pengguna', 'rules' => 'required|min_length[5]|max_length[12]'],
                'password' => ['field' => 'password', 'label' => 'Kata Laluan', 'rules' => 'required|trim|min_length[8]|max_length[255]']
            ], true) // set true to update the existing rules in model
            ->insertOrUpdate(['id' => $userID], $data);

        jsonResponse($saveUser);
    }

    public function delete($id)
    {
        $deleteData = $this->User_model->destroy($id);

        if (isSuccess($deleteData['code'])) {
            $deleteData['profile'] = $this->UserProfile_model->where('user_id', $id)->destroyAll();
        }

        jsonResponse($deleteData);
    }

    public function resetPassword()
    {
        $newpassword = input('newpassword');
        $confirmpassword = input('confirmpassword');

        if ($newpassword != $confirmpassword) {
            $response = ['code' => 422, 'message' => 'Confirm password are not match.'];
        } else {
            $userID = input('user_id');
            $dataUser = $this->User_model->select(['id', 'password'])->find($userID);

            $rules = ['field' => 'confirmpassword', 'label' => 'Pengesahan Kata Laluan', 'rules' => 'trim|required|min_length[4]|max_length[20]'];

            $canUpdate = isSuperadmin() || isAdmin() ? true : password_verify(input('oldpassword'), $dataUser['password']);

            if ($canUpdate) {
                $response = $this->User_model->setValidationRules($rules)->patch(['password' => password_hash($newpassword, PASSWORD_DEFAULT)], $userID);
            } else {
                $response = ['code' => 422, 'message' => 'Password are not match.'];
            }
        }

        jsonResponse($response);
    }

    public function uploadProfile()
    {
        $filename = input('filename');
        $user_id = input('user_id');
        $entity_type = input('entity_type');
        $entity_file_type = input('entity_file_type');

        // get previous data
        $dataPrev = $this->EntityFile_model
            ->where('entity_type', $entity_type)
            ->where('entity_file_type', $entity_file_type)
            ->where('entity_id', $user_id)
            ->fetch();

        // if have prev data then remove
        if (hasData($dataPrev)) {
            $fileID = $dataPrev['id'];
            $filePath = $dataPrev['files_path'];
            $remove = $this->EntityFile_model->destroy($fileID);
            if (isSuccess($remove['code'])) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // generate folder
        $folder = folder('directory', $user_id, 'avatar');

        $image = $_POST['image'];
        list($type, $image) = explode(';', $image);
        list(, $image) = explode(',', $image);

        $imageUpload = base64_decode($image);

        $fileNameNew = $user_id . "_" . date('dFY') . "_" . date('his') . '.jpg';
        $path = $folder . '/' . $fileNameNew;

        // $fileSave = NULL;
        if (file_put_contents($path, $imageUpload)) {
            // move image from default
            $moveImg = moveFile(
                $fileNameNew,
                $path,
                $folder,
                [
                    'type' => $entity_type,
                    'file_type' => $entity_file_type,
                    'entity_id' => $user_id,
                    'user_id' => $user_id,
                ],
                'rename'
            );

            if (!empty($moveImg)) {

                $fileSave = $this->EntityFile_model->create($moveImg);

                if (isSuccess($fileSave['code'])) {
                    if (currentUserID() == $user_id) {
                        setSession([
                            'userAvatar'  => asset(hasData($moveImg, 'files_path', true, 'upload/images/defaultUser.png'), false),
                        ]);
                    }
                }
            }
        }

        jsonResponse($fileSave);
    }
}
