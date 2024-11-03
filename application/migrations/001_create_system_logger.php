<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_system_logger extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_logger';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'null' => TRUE, 'comment' => ''],
			'errno' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'errtype' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'errstr' => ['type' => 'TEXT', 'null' => TRUE, 'comment' => ''],
			'errfile' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'errline' => ['type' => 'TEXT', 'null' => TRUE, 'comment' => ''],
			'user_agent' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'comment' => ''],
			'ip_address' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'comment' => ''],
			'time' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);

		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down() {
		$this->dbforge->drop_table($this->table_name, TRUE);
	}
}
