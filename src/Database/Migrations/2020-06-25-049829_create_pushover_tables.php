<?php namespace Tatter\Pushover\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePushoverTables extends Migration
{
	public function up()
	{
		// Messages
		$fields = [
			'message'       => ['type' => 'text'],
			'title'         => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'url'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'url_title'     => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'html'          => ['type' => 'boolean', 'default' => 0],
			'monospace'     => ['type' => 'boolean', 'default' => 0],
			'sound'         => ['type' => 'varchar', 'constraint' => 31, 'null' => true],
			'attachment'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'device'        => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'timestamp'     => ['type' => 'int', 'null' => true],
			'priority'      => ['type' => 'int'],
			'retry'         => ['type' => 'int', 'null' => true],
			'expire'        => ['type' => 'int', 'null' => true],
			'callback'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'receipt'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'request'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
			'created_at'    => ['type' => 'datetime', 'null' => true],
			'updated_at'    => ['type' => 'datetime', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('subject');
		$this->forge->addKey('created_at');
		
		$this->forge->createTable('pushover_messages');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('pushover_messages');
	}
}
