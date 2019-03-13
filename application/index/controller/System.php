<?php

namespace app\index\controller;

use think\Request;

use think\Db;

class System extends Allow{



	

	//更新配置函数

	public function update()

	{

		$request=Request::instance();

		$data=$request->param();//一维数组

		foreach ($data as $k => $v) {

			db("sysconfig")->where('varname',$k)->update(['value'=>$v]);		

		}



		$configfile=__DIR__ . '/../../../application/index/config.php';



	    $file=include $configfile;

	    $config=[

	    	'sys_basehost'=>input('sys_basehost'),

	    	'sys_webname'=>input('sys_webname'),

	    	'sys_powerby'=>input('sys_powerby'),

	    	'sys_beian'=>input('sys_beian'),
	    	'sys_stow_limit'=>input('sys_stow_limit'),

	    ];

	    $res=array_merge($file,$config);

		



	    $str='<?php return [';

	    foreach ($res as $key => $value) {

	    	$str .='\''.$key.'\''.'=>'.'\''.$value.'\''.',';

	    };

	    $str .='];';

	    if(file_put_contents($configfile, $str)){



	    	$this->success("配置成功","system/index");

	    }else {

	    	$this->error("配置失败！");

	    }

	    

	}

    public function index(){



    	$db=db("sysconfig")->select();

    	$this->assign("list",$db);



    	$this->assign("config",config("sysconfig"));//分配扩展配置

    	

    	

       //加载模板

    	return view();

    }



}

