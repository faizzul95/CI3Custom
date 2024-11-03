<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_users extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'users';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'user_preferred_name' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'comment' => ''],
			'email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'user_gender' => ['type' => 'TINYINT', 'null' => TRUE, 'comment' => ''],
			'user_dob' => ['type' => 'DATE', 'null' => TRUE, 'comment' => ''],
			'username' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'comment' => ''],
			'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'user_status' => ['type' => 'TINYINT', 'default' => '4', 'null' => TRUE, 'comment' => ''],
			'remember_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'first_login' => ['type' => 'TINYINT', 'default' => '1', 'null' => TRUE, 'comment' => ''],
			'email_verified_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
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
