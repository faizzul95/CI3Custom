<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_system_menu_navigation extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_menu_navigation';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'menu_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'menu_description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'menu_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'menu_order' => ['type' => 'TINYINT', 'null' => TRUE, 'comment' => ''],
			'menu_icon' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE, 'comment' => ''],
			'is_main_menu' => ['type' => 'BIGINT', 'default' => '0', 'null' => TRUE, 'comment' => ''],
			'menu_location' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '1', 'null' => TRUE, 'comment' => '1 - sidemenu, 2 - topmenu, 2 - Gear Setting'],
			'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => '1', 'null' => TRUE, 'comment' => '0 - Inactive, 1 - Active'],
			'abilities_id' => ['type' => 'BIGINT', 'null' => TRUE, 'comment' => 'Refer to system_abilities'],
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

	public function seeder()
	{
		$data = [
			[
				'id'	  			  => '1',
				'menu_title'	  	  => 'Halaman Utama',
				'menu_description'	  => 'For all user',
				'menu_url'  		  => 'dashboard',
				'menu_order'	  	  => '1',
				'menu_icon'	  	      => '<i class="ri-dashboard-2-line"></i>',
				'abilities_id'	  	  => '*',
				'created_at'		  => timestamp(),
			],
			[
				'id'	  			  => '2',
				'menu_title'	  	  => 'Directory',
				'menu_description'	  => 'For CRUD User (Superadmin only)',
				'menu_url'  		  => 'user',
				'menu_order'	  	  => '2',
				'menu_icon'	  	      => '<i class="ri-user-5-line"></i>',
				'abilities_id'	  	  => '*',
				'created_at'		  => timestamp(),
			]
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
