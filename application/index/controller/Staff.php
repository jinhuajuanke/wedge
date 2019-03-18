<?php

namespace app\index\controller;

use think\Db;
use think\Request;
class Staff extends Allow{	

    public function index(){
      
    	$c_tota_num=model("Admin")->getUserConTota();//工作人员数量
    	$this->assign("c_tota_num",$c_tota_num);


    	$c_zero_num=model("Admin")->getUserConZero();//正常使用中数量
    	$this->assign("c_zero_num",$c_zero_num);


    	$c_one_num=model("Admin")->getUserConOne();//禁用中数量
    	$this->assign("c_one_num",$c_one_num);



        $c_num_tota=model("Company")->getDataTota();//数据总数
        $this->assign("c_num_tota",$c_num_tota);


        $c_num_totaz=model("Company")->getDataZero();//未使用的数据总数
        $this->assign("c_num_totaz",$c_num_totaz);

        $c_num_totao=model("Company")->getDataOne();//已经使用的数据总数
        $this->assign("c_num_totao",$c_num_totao);

        //如果是总管理员调用所有数据
        if(session('userid')==1){
            //bd总数
            $db_bd_tota=db("collect_stow")->field("count(stowtypeid) as typeid")->select();

            //b2数量
            $db_bd_btwo=db("collect_stow")->where('stowtypeid',3)->field("count(stowtypeid) as btwo")->select();

            //p1数量
            $db_bd_pone=db("collect_stow")->where('stowtypeid',6)->field("count(stowtypeid) as pone")->select();

        }else {
            //如果是普通管理员
            $db_all=db("admin")->where("p_id",session("userid"))->field('id')->select();
            $img=array();
            foreach ($db_all as $one => $two) {
                $img[$one]=$two['id'];
            }

            //BD总数
            $db_bd_tota=db("collect_stow")->where('mid',implode(',', $img))->field("count(stowtypeid) as typeid")->select();            

            //b2数量
            $db_bd_btwo=db("collect_stow")->where('stowtypeid',3)->where('mid',implode(',', $img))->field("count(stowtypeid) as btwo")->select();

            //p1数量
            $db_bd_pone=db("collect_stow")->where('stowtypeid',6)->where('mid',implode(',', $img))->field("count(stowtypeid) as pone")->select();
        } 

        $db_bd['btwo']=$db_bd_btwo[0]['btwo'];
        $db_bd['pone']=$db_bd_pone[0]['pone'];
        $db_bd['total']=$db_bd_tota[0]['typeid'];
        $this->assign("bd",$db_bd);

       //加载模板
    	return view();

    }

    //加载模板，查看员工账户总数据

    public function showInfos(){

    	$nickname=input("nickname");
    	$orderby=input("orderby");
    	$where='';//搜索变量
    	$order='';//排序变量

    	if(!empty($nickname)){
    		$where['a.nickname']=$nickname;
    	}

    	if(!empty($orderby)){
    		$order=$orderby;
    	}

        if(session("userid")==1){
            $where['a.condition']=1;
            $where['a.group_id']=3;
        }else {

            $where['a.group_id']=3;
            $where['a.condition']=1;
            $where['a.p_id']=session('userid');
        }


    	$db_com=Db::name("admin")
    	->alias('a')
    	->where($where)
    	->join('lj_stat s','a.id=s.mid')
    	->order($order)
    	->paginate(10);       

    	//分配变量
        $this->assign("list",$db_com);
        $this->assign("nickname",$nickname);
    	$this->assign("orderby",$orderby);
    	return view();

    }

    //加载模板，查看员工详细数据

    public function checkTask(){	



    	

    	//从show_infos.html页面获取账户ID

    	$admin_id=input("id");

    	

    	//查询收藏表中的   aid在数据表中是否存在，如果不存在就把收藏表中的数据删除

        $list_stow=Db::name("collect_stow")->field('aid')->select();

        if($list_stow){

            foreach($list_stow as $liow){

                $listcount[]=$liow['aid'];

            }

            for ($i=0; $i < count($listcount); $i++) {

            

                $db_list=Db::name("company")->find($listcount[$i]);

                if(!$db_list){



                    Db::name("collect_stow")->where('aid',$listcount[$i])->delete();

                }

            }

        }

        //通过ajax接收分页变量

        if(Request()->isAjax()){

            session('page',null);//把上一次的session删除

            $name=input('page');//获取分页变量



            session('page',$name);//设置分页session

            return $name;

        }

        //搜索

        $keyword=input('keyword');

        $date='';

    	$time=input("time");

    	if(!empty($time)){

    		$date=$time;

    	}

        $typename=input('typename');



        $search=array();

        $bd=array();//bd状态数量

        if(!empty($keyword)){

            $search['company_name']=['like','%'.$keyword.'%'];

        }

        if(!empty($typename)){

            $search['stowtype']=$typename;

            $bd['stowtypeid']=$typename;

        }

         $search['co.mid']=$admin_id;



        //查询

        $join = [

            ['lj_collect_stowtype st','st.id=co.stowtypeid','LEFT'],

            ['lj_company com','com.id=co.aid','LEFT'],

           

        ];

        $field=['*','co.id'=>'co_id','com.id'=>'com_id'];

        $data=Db::name("collect_stow")->alias('co')->join($join)->field($field)->where($search)->whereTime('add_time',$date)->order('co_id desc')->paginate(session('page'),false,['query' => request()->param(),'list_rows'=>10]);

         //var_dump(Db::name("collect_stow")->getLastsql());

        

        //分配分页变量

        if(session('page')==20){

            $this->assign("btn2","btn-success");

        } else {

            $this->assign("btn2","");

        }

        if(session('page')==10){

            $this->assign("btn1","btn-success");

        } else {

            $this->assign("btn1","");

        }

        if(session('page')==30){

            $this->assign("btn3","btn-success");

        } else {

            $this->assign("btn3","");

        }

        if(session('page')==50){

            $this->assign("btn5","btn-success");

        } else {

            $this->assign("btn5","");

        }

        if(session('page')==100){

            $this->assign("btn10","btn-success");

        } else {

            $this->assign("btn10","");

        }

        //获取分类数据

        $data_stow=Db::name("collect_stowtype")->order('type_name asc')->select();

        $this->assign("datastow",$data_stow);//搜索条件中下拉菜单



        //获取账户已经添加BD状态的条数

        $bd['mid']=$admin_id;



        $db_bd=Db::name("collect_stow")->where($bd)->whereTime('add_time',$date)->field("count(stowtypeid) as typeid")->group('mid')->select();

        $bd_num='';

        foreach ($db_bd as $key => $value) {

        	$bd_num=$value['typeid'];

        }

        if($bd_num!=''){

        	$bd_num=$bd_num;

        }else {

        	$bd_num=0;

        }

        $this->assign("bd_num",$bd_num);//分配变量，BD状态数量



        $model=model("Admin")->getUserInfo($admin_id);//获取账户信息

        $this->assign("nickname",$model['nickname']);//分配变量，账户昵称

    	

        //分配数据变量

        $this->assign("list",$data);//循环遍历表格

        $this->assign("admin_id",$admin_id);

        $this->assign("keyword",$keyword);//分配变量，显示搜索条件关键词

        $this->assign("date",$date);//分配变量，显示搜索条件日期

        $this->assign("stowtype",$typename);//分配变量，显示搜索条件BD状态

        $this->assign("page_total",$data->total());//分配变量，收藏数据总条数

    	return view();

    }

}

