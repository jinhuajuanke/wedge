<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
class Writein extends Allow{
	//加载添加数据页面
    public function addData(){
    	//从数据库调用分类
    	$db=Db::name("collect_stowtype")->where('mid',session('userid'))->select();
    	//分配分类变量
    	$this->assign("list",$db);
       //加载模板
    	return view();
    }
    //执行添加
    public function insertData(){
    	$request=Request::instance();
    	$data=$request->param();//获取请求的所有参数
    	$data['mid']=session('userid');
    	$db=Db::name("company")->insert($data);//执行添加到company表

    	if($db){
    		//$map是查询条件，查询刚才插入到company表中的ID
    		$map['company_name']=$data['company_name'];
    		$map['mid']=$data['mid'];
    		$map['linkman']=$data['linkman'];

    		$db_id=Db::name("company")->where($map)->find();
    		$company_id=$db_id['id'];//获取刚刚插入的ID
    		//获取ID后插入到collect_stow表
    		$db_in=Db::name("collect_stow")->insert(['mid'=>$data['mid'],'stowtypeid'=>$data['stowtype'], 'aid'=>$company_id,'add_time'=>time(),'bd_time'=>time()]);
    		if($db_in){
    			$this->success("添加成功！","collect/stow");

    		}else {
    			$this->error("添加失败！");
    		}
    	}else {
    		$this->error("添加失败！");
    	}
    }
}
