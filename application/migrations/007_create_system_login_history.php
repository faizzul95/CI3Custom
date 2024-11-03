<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_system_login_history extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_login_history';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'user_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => ''],
			'ip_address' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => TRUE, 'comment' => ''],
			'login_type' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '1', 'null' => TRUE, 'comment' => ''],
			'operating_system' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'browsers' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'time' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'user_agent' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);

		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down() {
		$this->dbforge->drop_table($this->table_name, TRUE);
	}
}
