<?php

namespace Repository;

class Model_Repository extends \Orm\Model_Temporal
{
	protected static $_properties = array(
		'id',
		'temporal_start',
		'temporal_end',
		'name',
		'slug',
		'version',
		'author',
		'author_profile',
		'homepage',
		'description',
		'short_description',
		'changelog',
		'faq',
		'installation',
		'other_notes',
		'screenshots',
		'requires',
		'tested',
		'price',
		'purchase_link',
		'download_link'
	);

	protected static $_observers = array(
		'Orm\Observer_Slug' => array(
			'events'   => array('before_insert'),
			'source'   => 'name',
			'property' => 'slug',
		),
	);
	protected static $_table_name = 'repositories';

}
