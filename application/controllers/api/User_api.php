<?php

class User_api extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
	}

	public  function test()
	{
		$redis = new Redis();
		$redis ->connect('gameredis.roehpu.0001.apse1.cache.amazonaws.com',6379, 2);
		$data = $redis->get("user2");
		echo json_encode($data);
	}

	public function save_redis()
	{
		$this->load->library('SmartCache');

		$data = Array();
		$data['id'] = '1';
		$data['name'] = '1';
		$data['team'] = '1';
		$data['health'] = '100';
		$data['money'] = '200';
		$data['energy'] = '200';

		$location = Array();
		$location['x'] = "0";
		$location['y'] = "30";
		$location['z'] = "40";
		$data['location'] = $location;

		$weapons = Array();
		$weapons[] = Array(
			'id' => '0',
			'current_ammo' => '30',
			'carried_ammo' => '70'
		);
		$weapons[] = Array(
			'id' => '1',
			'current_ammo' => '5',
			'carried_ammo' => '40'
		);
		$data['weapons'] = $weapons;

		$data['roomId'] = '0';
		$cache = $this->smartcache->save_data("user2", json_encode($data), 0, 0);
	}

	public function login_post()
	{
		$account = $_POST["account"];
		$password = $_POST["password"];
		$fb_token = $_POST["fb_token"];

		$account = isset($account) ? $account : false;
		$password = isset($password) ? $password : false;
		$fb_token = isset($fb_token) ? $fb_token : false;
		$user = $this->User_model->getUser($account, $password, true, $fb_token);
		if (!$user) {
			$response = Array();
			$response['status'] = 'error';
			$response['msg'] = '帳號密碼錯誤!';
			echo json_encode($response);
			exit();
			//$this->response(['status' => 'error', 'msg' => '帳號密碼錯誤！'], 401);
		}

		$this->load->library('SmartCache');

		$cache = $this->smartcache->get_data("user" . $user['id']);
		if ($cache) {
			$user['data_in_game'] = $cache;
		}

		echo json_encode($user);
//		$this->response($user);
	}

	public function register_post()
	{
		$account = $_POST["account"];
		$email = $_POST["email"];
		$password = $_POST["password"];

		if (strlen($password) < 6) {
			$response = Array();
			$response['status'] = 'error';
			$response['msg'] = '密碼至少要有8個字';
			echo json_encode($response);
			exit();
			//$this->response(['status' => 'error', 'msg' => '密碼至少要有六個字'], 401);
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$response = Array();
			$response['status'] = 'error';
			$response['msg'] = 'Email格式錯誤';
			echo json_encode($response);
			exit();
//			$this->response(['status' => 'error', 'msg' => 'Email格式錯誤'], 401);
		} elseif ($this->User_model->getUserByEmail($email, 'normal')) {
			$response = Array();
			$response['status'] = 'error';
			$response['msg'] = '此組email已註冊過帳號，請改試試看登入帳號。';
			echo json_encode($response);
			exit();
//			$this->response(['status' => 'error', 'msg' => '此組email已註冊過帳號，請改試試看登入帳號。']);
		}

		$userid = $this->User_model->createUser($email, $password, $account, 'normal');
		$response = Array();
		$response['status'] = 'success';
		$response['msg'] = '註冊成功!';
		$response['userid'] = $userid;
		echo json_encode($response);
//		$this->response(['status' => 'success', 'msg' => '註冊成功！', 'userid' => $userid]);
	}

	public function set_user_data_in_room_post()
	{
		$id = trim(html_entity_decode($_POST["id"]),'"');
		$name = trim(html_entity_decode($_POST["name"]),'"');
		if(isset($_POST["room_id"]))
		{
			$room_id = $_POST["room_id"];
		}
		else
		{
			$room_id = "";
		}

		$data = Array();
		$data['id'] = $id;
		$data['name'] = $name;
		$data['room_id'] = $room_id;

		echo $data['id'] . " " . $data['name'] . " " . $data['room_id'];

		$this->load->library('SmartCache');

		$cache = $this->smartcache->save_data("user" . $data['id'], json_encode($data), 0, 0);
	}

	//清除redis中此房間所有資料
	public function clear_data_in_room_post()
	{
		if(isset($_POST["all_datas"]))
		{
			$all_datas = json_decode(trim(html_entity_decode($_POST["all_datas"]),'"'), true);
			$this->load->library('SmartCache');

			echo trim(html_entity_decode($_POST["all_datas"]),'"');

			$userdatas = $all_datas['userdatas'];



			foreach ($userdatas as &$userdata) {
				echo "user" . trim(html_entity_decode($userdata['id']),'"');
				$this->smartcache->delete_data("user" . trim(html_entity_decode($userdata['id']),'"'));
			}
		}

	}

	//儲存此房間所有資料進到mysql
	public function save_datas_in_room_post()
	{
		if(isset($_POST["all_datas"]))
		{

		}
	}

	//更新登入時間到redis
	public function set_user_login_time()
	{
		if(isset($_POST["id"]))
		{
			$data = Array();
			$data["login_timestamp"] = date('Y-m-d H:i:s', (time() + 15));
			$this->load->library('SmartCache');
			$this -> smartcache -> save_data("user" . $_POST["id"], $data);
		}
	}
}

?>
