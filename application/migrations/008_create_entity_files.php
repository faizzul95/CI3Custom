<?php 

 defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_entity_files extends CI_Migration {

	public function __construct() {
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'entity_files';
	}

	public function up() {
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'null' => TRUE, 'comment' => ''],
			'files_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'files_original_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'files_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'files_mime' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'files_extension' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => TRUE, 'comment' => ''],
			'files_size' => ['type' => 'INT', 'default' => '0', 'null' => TRUE, 'comment' => ''],
			'files_compression' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => TRUE, 'comment' => ''],
			'files_folder' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'files_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'files_disk_storage' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'public', 'null' => TRUE, 'comment' => ''],
			'files_path_is_url' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '0', 'null' => TRUE, 'comment' => ''],
			'files_description' => ['type' => 'TEXT', 'null' => TRUE, 'comment' => ''],
			'entity_type' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'entity_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => ''],
			'entity_file_type' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'user_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => ''],
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
