<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Company extends Model {
	
    
    //获取company表总数量
    public function getDataTota(){
    	$db_num=db("company")->field("count(id) as c_num")->select();
        foreach ($db_num as $key => $value) {
           $c_num=$value['c_num'];
        }
    	return $c_num;
    }
    //获取company表MID为0的数量，未使用总数
    public function getDataZero(){
        $db_num=db("company")->where("mid",0)->field("count(id) as c_num")->select();
        foreach ($db_num as $key => $value) {
           $c_num=$value['c_num'];
        }
        return $c_num;
    }

    //获取company表MID不为0的数量，已经使用总数
    public function getDataOne(){
        $db_num=db("company")->where("mid",'<>',0)->field("count(id) as c_num")->select();
        foreach ($db_num as $key => $value) {
           $c_num=$value['c_num'];
        }
        return $c_num;
    }
    
}
