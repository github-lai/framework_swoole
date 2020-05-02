<?php
namespace Lib;

class Auth
{
	public $request = null;
	public $response = null;

	function __construct($request,$response){
		$this->request = $request;
		$this->response = $response;
	}

	function reg($name,$array)
	{
		$this->response->cookie($name, serialize($array),time()+3600*24*30,'/');
	}

	function check($name)
	{
		return isset($this->request->cookie[$name]);
	}

	function get($name)
	{
		return unserialize($this->request->cookie[$name]);
	}

	function remove($name)
	{
		$this->response->cookie($name,"",time()-1,'/');
	}
	
}  

?>