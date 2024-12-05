<?php

defined('BASEPATH') or exit('No direct script access allowed');

use App\constants\DefaultProfileRoleAbility;

class Migration_create_system_permission extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_permission';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'role_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer to master_roles'],
			'abilities_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer to system_abilities'],
			'access_device_type' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '1', 'null' => TRUE, 'comment' => '1 - Web, 2 - Mobile'],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down()
	{
		$this->dbforge->drop_table($this->table_name, TRUE);
	}

	public function seeder()
	{
		$defaultAbiities = DefaultProfileRoleAbility::ROLE_ABILITIES;

		model('SystemAbilities_model');
		$listAbilities = $this->SystemAbilities_model->get();

		$preparedData = [];
		if (!empty($listAbilities)) {
			foreach ($defaultAbiities as $role_id => $abilities) {
				foreach ($abilities as $slug) {
					$abilities_id = findInCollection($listAbilities, 'abilities_slug', $slug, 'id');
					if (!empty($abilities_id)) {
						array_push($preparedData, [
							'role_id'	  		  => $role_id,
							'abilities_id'	  	  => $abilities_id,
							'access_device_type'  => '1',
							'created_at'		  => timestamp(),
						]);
					}
				}
			}
		}

		if (!empty($preparedData)) {
			$this->db->insert_batch($this->table_name, $preparedData);
		}
	}
}
