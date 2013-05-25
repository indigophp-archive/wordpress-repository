<?php


namespace Repository;

class Controller_Repository extends \Controller_Rest {

	protected $format = "serialized";

	protected $param;

	public function before($data = null)
	{
		parent::before($data);

		$response = new \stdClass;

		if (\Input::param('action', false) && \Input::param('auth', false))
		{
			if (\Input::param('request', false))
			{
				$this->param = (array)@unserialize(stripslashes(\Input::param('request', '')));
			}
		}
		else
		{
			$response->error = 'Action or Auth is missing';
			return $this->response($response);
		}

		if (is_array(\Input::param('auth')))
		{
			if (\Input::param('auth._plain') == true)
			{
				$user = \Auth::login(\Input::param('auth.user', ''), \Input::param('auth.pass', ''));

				if ($user and \Input::param('action') == 'repository_login')
				{
					$response->success = 'Login successfully';
					$response->pass = \Auth::instance()->hash_password(\Input::param('auth.pass', ''));
					$response->info = array('login' => 'authentication');
					return $this->response($response);
				}
				else
				{
					$response->error = 'Login failed';
					$response->info = array('login' => 'authentication');
					return $this->response($response);
				}
			}
			else
			{
				$user = \Model\Auth_User::query()
					->where_open()
						->where('username', '=', \Input::param('auth.user', ''))
						->or_where('email', '=', \Input::param('auth.user', ''))
					->where_close()
					->where('password', '=', \Input::param('auth.pass', ''))
					->get_one();

				if ( ! $user)
				{
					$response->error = 'Authentication is wrong';
					$response->info = array('login' => 'authentication');
					return $this->response($response);
				}
			}
		}
		else
		{
			$response->error = 'Authentication is missing';
			return $this->response($response);
		}
	}
}