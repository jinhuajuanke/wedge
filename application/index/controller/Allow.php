<?php



// 后台文件继承的登录文件

// @file Allow.php

// @date 2019.2.19

// @author yajun Li<312690422@qq.com>



namespace app\index\controller;

use think\Controller;

use think\Request;

use think\Db;

header("Content-type:text/html;charset=utf8");

class Allow extends Controller{
    public function __construct()
    {
    	parent::__construct();
        $db_s=Db::name("admin")->field('status')->find(session('userid'));      
    	if($db_s['status']!=1 || !session('userid')){
    		$this->error("请先登录！",'Login/index');
    	}

    	$this->userid=session("userid");

    	//用户登录后检查权限
    	if(!$this->checkAuth($this->userid)){
    		$this->error("操作权限不够！") ;
    	}

        $db=db("admin")->where("group_id",2)->field('id')->select();
        foreach ($db as $key=>  $value) {
           $id[$key]=$value['id'];
        }
        $group= implode(',', $id);
        Config('group',$group.',1');
    }

    

    public function checkAuth($userid){

    	if(!$userid){

    		return false;

    	}

    	//如果是admin给予一切权限

    	if($userid==1){

            return true;

        }

    	$c=Request::instance()->controller();

    	$a=Request::instance()->action();

    	$con=strtolower($c);

    	$act=strtolower($a);

    	if ($con == 'index' && $act == 'index') {

            return true;

        }

    	$data=Db::name("admingroup")

    	->alias('ad')

    	->where("ag.id",$userid)

    	->join("lj_admin ag","ag.group_id=ad.id")

    	->field("ad.rule")

    	->find();

    	$str=explode(',', $data['rule']);

    	$where['id']=['in',$str];

    	$db=Db::name("permission_menu")->where($where)->select();

    	

    	foreach ($db as $k => $v) {

            

    		if(strtolower($v['controller'])==$con && strtolower($v['action'])==$act){



    			return true;

    		}

    	}

    	return false;

    }

}

