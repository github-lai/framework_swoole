<?php
namespace Lib;

class Rsp
{
	public $status = 200;
	public $head = array('content-type'=>'text/html','charset'=>'utf-8');//要求所有的key都是小写
	public $content = null;

	function __construct($content, $status=200){
		$this->status = $status;
		$this->content = $content;
	}

}

?>