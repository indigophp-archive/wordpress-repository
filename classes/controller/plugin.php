<?php


namespace Repository;

class Controller_Plugin extends Controller_Repository {

	protected $format = "serialized";

	protected $post = array();

	public function before($data = null)
	{
		parent::before($data);

		if (\Input::param('plugins', false))
		{
			$this->param = (array)@unserialize(stripslashes(\Input::param('plugins', '')));
		}
	}

	public function action_index()
	{

		if (method_exists($this, \Input::param('action')))
		{
			call_user_func(array($this, \Input::param('action')));
		}
		else
		{
			$this->resp = 
		}

		switch (\Input::param('action')) {
			case 'query_plugins':
					$this->query('plugin');
				break;
			case 'plugin_information':
					$this->information('plugin');
				break;
			case 'plugins_update':
					$this->update('plugin');
				break;
			case 'query_themes':
					$this->query('theme');
				break;
			case 'theme_information':
					$this->information('theme');
				break;
			case 'themes_update':
					$this->update('theme');
				break;
			case 'hot_tags':
					$this->query('plugin');
				break;

			default:
				# code...
				break;
		}
		\File::update('/tmp/', 'valami2.txt', \Format::forge($this->answer)->to_json() . date('H:i:s'));
		return $this->response($this->answer);
	}

	private function query($type = 'plugin')
	{

		$term = '%' . \Arr::get($this->param, 'search', '') . '%';
		$page = \Arr::get($this->param, 'page', 1);
		$limit = \Arr::get($this->param, 'per_page', 10);
		$offset = ($page - 1) * $limit;

		$currency = new \stdClass;
		$currency->symbol = '$';
		$currency->name = 'USD';


		$result = Model_Repository::query()
			->where('temporal_end', Model_Repository::temporal_property('max_timestamp'))
			->where('type', $type)
			->where_open()
				->where('name', 'LIKE', $term)
				->or_where('description', 'LIKE', $term)
				->or_where('short_description', 'LIKE', $term)
			->where_close();

		$count = $result->count();

		$result = $result
			->limit($limit)
			->offset($offset)
			->get();

		$this->answer->info = array(
			'results' => $count,
			'page'    => $page,
			'pages'   => ceil($count / $limit)
		);

		$type = \Inflector::pluralize($type);

		$this->answer->$type = array();

		foreach ($result as $object) {
			$this->answer->{$type}[] = (object) array(
				'name'              => $object->name,
				'slug'              => $object->slug,
				'version'           => $object->version,
				'author'            => $object->author,
				'author_profile'    => $object->author_profile,
				'contributors'      => array(),
				'requires'          => $object->requires,
				'tested'            => $object->tested,
				'compatibility'     => array(),
				'rating'            => '',
				'num_rating'        => '',
				'homepage'          => $object->homepage,
				'description'       => $object->description,
				'short_description' => $object->short_description,
				'purchase_link'     => ! empty($object->purchase_link) ? \Uri::create($object->purchase_link) : \Uri::create('shop/' . $type . '/' . $object->slug),
				'download_link'     => ! empty($object->download_link) ? \Uri::create($object->download_link) : \Uri::create('download/' . $type . '/' . $object->slug),
				'price'             => $object->price,
				'currency'          => $currency,
				'message'           => '',
				'message_type'      => ''
			);
		}
	}

	private function information($type = 'plugin')
	{

		$slug = \Arr::get($this->param, 'slug');

		$currency = new \stdClass;
		$currency->symbol = '$';
		$currency->name = 'USD';

		$result = Model_Repository::query()
			->where('type', $type)
			->where('slug', $slug);

		$min = $result->min('temporal_start');

		$result = $result
			->where('temporal_end', Model_Repository::temporal_property('max_timestamp'))
			->get_one();

		$this->answer = (object) array(
				'added' 			=> date('Y-m-d H:i:s', $min),
				'author' 			=> $result->author,
				'author_profile' 	=> $result->author_profile,
				'contributors'		=> array(),
				'compatibility' 	=> array(),
				'purchase_link'     => ! empty($result->purchase_link) ? \Uri::create($result->purchase_link) : \Uri::create('shop/' . $type . '/' . $result->slug),
				'download_link'     => ! empty($result->download_link) ? \Uri::create($result->download_link) : \Uri::create('download/' . $type . '/' . $result->slug),
				'price' 			=> '',
				'currency' 			=> $currency,
				'downloaded' 		=> 50,
				'homepage' 			=> $result->homepage,
				'last_updated' 		=> date('Y-m-d H:i:s', $result->temporal_start),
				'name' 				=> $result->name,
				'num_ratings' 		=> '',
				'rating' 			=> '',
				'requires' 			=> $result->requires,
				'slug' 				=> $result->slug,
				'tested' 			=> $result->tested,
				'version' 			=> $result->version,
				'tags' 				=> array(),
				'sections' 			=> array(
						'changelog'    => $result->changelog,
						'description'  => $result->description,
						'faq'          => $result->faq,
						'installation' => $result->installation,
						'other_notes'  => $result->other_notes,
						'screenshots'  => $result->screenshots,
				),
				'message'           => '',
				'message_type'      => ''
		);
	}

	private function update($type = 'plugin')
	{

		$plugins = array();

		$currency = new \stdClass;
		$currency->symbol = '$';
		$currency->name = 'USD';

		foreach (\Arr::get($this->param, 'plugins') as $key => $value) {
			$plugins[$value['slug']] = array(
					'version' => $value['Version'],
					'path'    => $key
			);
		}
		$results = Model_Repository::query()
			->where('type', $type)
			->where('slug', 'IN', array_keys($plugins))
			->where('temporal_end', Model_Repository::temporal_property('max_timestamp'))
			->get();

		$this->answer = array();
		foreach ($results as $result) {
			if ($result->version > \Arr::get($plugins, $result->slug . '.version')) {
				$this->answer[\Arr::get($plugins, $result->slug . '.path')] = (object) array(
						'slug'         => $result->slug,
						'new_version'  => $result->version,
						'url'          => \Uri::create(),
						'purchase'     => ! empty($result->purchase_link) ? \Uri::create($result->purchase_link) : \Uri::create('shop/' . $type . '/' . $result->slug),
						'package'      => ! empty($result->download_link) ? \Uri::create($result->download_link) : \Uri::create('download/' . $type . '/' . $result->slug),
						'price'        => '79',
						'currency'     => $currency,
						'message'      => '',
						'message_type' => ''
				);
			}
		}
	}

	public function action_test()
	{
		$request = \Request::forge('http://api.wpml.org', 'curl');
		$request->set_method('post');
		$request->set_params(array(
			'action' => 'query_plugins',
			'auth' => array(
				'user' => 'sagikazarmark',
				//'pass' => 'EECgvhj0JOJZ',
				'pass' => '$P$BZfp9upMJqO6ES7jvd68pvwGpVOPKV0',
				'salt' => 'asd',
				//'_plain' => 'true'
			)
		));
		$request->execute();
		var_dump($request->response()); exit;
		print_r(unserialize($request->response()->body)); exit;
	}

	public function action_test2()
	{
		$request = \Request::forge('http://fuel.localhost/repository/', 'curl');
		$request->set_method('post');
		$request->set_params(array(
			'action' => 'plugins_update',
			'auth' => array(
				'user' => 'admin',
				//'pass' => 'EECgvhj0JOJZ',
				'pass' => 'YWqmPGH+dOEvOh6pf83a62lzJ1QQLHRMPHhNIaohB3s=',
				'salt' => 'asd',
				//'_plain' => 'true'
			),
			// 'request' => serialize(array(
			// 	'search' => '',
			// 	'page' => '1',
			// 	'per_page' => '10',
			// 	'slug' => 'teszt-plugin'
			// )),
			'plugins' => serialize((object) array(
				'plugins' => array(
					'test-plugin/test-plugin.php' => array(
						'Version' => '1.0',
						'slug' => 'teszt-plugin'
					),
				)
			))
		));
		$request->set_auto_format(false);
		$request->execute();
		print_r(unserialize($request->response()->body)); exit;
	}
}