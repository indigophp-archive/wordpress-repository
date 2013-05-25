<?php

namespace Fuel\Migrations;

class Create_plugins
{
	public function up()
	{
		\DBUtil::create_table('plugins', array(
			'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'temporal_start' => array('constraint' => 11, 'type' => 'int'),
			'temporal_end' => array('constraint' => 11, 'type' => 'int', 'null' => true),
			'name' => array('constraint' => 512, 'type' => 'varchar'),
			'slug' => array('constraint' => 512, 'type' => 'varchar'),
			'version' => array('constraint' => 16, 'type' => 'varchar'),
			'author' => array('constraint' => 256, 'type' => 'varchar'),
			'author_profile' => array('type' => 'text', 'null' => true),
			'homepage' => array('type' => 'text', 'null' => true),
			'description' => array('type' => 'text'),
			'short_description' => array('type' => 'text', 'null' => true),
			'changelog' => array('type' => 'text', 'null' => true),
			'faq' => array('type' => 'text', 'null' => true),
			'installation' => array('type' => 'text', 'null' => true),
			'other_notes' => array('type' => 'text', 'null' => true),
			'screenshots' => array('type' => 'text', 'null' => true),
			'requires' => array('constraint' => 8, 'type' => 'varchar'),
			'tested' => array('constraint' => 8, 'type' => 'varchar'),
			'price' => array('type' => 'float', 'null' => true),
			'purchase_link' => array('type' => 'text', 'null' => true),
			'download_link' => array('type' => 'text', 'null' => true),


		), array('id', 'temporal_start', 'temporal_end'));
	}

	public function down()
	{
		\DBUtil::drop_table('plugins');
	}
}