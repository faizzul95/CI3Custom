<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_system_abilities extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
		$this->load->dbforge();
		$this->table_name = 'system_abilities';
	}

	public function up()
	{
		$this->dbforge->add_field([
			'id' => ['type' => 'BIGINT', 'unsigned' => TRUE, 'auto_increment' => TRUE, 'comment' => ''],
			'abilities_name' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE, 'comment' => ''],
			'abilities_slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'comment' => ''],
			'abilities_desc' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => ''],
			'created_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'updated_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
			'deleted_at' => ['type' => 'TIMESTAMP', 'null' => TRUE, 'comment' => ''],
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
				'abilities_name'	  => 'All access',
				'abilities_slug'	  => '*',
				'abilities_desc'	  => 'User can view everything (FOR SUPERADMIN ONLY)',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'View Dashboard',
				'abilities_slug'	  => 'dashboard-view',
				'abilities_desc'	  => 'User can view dashboard information',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'List User',
				'abilities_slug'	  => 'user-view',
				'abilities_desc'	  => 'User can view List user',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'Create New User',
				'abilities_slug'	  => 'user-create',
				'abilities_desc'	  => 'User can create new user',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'Update User',
				'abilities_slug'	  => 'user-update',
				'abilities_desc'	  => 'User can update user information',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'Delete User',
				'abilities_slug'	  => 'user-delete',
				'abilities_desc'	  => 'User can delete user information',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'User Assign Role',
				'abilities_slug'	  => 'user-assign-role',
				'abilities_desc'	  => 'User can assgin role to user',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'User Set Main Profile',
				'abilities_slug'	  => 'user-default-profile',
				'abilities_desc'	  => 'User can set default profile',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'User Delete Profile',
				'abilities_slug'	  => 'user-delete-profile',
				'abilities_desc'	  => 'User can delete user profile',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'View Info Settings',
				'abilities_slug'	  => 'settings-view-info',
				'abilities_desc'	  => 'User can view settings information',
				'created_at'		  => timestamp(),
			],
			[
				'abilities_name'	  => 'Change Password Settings',
				'abilities_slug'	  => 'settings-change-password',
				'abilities_desc'	  => 'User can view and change password settings information',
				'created_at'		  => timestamp(),
			]
		];

		$this->db->insert_batch($this->table_name, $data);
	}
}
