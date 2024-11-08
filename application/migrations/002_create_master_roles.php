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
				'role_rank' 	=> '2000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '2',
				'role_name' 	=> 'Administrator',
				'role_rank' 	=> '1500',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			],
			[
				'id' 			=> '3',
				'role_name' 	=> 'System Support',
				'role_rank' 	=> '1000',
				'role_status' 	=> '1',
				'created_at' 	=>  timestamp(),
			]
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
