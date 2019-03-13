<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Admin extends Model {
	//获取账户单个数据信息
    public function getUserInfo($id){
    	$db=db("admin")->where('id',$id)->find();
        return $db;
    }
    
    //获取admin表账户工作人员数量
    public function getUserConTota(){
    	if(session('userid')==1){
            $where['group_id']=['in','2,3'];
        }else {
            $where['group_id']=3;
            $where['p_id']=session('userid');
        }
    	$db_num=db("admin")->where($where)->field("count(id) as c_num")->select();
    	$c_num='';
    	foreach ($db_num as $key => $value) {
    		$c_num=$value['c_num'];
    	}
    	return $c_num;
    }
    //获取admin表禁用状态数量
    public function getUserConZero(){
    	$where['condition']=0;
        if(session('userid')==1){
            $where['group_id']=['in','2,3'];
        }else {
            $where['group_id']=3;
            $where['p_id']=session('userid');
        }
    	$db_num=db("admin")->where($where)->field("count(id) as c_num")->select();
    	$c_num='';
    	foreach ($db_num as $key => $value) {
    		$c_num=$value['c_num'];
    	}
    	return $c_num;
    }
    //获取admin表正常使用状态数量
    public function getUserConOne(){
    	$where['condition']=1;
    	if(session('userid')==1){
            $where['group_id']=['in','2,3'];

        }else {
            $where['group_id']=3;
            $where['p_id']=session('userid');

        }
    	$db_num=db("admin")->where($where)->field("count(id) as c_num")->select();
    	$c_num='';
    	foreach ($db_num as $key => $value) {
    		$c_num=$value['c_num'];
    	}
    	return $c_num;
    }

    //删除账户单个数据
    public function del($id){
    	$db_cs=db("collect_stow")->where('mid',$id)->find();
    	if(!empty($db_cs)){
    		db("collect_stow")->where("mid",$id)->delete();
    	}

    	$db_cst=db("collect_stowtype")->where('mid',$id)->find();
    	if(!empty($db_cst)){
    		db("collect_stow")->where("mid",$id)->delete();
    	}

    	$db_cst=db("company")->where('mid',$id)->find();
    	if(!empty($db_cst)){
    		db("company")->where('mid',$id)->update(['mid'=>0,'stowtype'=>0]);
    	}
    	// db("collect_stow")->where('mid',$id)->delete();
    }
    
}
