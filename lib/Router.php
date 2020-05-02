<?php
namespace Lib;

class Router{
	
	static function dispatch($request, $response)
	{
		$path_info = $request->server['path_info'];
		$path_info = str_replace(strtolower(Config::get("root")),"",strtolower($path_info));

		$path_info_array = array();
		
		if($path_info != "" && $path_info != null)
		{
			$path_info = trim($path_info,"/");
			//去掉最后的后缀名
			$arr = explode(".",$path_info);
			if(count($arr)>0){
				$path_info_array = explode("/", $arr[0]);
			}
		}
		
		if($path_info_array == null || count($path_info_array)==0 || implode("",$path_info_array) == ""){
			$path_info_array = array("Home","Index");
		}

		$areas = Config::get("area");

		$seg0 = ucfirst($path_info_array[0]);//取出第一个元素area
		$seg1 = ucfirst($path_info_array[1]);//取出第二个元素controller
		$seg2 = ucfirst($path_info_array[2]);//取出第三个元素action
		$seg3 = $path_info_array[3];//取出第四个元素id

		$area =  $seg0;
		if(substr($seg0,0,1) === '@'){
			$area =  ucfirst(substr($seg0,1));
			$controller = $seg1;
			$action =  $seg2;
			$id =  $seg3;
		}else{
			$area =  'H3';//默认
			$controller = $seg0;
			$action =  $seg1;
			$id =  $seg2;
		}
		
		if($controller == ""){
			$controller = "Home";//默认控制器名
		}
		if($action == ""){
			$action = "Index";//默认Action名
		}

		Config::set("uri","$area/$controller/$action");

		$ctrlfile = ROOTDIR."usr/ctrl/".strtolower($area)."/".$controller.".php";//这句话的strtolower是根据linux的调适而来

		if(!file_exists($ctrlfile)){
			$msg = "dispatch alert : Controller '$area/$controller' not found(check $ctrlfile)";
			Helper::error($msg);
			return new Rsp($msg,404);
		}else{
			$classname = "Ctrl\\$area\\".$controller;

			//权限认证机制
			$authcfg = Config::get("auth");
			
			$auths = array();
			foreach($authcfg as $k=>$v)
			{
				if(Config::get("debug") === 'true'){
					$arr = explode('/',strtolower($k));
					if(count($arr) != 3){
						return new Rsp("路由配置项 $k 不符合要求",500);
					}
				}
				$level1 = "$area/*/*";
				$level2 = "$area/$controller/*";
				$level3 = "$area/$controller/$action";
				if($level3 == $k){
					array_push($auths, $v);//压栈
				}
				if($level2 == $k){
					array_push($auths, $v);
				}
				if($level1 == $k){
					array_push($auths, $v);
				}
			}
			
			while(count($auths) > 0){
				$name = array_pop($auths);//出栈
				$name = "Auth\\".$name;
				
				$auth = new $name($request, $response);

				$check = $auth->valid(Config::get("uri"));
				if($check){
					$auth->allow();
				}else{
					$auth->deny();
				}
			}

			//实例化controller
			$ctrl = new $classname;

			$ctrl->setArea($area);
			$ctrl->setController($controller);
			$ctrl->setTpl($action);
			
			$ctrl->setRequest($request);
			$ctrl->setResponse($response);
			
			$result = $ctrl->$action($id);
			if(!($result instanceof Rsp))
			{
				$result = new Rsp($result,200);
			}
			return $result;
		}
	}

}

?>