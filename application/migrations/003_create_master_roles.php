<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_master_roles extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'master_roles';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'role_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'role_rank' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'role_status' => ['type' => 'TINYINT', 'null' => TRUE, 'comment' => ''],
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
		$data = [
			[
				'id' 			=> '1',
				'role_name' 	=> 'Super Administrator',
				'role_rank' 	=> '9000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '2',
				'role_name' 	=> 'System Administrator',
				'role_rank' 	=> '8000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '3',
				'role_name' 	=> 'System Support',
				'role_rank' 	=> '7000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '4',
				'role_name' 	=> 'Director',
				'role_rank' 	=> '5000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '5',
				'role_name' 	=> 'Secretary',
				'role_rank' 	=> '4500',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '6',
				'role_name' 	=> 'Administrator',
				'role_rank' 	=> '4000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			]
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
