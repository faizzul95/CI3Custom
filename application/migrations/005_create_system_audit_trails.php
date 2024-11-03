<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_system_audit_trails extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_audit_trails';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'user_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer table users'],
			'role_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer table master_roles'],
			'user_fullname' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'event' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'comment' => ''],
			'table_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => TRUE, 'comment' => ''],
			'old_values' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'new_values' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'url' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE, 'comment' => ''],
			'ip_address' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE, 'comment' => ''],
			'user_agent' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);

		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down() {
		$this->dbforge->drop_table($this->table_name, TRUE);
	}
}
