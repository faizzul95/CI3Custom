<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_user_profile extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'user_profile';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'user_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer table users'],
			'role_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer table master_roles'],
			'is_main' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => TRUE, 'comment' => '0-No, 1-Yes'],
			'profile_status' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => TRUE, 'comment' => '0-Inactive, 1-Active'],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'deleted_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
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
		$data = [
			[
				'user_id'	  		=> '1',
				'role_id' 			=> '1',
				'is_main' 		  	=> '1',
				'profile_status' 	=> '1',
				'created_at'		=> timestamp(),
			],
			[
				'user_id'	  		=> '1',
				'role_id' 			=> '2',
				'is_main' 		  	=> '0',
				'profile_status' 	=> '1',
				'created_at'		=> timestamp(),
			],
			[
				'user_id'	  		=> '1',
				'role_id' 			=> '3',
				'is_main' 		  	=> '0',
				'profile_status' 	=> '1',
				'created_at'		=> timestamp(),
			],
			[
				'user_id'	  		=> '2',
				'role_id' 			=> '2',
				'is_main' 		  	=> '1',
				'profile_status' 	=> '1',
				'created_at'		=> timestamp(),
			],
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
