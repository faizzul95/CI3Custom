<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_system_queue_job extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_queue_job';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'uuid' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => TRUE, 'comment' => ''],
			'type' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => TRUE, 'comment' => ''],
			'payload' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'attempt' => ['type' => 'INT', 'default' => '0', 'null' => TRUE, 'comment' => ''],
			'status' => ['type' => 'TINYINT', 'default' => '1', 'null' => TRUE, 'comment' => '1 - Pending, 2 - Processing, 3 - Completed, 4 - Failed'],
			'user_id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'null' => TRUE, 'comment' => 'Refer to table users'],
			'message' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'start_time' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'end_time' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'run_time' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'comment' => 'in seconds'],
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
}
