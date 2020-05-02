<?php

namespace Lib\IBase;

interface IAuth 
{  
	//是否通过校验
	function valid($act);
	//通过校验该怎么做
	function allow();
	//未通过校验该怎么做
	function deny();

}  


?>