<?php

class User_model extends CI_Model
{
	protected $table = 'users';
	protected $primaryKey = 'id';

	function __construct()
	{
		parent::__construct();
	}

	public function getUserById($id)
	{
		$user = $this->db->query('SELECT id, account, email, nickname, login_type, created_ts FROM users WHERE id=?', $id);
		if (!$user->num_rows()) {
			return false;
		}
		$user = $user->row_array();
		return $user;
	}

	public function getUser($account, $password, $login = false, $fb_token= false)
	{
		$user = $this->db->query('SELECT id, account, password, nickname, login_type FROM users WHERE account=?', $account);
		if(!$user->num_rows())
		{
			return array();
		}

		$user = $user->row_array();
		if($user['password'] !== md5($password))
		{
			return false;
		}

		return $user;
	}

	public function getUserByEmail($email, $login_type = null)
	{
		//$user = $this->db->query('SELECT id, email, sid_prefix, sid_postfix, sid_ts, first_ts FROM user WHERE email=?', array($email));
		$this->db->select('id, email');
		$this->db->from('users');
		$this->db->where('email', $email);
		if ($login_type) {
			$this->db->where('login_type', $login_type);
		}
		$user = $this->db->get();
		if (!$user->num_rows()) {
			return false;
		}
		$user = $user->row_array();

		return $user;
	}

	//創建帳戶
	public function createUser($email, $password, $account = null, $login_type = 'normal')
	{

		if (!$email) {
			$email = '';
		}

		if ('' != $password) {
			$password = md5($password);
		}

		if (!$account) {
			return false;
		}

		$userData = Array(
			'account' => $account,
			'email' => $email,
			'password' => $password,
			'nickname' => $account,
			'login_type' => $login_type,
			'created_ts' => date('Y-m-d H:i:s'),
		);

		if (!$this->db->insert('users', $userData)) {
			return false;
		}

		return $this->db->insert_id();
	}
}

?>
