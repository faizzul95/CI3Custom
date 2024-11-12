<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_system_backup_db extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_backup_db';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'backup_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'backup_storage_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'local', 'null' => TRUE, 'comment' => ''],
			'backup_location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);

		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down()
	{
		$this->dbforge->drop_table($this->table_name, TRUE);
	}
}