<?php
/**
*系统用户组管理
*@file agroup.php
*@date 2019/2/20
*@author Yajun Li<312690422@qq.com>
*/
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Validate;
use think\Db;
class Agroup extends Allow{
	
    public function index(){
    	$data=db("admingroup")->where('id','not in',1)->order('id asc')->select();
    	//分配变量
    	$this->assign("data",$data);
       //加载模板
    	return view();
    }
    //加载添加页面
    public function add(){
        
    	return view();
    }
    //执行添加程序
    public function insert(){
    	//获取请求的参数
    	$result=Request::instance();
    	$list=$result->param();
    	//验证表单
    	$rule=[

    		['groupname', 'require','组名称必须填写']

    	];
    	$data=[
    		'groupname' => $list['groupname'],
    		'notes' => $list['notes']
    	];
    	$validate=new Validate($rule);
    	$val_res=$validate->check($data);
    	if(!$val_res){
    		$this->error($validate->getError());
    	}
    	//插入数据
    	$data_in=db("admingroup")->insert($list);
    	if($data){
    		$this->success("成功添加数据","agroup/index");
    	}
    }
    //加载修改用户组页面
    public function edit($id){
    	$table=db("admingroup")->where('id',$id)->find();
    	//分配变量
    	$this->assign("list",$table);
    	return view();
    }
    //执行修改用户组
    public function update(){
    	//获取数据
    	$result=Request::instance();
    	
    	$data=$result->param();
    	
    	$data_up=db("admingroup")->where('id',$data['id'])->update(['groupname'=>$data['groupname'],'notes'=>$data['notes'] ]);

    	if($data_up){
    		$this->success("更新数据成功","agroup/index");
    	} else {
    		$this->error("更新数据失败");
    	}

    }
    //执行删除
    public function del(){
        $id=Request::instance()->param('id');
        $data_del=db("admingroup")->delete($id);
        if($data_del){
            $this->success("删除数据成功","agroup/index");
        } else {
            $this->error("删除数据失败");
        }
    }
    //加载设置权限页面
    public function permission(){
        //获取权限数据
        $db_rule=Db::name("admingroup")->field('rule')->find(input('id'));
        $db_rule_arr=explode(',', $db_rule['rule']);
        
        //获取内嵌循环数据
        $db=Db::name("permission_menu")->where("parent_id",0)->select();
        $data='';
        foreach ($db as $k => $v) {
            $db2=Db::name("permission_menu")->where("parent_id",$v['id'])->select();
            $data2='';
            foreach ($db2 as $k2 => $v2) {
                $data2[$k2]['id']=$v2['id'];
                $data2[$k2]['per_name']=$v2['per_name'];

            }
            $data[$k]['id']=$v['id'];
            $data[$k]['per_name']=$v['per_name'];
            $data[$k]['child']=$data2;
        }
        //获取用户组名称
        $typename=Db::name("admingroup")->field('groupname')->find(input('id'));
        //分配变量
        $this->assign("user_group_id",input('id'));//分配用户组ID
        $this->assign("user_group_name",$typename['groupname']);//分配用户组名称
        $this->assign("list",$data);
        $this->assign("rule",$db_rule_arr);
        return view();
    }
    //执行设置权限
    public function create_per(){
        //获取请求的数据
        $request=Request::instance();
        $data=$request->only('rule,id');
        $list=implode(',',$data['rule']);
        
        //更新组管理表
        $db_update=Db::name("admingroup")->where('id',$data['id'])->update(['rule'=>$list]);
        if($db_update){
            $this->success("成功设置权限！","agroup/index");
        }else {
            $this->error("设置权限失败！");
        }
    }
}
