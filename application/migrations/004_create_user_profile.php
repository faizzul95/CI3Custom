<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_user_profile extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'user_profile';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'null' => TRUE, 'comment' => ''],
			'user_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => ''],
			'role_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => ''],
			'is_main' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => TRUE, 'comment' => ''],
			'profile_status' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'deleted_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
		]);

		$this->dbforge->add_key('id', TRUE);

		$this->dbforge->create_table($this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);
	}

	public function down() {
		$this->dbforge->drop_table($this->table_name, TRUE);
	}
}
