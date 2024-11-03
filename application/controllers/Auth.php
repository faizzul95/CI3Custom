<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        model('User_model');

        library('user_agent');
    }

    public function index()
    {
        render('Auth/login', ['title' => 'Sign In', 'currentSidebar' => 'auth', 'currentSubSidebar' => null]);
    }

    public function authorize()
    {
        $username  = input('username');
        $enteredPassword = input('password');

        $validateRecaptcha = recaptchav2();

        // Check with recaptcha first
        if ($validateRecaptcha['success']) {

            $data = $this->User_model
                ->with(['profile' => function ($query) {
                    $query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status'])
                        ->where('is_main', '1')
                        ->where('profile_status', '1');
                }])
                ->with(['profile.roles' => function ($query) {
                    $query->select('id, role_name')->safeOutput();
                }])
                ->with('profile.avatar')
                ->where('username', input('username'))
                ->safeOutput()
                ->fetch();

            if (hasData($data)) {
                $response = $this->sessionLoginStart($data);
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
            ->with(['profile' => function ($query) {
                $query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status'])
                    ->where('is_main', '1')
                    ->where('profile_status', '1');
            }])
            ->with(['profile.roles' => function ($query) {
                $query->select('id, role_name')->safeOutput();
            }])
            ->with('profile.avatar')
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

        $data = $this->User_model
            ->with(['profile' => function ($query) use ($profileID) {
                $query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status'])->where('id', $profileID);
            }])
            ->with(['profile.roles' => function ($query) {
                $query->select('id, role_name')->safeOutput();
            }])
            ->with('profile.avatar')
            ->safeOutput()
            ->find($userID);

        setSession([
            'userID'  => $userID,
            'userAvatar'  => $userAvatar,
            'profileID'  => $profileID,
            'profileName' => $profileName,
            'roleID' => $roleID,
            'isLoggedInSession' => TRUE
        ]);

        jsonResponse(['code' => 200, 'message' => NULL]);
    }

    private function sessionLoginStart($dataUser, $isImpersonate = false, $notify = true)
    {
        $response = ['code' => 400, 'message' => 'Wrong password'];

        if (!hasData($dataUser)) {
            return $response;
        }

        $userID  = $dataUser['id'];
        $userFullName = $dataUser['user_full_name'];
        $userNickName = $dataUser['user_preferred_name'];
        $userAvatar = $dataUser['user_avatar'];
        $userStatus = $dataUser['user_status'];

        if ($userStatus == 1) {

            $sessionData = [
                'userID'  => $userID,
                'userFullName'  => $userFullName,
                'userNickName'  => $userNickName,
                'userEmail'  => $userEmail,
                'userAvatar'  => $userAvatar,
                'profileID'  => $profileID,
                'profileName' => $profileName,
                'roleID' => $roleID,
                'isImpersonateSession' => $isImpersonate,
                'isLoggedInSession' => TRUE
            ];

            // if ($isImpersonate) {
            //     $sessionData['impersonateUserID'] = currentUserID();
            // } else if (getSession('impersonateUserID')) {
            //     ci()->session->unset_userdata('impersonateUserID');
            // }

            setSession($sessionData);

            // if ($notify) {
            //     // Sent email secure login
            //     $template = $this->templateM::where(['email_type' => 'SECURE_LOGIN', 'email_status' => '1'], 'row_array');

            //     $browsers = $this->agent->browser();
            //     $os = $this->agent->platform();
            //     $iplogin = $this->input->ip_address();

            //     // if template email is exist and active
            //     if (hasData($template)) {

            //         $bodyMessage = replaceTextWithData($template['email_body'], [
            //             'name' => purify($userFullName),
            //             'email' => purify($userEmail),
            //             'browsers' => $browsers,
            //             'os' => $os,
            //             'details' => '<table border="1" cellpadding="1" cellspacing="1" width="40%">
            // 			<tr>
            // 				<td style="width:30%">&nbsp; Username </td>
            // 				<td style="width:70%">&nbsp; ' . purify($dataUser['user_username']) . ' </td>
            // 			</tr>
            // 			<tr>
            // 				<td style="width:30%">&nbsp; Browser </td>
            // 				<td style="width:70%">&nbsp; ' . $browsers . ' </td>
            // 			</tr>
            // 			<tr>
            // 				<td style="width:30%">&nbsp; Operating System </td>
            // 				<td style="width:70%">&nbsp; ' . $os . ' </td>
            // 			</tr>
            // 			<tr>
            // 				<td style="width:30%">&nbsp; IP Address </td>
            // 				<td style="width:70%">&nbsp; ' . $iplogin . ' </td>
            // 			</tr>
            // 			<tr>
            // 				<td style="width:30%">&nbsp; Date </td>
            // 				<td style="width:70%">&nbsp; ' . timestamp('d/m/Y') . ' </td>
            // 			</tr>
            // 			<tr>
            // 				<td style="width:30%">&nbsp; Time </td>
            // 				<td style="width:70%">&nbsp; ' . timestamp('h:i A') . ' </td>
            // 			</tr>
            // 		</table>',
            //             'url' => baseURL()
            //         ]);

            //         // add to queue
            //         $saveQueue = $this->systemM->saveQueue([
            //             'queue_uuid' => uuid(),
            //             'type' => 'email',
            //             'payload' => json_encode([
            //                 'name' => purify($userFullName),
            //                 'to' => purify($userEmail),
            //                 'cc' => $template['email_cc'],
            //                 'bcc' => $template['email_bcc'],
            //                 'subject' => $template['email_subject'],
            //                 'body' => $bodyMessage,
            //                 'attachment' => NULL,
            //             ]),
            //             'created_at' => timestamp()
            //         ]);
            //     }
            // }

            return ['code' => 200, 'message' => 'Login', 'verify' => false, 'redirectUrl' => url('dashboard')];
        } else {
            return ['code' => 400, 'message' => 'Your ID is inactive, Please contact college administrator', 'verify' => false, 'redirectUrl' => NULL];
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
