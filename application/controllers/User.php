<?php
defined('BASEPATH') or exit('No direct script access allowed');

use App\Constants\LoginPolicy;

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

        // Validate username
        $usernameErrors = LoginPolicy::validateUsername($data['username']);
        if (!empty($usernameErrors)) {
            // Format username errors as an HTML list
            $errorList = '';
            if (!empty($usernameErrors)) {
                $errorList = '<ul>';
                foreach ($usernameErrors as $error) {
                    $errorList .= '<li>' . htmlspecialchars($error) . '</li>';
                }
                $errorList .= '</ul>';
            }

            // Return JSON response with formatted HTML error list
            jsonResponse([
                'code' => 422,
                'message' => $errorList,
                'errors' => ['username' => $usernameErrors]
            ]);
        }

        // For new users
        if (!hasData($userID)) {
            $data['user_status'] = '1';

            if (!empty($data['password'])) {
                // Validate password
                $passwordErrors = LoginPolicy::validatePassword(
                    $data['password'],
                    $data['username'],
                    isset($data['email']) ? $data['email'] : null
                );

                if (!empty($passwordErrors)) {
                    jsonResponse([
                        'code' => 422,
                        'message' => implode(' ', $passwordErrors),
                        'errors' => ['password' => $passwordErrors]
                    ]);
                }
            } else {
                $data['password'] = LoginPolicy::DEFAULT_PASSWORD;
            }

            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_last_changed'] = time();

            // Check if we should force password change
            if ($data['password'] === LoginPolicy::DEFAULT_PASSWORD && LoginPolicy::FORCE_CHANGE_DEFAULT_PASSWORD) {
                $data['password_must_change'] = 1;
            }
        }

        // Build validation rules based on policy
        $usernameRule = 'required|min_length[' . LoginPolicy::USERNAME_MIN_LENGTH . ']' .
            '|max_length[' . LoginPolicy::USERNAME_MAX_LENGTH . ']' .
            '|regex_match[' . LoginPolicy::USERNAME_PATTERN . ']';

        $passwordRule = 'required|trim|min_length[' . LoginPolicy::PASSWORD_MIN_LENGTH . ']' .
            '|max_length[' . LoginPolicy::PASSWORD_MAX_LENGTH . ']' .
            '|regex_match[' . LoginPolicy::PASSWORD_PATTERN . ']';

        // Insert or update user data
        $saveUser = $this->User_model
            ->setPatchValidationRules([
                'email' => ['field' => 'email', 'label' => 'Email', 'rules' => 'required|trim|valid_email', 'errors' => ['valid_email' => 'Please enter valid email.']],
                'username' => ['field' => 'username', 'label' => 'Nama Pengguna', 'rules' => $usernameRule, 'errors' => ['regex_match' => LoginPolicy::USERNAME_PATTERN_MSG]],
                'password' => ['field' => 'password', 'label' => 'Kata Laluan', 'rules' => $passwordRule, 'errors' => ['regex_match' => LoginPolicy::PASSWORD_PATTERN_MSG]]
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
            $dataUser = $this->User_model->select(['id', 'password', 'username', 'email'])->find($userID);

            $rules = ['field' => 'confirmpassword', 'label' => 'Confirm password', 'rules' => 'trim|required|min_length[4]|max_length[20]'];

            $canUpdate = isSuperadmin() || isAdmin() ? true : password_verify(input('oldpassword'), $dataUser['password']);

            if ($canUpdate) {
                $passwordErrors = LoginPolicy::validatePassword(
                    $newpassword,
                    $dataUser['username'],
                    isset($dataUser['email']) ? $dataUser['email'] : null
                );

                if (!empty($passwordErrors)) {
                    $errorList = '';
                    if (!empty($passwordErrors)) {
                        $errorList = '<ul>';
                        foreach ($passwordErrors as $error) {
                            $errorList .= '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        $errorList .= '</ul>';
                    }

                    // Return JSON response with formatted HTML error list
                    $response = [
                        'code' => 422,
                        'message' => $errorList,
                        'errors' => ['password' => $passwordErrors]
                    ];
                } else {
                    $response = $this->User_model->setValidationRules($rules)
                        ->patch(
                            [
                                'password' => password_hash($newpassword, PASSWORD_DEFAULT),
                                'password_last_changed' => time(),
                            ],
                            $userID
                        );

                    if (isSuccess($response['code'])) {
                        if (LoginPolicy::NOTIFY_PASSWORD_CHANGE) {
                            $template = $this->MasterEmailTemplate_model->where('email_type', 'PASSWORD_UPDATE')->where('email_status', '1')->fetch();

                            // if template email is exist and active
                            if (hasData($template)) {

                                $bodyMessage = replaceTextWithData($template['email_body'], [
                                    'name' => purify(hasData($dataUser, 'name', true, 'N/A')),
                                    'email' => purify(hasData($dataUser, 'email', true, 'N/A')),
                                    'username' => purify(hasData($dataUser, 'username', true, 'N/A')),
                                    'url' => base_url()
                                ]);

                                // add to queue
                                $this->SystemQueueJob_model->create([
                                    'uuid' => uuid(),
                                    'type' => 'email',
                                    'payload' => json_encode([
                                        'name' => purify(hasData($dataUser, 'name', true, 'N/A')),
                                        'to' => purify(hasData($dataUser, 'email', true, 'N/A')),
                                        'cc' => $template['email_cc'],
                                        'bcc' => $template['email_bcc'],
                                        'subject' => $template['email_subject'],
                                        'body' => $bodyMessage,
                                        'attachment' => NULL,
                                    ]),
                                    'created_at' => timestamp()
                                ]);
                            }
                        }
                    }
                }
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
