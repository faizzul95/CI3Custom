<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		model('User_model');
		model('UserProfile_model');
	}

	public function index()
	{
		view('welcome_message');
	}

	public function testing1($id = null)
	{
		$data = $this->User_model
			->with(['profile' => function ($query) {
				$query->select(['id', 'user_id', 'role_id', 'is_main', 'profile_status']);
			}])
			->with(['profile.roles' => function ($query) {
				$query->select('id,role_name, created_at')->safeOutput();
			}])
			->with('profile.avatar')
			->find($id);

		jsonResponse(['data' => $data]);
	}

	public function testing2($id = null)
	{
		$dataUser = $this->db
			->where('id', $id)
			->get('users')
			->row_array();

		if (!empty($dataUser)) {
			$dataUser['profile'] = $this->db->select('id,user_id,role_id,is_main,profile_status')
				->where('user_id', $dataUser['id'])
				->get('user_profile')
				->result_array();

			if (!empty($dataUser['profile'])) {
				foreach ($dataUser['profile'] as $key => $profile) {
					$roles = $this->db->select('id,role_name')
						->where('id', $profile['role_id'])
						->get('master_roles')
						->row_array();

					$roles['role_name'] = htmlspecialchars(trim($roles['role_name']), ENT_QUOTES, 'UTF-8');

					$avatar = $this->db->where('entity_id', $profile['id'])
						->get('entity_files')
						->row_array();

					$dataUser['profile'][$key]['roles'] = $roles;
					$dataUser['profile'][$key]['avatar'] = $avatar;
				}
			}
		}

		jsonResponse(['data' => $dataUser]);
	}
}
