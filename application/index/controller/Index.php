<?php

/**
*@file inddex.php
*@date 2019/2/19
*@author yajun Li<312690422@qq.com>
*/

namespace app\index\controller;
use think\Request;
use think\Db;
use think\Cache;
class Index extends Allow {

    public function index(){
       
    	$request=Request::instance();
    	$data=$request->server();
    	$version = Db::query('SELECT VERSION() AS ver');    	

    	$config=[
    		'host'=>$data['HTTP_HOST'],
    		'server_soft'=>$data['SERVER_SOFTWARE'],
    		'php_version'=>PHP_VERSION,
    		'upload_max_filesize'=>ini_get('upload_max_filesize'),
    		'mysql_version'=>$version[0]['ver'],
    		'server_os'=>PHP_OS,//服务器操作系统
    	];
    	$this->assign("config",$config);
        
        $db=db("company")->field("count(id) as total")->select();//二维数组
        foreach ($db as $key => $value) {
            $num=$value['total'];
        }

        $this->assign("agent_num",$num);//company表总数


        $db_mid=db("company")->where('mid',0)->field("count(id) as total")->select();//二维数组
        foreach ($db_mid as $k => $v) {
            $num1=$v['total'];
        }
        $this->assign("wei_num",$num1);//company表未使用总数


        $db_stow=db("collect_stow")->where('stowtypeid',3)->field("count(id) as total")->select();//二维数组
        foreach ($db_stow as $k1 => $v1) {
            $num2=$v1['total'];
        }
        $this->assign("b2_num",$num2);//b2状态总数


        $db_stow1=db("collect_stow")->where('stowtypeid',6)->field("count(id) as total")->select();//二维数组
        foreach ($db_stow1 as $k2 => $v2) {
            $num3=$v2['total'];
        }
        $this->assign("p1_num",$num3);//p1状态总数
        

        $db_mid_stow=db("collect_stow")->field("count(id) as stowtota")->select();
        foreach ($db_mid_stow as $vo) {
            $stownum=$vo['stowtota'];
        }  
        $this->assign("stownum",$stownum);//获取当前会员ID的收藏总数

       
        $map['stowtypeid']=null;
        $db_mid_wei=db("collect_stow")->where($map)->field("count(id) as stowtota")->select();      

        foreach ($db_mid_wei as $vos) {
            $stownums=$vos['stowtota'];
        }
        $this->assign("stownums",$stownums);//获取当前会员ID的未分类数量



        $db_mid_zai=db("admin")->where('status',1)->field("count(id) as stowtota")->select();
        foreach ($db_mid_zai as $zai) {
            $zai_num=$zai['stowtota'];
        }
        $this->assign("zai_num",$zai_num);//获取当前在线会员ID的数量

        
       
       //加载模板

    	return view();

    }



}

