<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 2019-11-12
 * Time: 22:53
 */

class CronJob_api
{
	public function __construct()
	{
		parent::__construct();
	}

	public function count_online_user()
	{
		$this->load->library('SmartCache');
		$cache = $this->smartcache->get_key_list("*");
		return $cache;
	}


}

?>
