<?php

class SmartCache
{
	private $HOST = 'gameredis.roehpu.0001.apse1.cache.amazonaws.com';
	private $redis;
	protected $connected = null;

	public function __construct()
	{
		$this->redis = new Redis();
	}

	protected function load()
	{
		if ($this->redis->isConnected()) {
			return;
		}

		try {
			$connected = $this->redis->connect('gameredis.roehpu.0001.apse1.cache.amazonaws.com',6379, 2);
		} catch (Exception $e) {
			log_message('error', 'redis error ' . $e . $_SERVER['REQUEST_URI']);
		}

		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		$this->redis->setOption(Redis::OPT_PREFIX, 'smartcache:');
		$this->connected = $connected;
	}

	public function __destruct()
	{
		if ($this->redis->isConnected()) {
			$this->redis->close();
		}
	}

	public function save_data($fileName, $data, $expire = '', $timestamp = false)
	{
//		if (null === $this->connected) {
//			$this->load();
//		}
//		if (!$this->connected) {
//			return false;
//		}
//		$data = msgpack_pack($data);
//		if ($timestamp) {
//			$this->redis->set($fileName, $data);
//			$this->redis->expireAt($fileName, $expire);
//		} else {
//			$this->redis->set($fileName, $data, $expire * 60);
//		}
//		//log_message('error', ' redis save_data '.$fileName.' '.$_SERVER['REQUEST_URI']);
//		return true;

		$redis = new Redis();
		$redis -> connect($this->HOST,6379, 2);
		$redis -> set($fileName, $data);
		return true;
	}

	/**
	 * Read from the cache.
	 */
	public function get_data($fileName)
	{
		//log_message('error', ' redis get_data '.$fileName.' '.$_SERVER['REQUEST_URI']);
//		if (null === $this->connected) {
//			$this->load();
//		}
//		if (false === $this->connected) {
//			return false;
//		}
//		$data = $this->redis->get($fileName);
//
//		if (false === $data) {
//			return false;
//		} else {
//			return msgpack_unpack($data);
//		}

		$redis = new Redis();
		$redis ->connect($this->HOST,6379, 2);
		$data = $redis->get($fileName);
		return $data;
	}

	/***
	 * Deletes the cached data file with the given file name
	 */
	public function delete_data($fileName)
	{
		//return $this->redis->delete($fileName);
		$redis = new Redis();
		$redis ->connect($this->HOST,6379, 2);
		return $redis->delete($fileName);
	}


	public function key_exists($key)
	{
		if (null === $this->connected) {
			$this->load();
		}
		if (false === $this->connected) {
			return false;
		}

		return $this->redis->exists($key);
	}

	public function save_hash_data($fileName, $data, $expire = '', $timestamp = false)
	{
		if (null === $this->connected) {
			$this->load();
		}
		if (!$this->connected) {
			return false;
		}

		foreach ($data as $k => &$v) {
			$v = msgpack_pack($v);
		}

		if ($timestamp) {
			$this->redis->hMSet($fileName, $data);
			$this->redis->expireAt($fileName, $expire);
		} else {
			$this->redis->hMSet($fileName, $data);
			$this->redis->expireAt($fileName, time() + $expire * 60);
		}

		return true;
	}

	public function get_hash_data($fileName, $indexes)
	{
		if (null === $this->connected) {
			$this->load();
		}
		if (false === $this->connected) {
			return false;
		}
		$data = $this->redis->hMGet($fileName, $indexes);

		if (false === $data) {
			return false;
		} else {
			foreach ($data as $k => &$v) {
				$v = msgpack_unpack($v);
			}

			return $data;
		}
	}

	public function get_key_list($query)
	{
		if(null == $this->connected)
		{
			$this->load();
		}
		if(false == $this->connected)
		{
			return false;
		}
		$list = $this->redis-> keys($query);

		return $list;
	}

}
?>
