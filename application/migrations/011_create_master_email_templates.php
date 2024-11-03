<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_master_email_templates extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'master_email_templates';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'null' => TRUE, 'comment' => ''],
			'email_type' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'email_subject' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'email_body' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'email_footer' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'email_cc' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'email_bcc' => ['type' => 'LONGTEXT', 'null' => TRUE, 'comment' => ''],
			'email_status' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '1', 'null' => TRUE, 'comment' => ''],
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
