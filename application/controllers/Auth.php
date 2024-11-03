<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        model('User_model');
        model('UserProfile_model');
        model('SystemLoginAttempt_model');
        model('SystemLoginHistory_model');
        model('MasterEmailTemplate_model');
        model('SystemQueueJob_model');

        library('user_agent');
    }

    public function index()
    {
        render('Auth/login', ['title' => 'Sign In', 'currentSidebar' => 'auth', 'currentSubSidebar' => null]);
    }

    public function authorize()
    {
        $validateRecaptcha = recaptchav2();

        // Check with recaptcha first
        if ($validateRecaptcha['success']) {

            $data = $this->User_model
                ->with(['main_profile' => function ($query) {
                    $query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status'])
                        ->where('is_main', '1')
                        ->where('profile_status', '1');
                }])
                ->with(['main_profile.roles' => function ($query) {
                    $query->select('id, role_name')->safeOutput();
                }])
                ->with('main_profile.avatar')
                ->where('username', input('username'))
                ->safeOutput()
                ->fetch();

            if (hasData($data)) {
                $userID = hasData($data, 'id', true);

                $countAttempt = $this->SystemLoginAttempt_model
                    ->where('ip_address', $this->input->ip_address())
                    ->where('time > NOW() - INTERVAL 10 MINUTE')
                    ->where('user_id', $userID)
                    ->count();

                $maxLoginAttempt = 5;
                $attemptExceed = !($maxLoginAttempt <= (int) $countAttempt);

                if ($attemptExceed) {
                    if (password_verify(input('password'), hasData($data, 'password', true, 'N/A'))) {
                        $response = $this->sessionLoginStart($data);

                        // Clear attempt if login is success
                        if (isSuccess($response['code'])) {
                            $this->SystemLoginAttempt_model
                                ->where('user_id', $userID)
                                ->where('ip_address', $this->input->ip_address())
                                ->destroyAll();
                        }
                    } else {

                        $this->SystemLoginAttempt_model->create([
                            'user_id' => $userID,
                            'ip_address' => $this->input->ip_address(),
                            'user_agent' => $this->input->user_agent(),
                            'time' => timestamp(),
                        ]);

                        $countAttemptRemain = $maxLoginAttempt - (int) $countAttempt;

                        $response = [
                            'code' => 400,
                            'message' => ($countAttempt >= 2) ? 'Invalid username or password. Attempt remaining : ' . $countAttemptRemain : 'Invalid username or password',
                            'verify' => false
                        ];
                    }
                } else {
                    $response = [
                        'code' => 400,
                        'message' => 'You have reached maximum number of login attempt. Your account has been suspended for 15 minutes.',
                        'verify' => false
                    ];
                }
            }
        } else {
            $response = array(
                "code" => 400,
                "message" => filter_var(env('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) ? $validateRecaptcha['error_message'] : "Please verify that you're a human",
                'verify' => false,
                "redirectUrl" => NULL,
            );
        }

        jsonResponse($response);
    }

    public function socialite()
    {
        $data = $this->User_model
            ->with(['main_profile' => function ($query) {
                $query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status'])
                    ->where('is_main', '1')
                    ->where('profile_status', '1');
            }])
            ->with(['main_profile.roles' => function ($query) {
                $query->select('id, role_name')->safeOutput();
            }])
            ->with('main_profile.avatar')
            ->where('email', input('email'))
            ->safeOutput()
            ->fetch();

        jsonResponse($this->sessionLoginStart($data));
    }

    // public function reset_password() {}
    // public function impersonateUser($id = null) {}

    public function change_profile()
    {
        $profileID = input('profile_id');
        $userID = input('user_id');

        $data = $this->UserProfile_model
            ->with(['roles' => function ($query) {
                $query->select('id, role_name')->safeOutput();
            }])
            ->with('avatar')
            ->safeOutput()
            ->find($profileID);

        setSession([
            'userID'  => $userID,
            'userAvatar'  => asset(hasData($data, 'avatar.files_path', true, 'upload/images/defaultUser.png')),
            'profileID'  => hasData($data, 'id', true),
            'profileName' => hasData($data, 'roles.role_name', true),
            'roleID' => hasData($data, 'roles.id', true),
            'isLoggedInSession' => TRUE
        ]);

        jsonResponse(['code' => 200, 'message' => NULL]);
    }

    private function sessionLoginStart($dataUser, $isImpersonate = false, $notify = true)
    {
        $response = ['code' => 400, 'message' => 'Wrong username/email or password'];

        if (!hasData($dataUser)) {
            return $response;
        }

        $userStatus = hasData($dataUser, 'user_status', true);

        if ($userStatus == 1) {
            if (hasData($dataUser, 'main_profile')) {

                $userID  = hasData($dataUser, 'id', true);
                $sessionData = [
                    'userID'  => $userID,
                    'userFullName'  => hasData($dataUser, 'name', true, 'N/A'),
                    'userNickName'  => hasData($dataUser, 'user_preferred_name', true, 'N/A'),
                    'userEmail'  => hasData($dataUser, 'email', true, 'N/A'),
                    'userAvatar'  => asset(hasData($dataUser, 'main_profile.avatar.files_path', true, 'upload/images/defaultUser.png')),
                    'profileID'  => hasData($dataUser, 'main_profile.id', true),
                    'profileName' => hasData($dataUser, 'main_profile.roles.role_name', true),
                    'roleID' => hasData($dataUser, 'main_profile.roles.id', true),
                    'isImpersonateSession' => $isImpersonate,
                    'isLoggedInSession' => TRUE
                ];

                if ($isImpersonate) {
                    $sessionData['impersonateUserID'] = currentUserID();
                } else if (getSession('impersonateUserID')) {
                    $this->session->unset_userdata('impersonateUserID');
                }

                setSession($sessionData);

                if ($notify) {
                    // Sent email secure login
                    $template = $this->MasterEmailTemplate_model->where('email_type', 'SECURE_LOGIN')->where('email_status', '1')->fetch();

                    // if template email is exist and active
                    if (hasData($template)) {

                        $browsers = $this->agent->browser();
                        $os = $this->agent->platform();
                        $iplogin = $this->input->ip_address();

                        $bodyMessage = replaceTextWithData($template['email_body'], [
                            'name' => purify(hasData($dataUser, 'name', true, 'N/A')),
                            'email' => purify(hasData($dataUser, 'email', true, 'N/A')),
                            'browsers' => $browsers,
                            'os' => $os,
                            'details' => '<table border="1" cellpadding="1" cellspacing="1" width="40%">
                			<tr>
                				<td style="width:30%">&nbsp; Username </td>
                				<td style="width:70%">&nbsp; ' . purify($dataUser['username']) . ' </td>
                			</tr>
                			<tr>
                				<td style="width:30%">&nbsp; Browser </td>
                				<td style="width:70%">&nbsp; ' . $browsers . ' </td>
                			</tr>
                			<tr>
                				<td style="width:30%">&nbsp; Operating System </td>
                				<td style="width:70%">&nbsp; ' . $os . ' </td>
                			</tr>
                			<tr>
                				<td style="width:30%">&nbsp; IP Address </td>
                				<td style="width:70%">&nbsp; ' . $iplogin . ' </td>
                			</tr>
                			<tr>
                				<td style="width:30%">&nbsp; Date </td>
                				<td style="width:70%">&nbsp; ' . timestamp('d/m/Y') . ' </td>
                			</tr>
                			<tr>
                				<td style="width:30%">&nbsp; Time </td>
                				<td style="width:70%">&nbsp; ' . timestamp('h:i A') . ' </td>
                			</tr>
                		</table>',
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

                return ['code' => 200, 'message' => 'Login', 'verify' => false, 'redirectUrl' => url('dashboard')];
            } else {
                return ['code' => 400, 'message' => 'No active profile, Please contact system administrator', 'verify' => false, 'redirectUrl' => url('dashboard')];
            }
        } else {
            return ['code' => 400, 'message' => 'Your ID is inactive, Please contact system administrator', 'verify' => false, 'redirectUrl' => NULL];
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('/');
    }
}
