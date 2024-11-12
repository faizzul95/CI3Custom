<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_system_abilities extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_abilities';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'abilities_name' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'abilities_slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'comment' => ''],
			'abilities_desc' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
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
				'id'	  			  => '1',
				'abilities_name'	  => 'All Access',
				'abilities_slug'	  => '*',
				'abilities_desc'	  => 'User can view everything',
				'created_at'		  => timestamp(),
			],
			[
				'id'	  			  => '2',
				'abilities_name'	  => 'View Dashboard',
				'abilities_slug'	  => 'dashboard-view',
				'abilities_desc'	  => 'User can view dashboard information',
				'created_at'		  => timestamp(),
			],
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
