<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Validate;
header("Content-type:text/html;charset=utf8");
class Permission extends Allow{
	//加载权限列表页面
    public function index(){

    	//从数据库获取数据
    	$sql_data=Db::name("permission_menu")->where('parent_id',0)->select();
    	$data='';
    	foreach ($sql_data as $k => $v) {
    		$sql_data2=Db::name("permission_menu")->where('parent_id',$v['id'])->select();
    		$data2='';
    		foreach ($sql_data2 as $k2 => $v2) {
    			$data2[$k2]['id']=$v2['id'];
	    		$data2[$k2]['per_name']=$v2['per_name'];
	    		$data2[$k2]['controller']=$v2['controller'];
	    		$data2[$k2]['action']=$v2['action'];
	    		$data2[$k2]['status']=$v2['status'];
    		}
    		$data[$k]['id']=$v['id'];
    		$data[$k]['per_name']=$v['per_name'];
    		$data[$k]['controller']=$v['controller'];
    		$data[$k]['action']=$v['action'];
    		$data[$k]['status']=$v['status'];
    		$data[$k]['child']=$data2;
    	}

    	//分配变量
    	$this->assign("list",$data);
       //加载模板
    	return $this->fetch();
    }
    //加载添加权限页面
    public function add(){
    	//从数据库获取权限目录
    	$res=Db::name("permission_menu")->where('parent_id',0)->select();
    	$data="";
		foreach($res as $k => $v){
            //2级
			$res2 = Db::name('permission_menu')->where("parent_id",$v['id'])->select();
			$data2="";
			foreach($res2 as $k2 => $v2){
				$data2[$k2]['id']=$v2['id'];
				$data2[$k2]['per_name']=$v2['per_name'];
			}
			$data[$k]['id']=$v['id'];
			$data[$k]['per_name']=$v['per_name'];
			$data[$k]['child']=$data2;
		}
    	//分配变量
    	$this->assign("list",$data);
    	return view();
    }
    //执行添加权限目录
    public function insert(){
    	//获取请求的数据
    	$request=Request::instance();
    	$data=$request->param();
    	//验证数据
    	$rule=[
    		['parent_id','number','目录必须是数字'],
    		['per_name','require|max:30','名称必须填写|超出名称最大值'],
    		['controller','alpha|max:30','控制器必须是字母|超出控制器最大值'],
    		['action','alphaDash|max:30','方法必须是字母和数字，下划线_及破折号-|方法超出最大值']

    	];
    	$scene=[
    		'parent_id'=>$data['parent_id'],
    		'per_name'=>$data['per_name'],
    		'controller'=>$data['controller'],
    		'action'=>$data['action'],
    	];
    	$validate=new Validate($rule);
    	if(!$validate->check($scene)){
    		$this->error($validate->getError());
    	}
    	//插入数据
    	$sql_in=Db::name("permission_menu")->insert($data);
    	if($sql_in){
    		$this->success("成功添加权限目录",'permission/index');
    	}else {
    		$this->error("添加权限目录失败");

    	}
    }
    //加载修改页面
    public function edit(){
    	//获取请求的数据ID
    	$id=input('id');
    	//获取权限目录数据做下拉菜单
    	$db_list=Db::name("permission_menu")->where('parent_id',0)->field('id,per_name')->select();
    	$data='';
    	foreach ($db_list as $key => $value) {
    		$db_list2=Db::name("permission_menu")->where("parent_id",$value['id'])->select();
    		$data2='';
    		foreach ($db_list2 as $key2 => $value2) {
    			$data2[$key2]['id']=$value2['id'];
    			$data2[$key2]['per_name']=$value2['per_name'];
    		}
    		$data[$key]['id']=$value['id'];
    		$data[$key]['per_name']=$value['per_name'];
    		$data[$key]['child']=$data2;
    	}
    	//获取数据库里面的数据添加到input.value里面
    	$db_menu=Db::name("permission_menu")->where('id',$id)->find();
    	//分配$db_menu变量到input.value里面
    	$this->assign("list_find",$db_menu);
    	//分配权限目录下拉菜单
    	$this->assign('list_menu',$data);
    	return view();
    }
    //修改权限
    public function update(){
    	//获取请求的数据
    	$request=Request::instance();
    	$data=$request->param();
    	//验证数据
    	$rule=[
    		['id','number','目录必须是数字'],
    		['parent_id','number','目录必须是数字'],
    		['per_name','require|max:50','名称必须填写|超出名称最大值'],
    		['controller','alpha|max:20','控制器必须是字母|超出控制器最大值'],
    		['action','alpha|max:20','控制器必须是字母|超出控制器最大值'],
    		['status','number','状态必须是数字'],


    	];
    	$scene=[
    		'id'=>$data['id'],
    		'parent_id'=>$data['parent_id'],
    		'per_name'=>$data['per_name'],
    		'controller'=>$data['controller'],
    		'action'=>$data['action'],
    		'status'=>$data['status'],
    	];
    	$validate=new Validate($rule);
    	if(!$validate->check($scene)){
    		$this->error($validate->getError());
    	}
    	//插入数据
    	$sql_in=Db::name("permission_menu")->update($data);
    	if($sql_in){
    		$this->success("成功修改权限目录",'permission/index');
    	}else {
    		$this->error("修改权限目录失败");

    	}
    }
    //执行删除权限
    public function del(){
    	$id=input('id');
    	$db=Db::name("permission_menu")->delete($id);
    	if($db){
    		$this->success("成功删除权限目录",'permission/index');
    	}else {
    		$this->error("删除权限目录失败");

    	
    	}
    }


}
