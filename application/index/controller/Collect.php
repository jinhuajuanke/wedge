<?php

namespace app\index\controller;

use think\Controller;

use think\Request;

use think\Validate;

use think\Db;

use think\Paginator;

header("Content-type:text/html;charset=utf8");

class Collect extends Allow{

    

    public function index(){

        //通过ajax接收分页变量

        if(Request()->isAjax()){

            session('page',null);//把上一次的session删除

            $name=input('page');//获取分页变量

            session('page',$name);//设置分页session

            return $name;

        }

        //搜索

        $keyword=input('keyword');

        $search=array();

        if(!empty($keyword)){

            $search['company_name']=['like','%'.$keyword.'%'];

        }

         $search['co.mid']=0;

        //查询

        $join = [

            ['lj_collect_stowtype st','st.id=co.stowtype','LEFT'],

        ];

        $field=['*','co.id'=>'co_id'];

        $data=Db::name("company")->alias('co')->join($join)->field($field)->where($search)->order('co_id asc')->paginate(session('page'),false,['query' => request()->param(),'list_rows'=>10]);

         //var_dump(Db::name("company")->getLastsql());

        

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

        //分配数据变量

        $this->assign("list",$data);

        $this->assign("keyword",$keyword);

        $this->assign("num_total",$data->total());



       //加载模板

        return $this->fetch();

    }

    //在客户公海页面执行放入我的客户中

    public function putInCollect(){

        $data['aid']=input("aid");
        $data['mid']=session('userid');
        $data['add_time']=time();
        $data['source']='客户公海';       

        $check_db=Db::name("collect_stow")->where('aid',$data['aid'])->find();
        if(!$check_db){
            $efg=db("stat")->where('mid',session('userid'))->field("stat_stow,stat_note")->find();
            $nolink=$efg['stat_stow']-$efg['stat_note'];
            $stow_li=config("sys_stow_limit");//3,未设置BD状态的数量超出限额
            
            if($nolink<=$stow_li){
                $db=Db::name("collect_stow")->insert($data);         
                if($db){
                    //更新company中MID
                    Db::name("company")->where('id',$data['aid'])->update(['mid'=>$data['mid']]);

                    //信息统计收藏数量加1
                    Db::name("stat")->where('mid',session('userid'))->setInc('stat_stow',1);
                    $this->redirect("collect/stow");
                } else {

                    $this->error("添加失败");

                }
            }else {
                $this->error("您还有很多客户没有联系！");
            }

            

        }else {

            $this->error("这条数据已经被使用！");

        }

        

    }

    //数据列表备注

    public function notes(){

        //获取数据

        $result=Request::instance();

        $data=$result->only('notes,id,action');



        //验证数据

        $rule=[

            ['id', 'number','参数错误'],

            ['notes','length:0,255','最多128个汉字'],

        ];

        $sence=[

            'notes'=>$data['notes'],

        ];

        $validate=new Validate($rule);

        $res_val=$validate->check($sence);

        if(!$res_val){

            $this->error($validate->getError());

        }



        //更新数据库

        $data_up=Db::table("lj_company")->update(['id'=>$data['id'],'mid'=>session('userid'),'notes' => $data['notes']]);//返回受影响行数

        if($data_up){



            

            $this->redirect("collect/index");



        } else {

            $this->error("添加失败");

        }

    }

    //执行添加备注，客户公海页面

    public function stownotes(){

        //获取数据

        $result=Request::instance();

        $data=$result->only('notes,id,action');



        //验证数据

        $rule=[

            ['id', 'number','参数错误'],

            ['notes','length:0,255','最多255个字节'],

        ];

        $sence=[

            'notes'=>$data['notes'],

        ];

        $validate=new Validate($rule);

        $res_val=$validate->check($sence);

        if(!$res_val){

            $this->error($validate->getError());

        }

        //如果company表中的备注为空，统计才加1

        $db_notes=db("company")->where('id',$data['id'])->field('notes')->find();

        if(empty($db_notes['notes'])){

             //信息统计备注数量加1

            Db::name("stat")->where('mid',session('userid'))->setInc('stat_note',1);

        }

        if($data['notes']==''){

            //如果提交变量里面没字就减1

            Db::name("stat")->where('mid',session('userid'))->setDec('stat_note',1);



        }

        //更新数据库

        $data_up=Db::table("lj_company")->update(['id'=>$data['id'],'mid'=>session('userid'),'notes' => $data['notes']]);//返回受影响行数

        if($data_up){

           echo "<script>javascript:history.back(-1);</script>";

        } else {

            $this->error("添加失败");

        }

    }

    //加载收藏页面

    public function stow(){
        //把收藏表中的所有b2收集起来放入统计表中
        $dbstr=db("collect_stow")->where('stowtypeid',3)->where('mid',session("userid"))->field('count(id) as lkin')->select();
        foreach ($dbstr as $v5) {
            $xin=$v5['lkin'];
        }
        db("stat")->where("mid",session('userid'))->update(['stat_b2'=>$xin]);

        //把收藏表中的所有p1收集起来放入统计表中
        $dbstrs=db("collect_stow")->where('stowtypeid',6)->where('mid',session("userid"))->field('count(id) as lkin')->select();
        foreach ($dbstrs as $v6) {
            $xins=$v6['lkin'];
        }
        db("stat")->where("mid",session('userid'))->update(['stat_p1'=>$xins]);

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
        $typename=input('typename');
        $source=input('source');
        $time=input('time');
        $search=array();
        if(!empty($keyword)){
            $search['company_name']=['like','%'.$keyword.'%'];
        }

        if(!empty($typename)){
            $search['stowtype']=$typename;
        }
        if(!empty($source)){
            $search['source']=$source;
        }

        $date='month';

        if(!empty($time)){
            $date=$time;
        }

        $search['co.mid']=session("userid");

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

        $db_mid_stow=db("collect_stow")->where("mid",session('userid'))->field("count(id) as stowtota")->select();
        foreach ($db_mid_stow as $vo) {
            $stownum=$vo['stowtota'];
        }        
        $this->assign("stownum",$stownum);//获取当前会员ID的收藏总数

        $map['mid']=session('userid');
        $map['stowtypeid']=null;
        $db_mid_wei=db("collect_stow")->where($map)->field("count(id) as stowtota")->select();      

        foreach ($db_mid_wei as $vos) {
            $stownums=$vos['stowtota'];
        }
        $this->assign("stownums",$stownums);//获取当前会员ID的未分类数量

        //分配数据变量
        $this->assign("list",$data);//循环遍历表格
        $this->assign("keyword",$keyword);//显示搜索条件
        $this->assign("stowtype",$typename);//显示搜索条件
        $this->assign("date",$date);//显示搜索条件
        $this->assign("source",$source);//显示搜索条件
        //加载模板

        return view();

    }

    

    //从修改分类函数中跳转到加载BD状态页面

    public function upClass(){

       

        //从数据库获取数据

        $sel_data=Db::name("collect_stowtype")->select();

        

        $this->assign("dclass",$sel_data);

        $this->assign("aid",0);

        $this->assign("stowtypeid",0);

        $this->assign("action",'upclass');

        //分配变量

        $this->assign("list",$sel_data);

        return $this->fetch('collect/mstowtype');

    }

    

     

    //收藏分类管理

    public function mstowtype(){

        //从数据库获取数据

        $sel_data=Db::name("collect_stowtype")->order('type_name asc')->select();

        

        $this->assign("list",$sel_data); //分配变量，放在表格里面

        return view();

    }





    //加载添加BD状态页面

    public function addstow(){

        

        return $this->fetch();

    }

    //执行添加BD状态

    public function insertStowType(){

        //获取提交的数据

        $request=Request::instance();

        $typenames=$request->only('typename');

        

        //验证数据

        $rule=[

            ['typename','require']

        ];

        $scene=[

            'typename' => $typenames['typename'],

        ];

        $validate= new Validate($rule);

        if(!$validate->check($scene)){

            $this->error($validate->getError());

        }

        $data=[

            'type_name'=>$scene['typename'],

           

        ];

        //插入数据库

        $exe_in=Db::name("collect_stowtype")->insert($data);

        if($exe_in){

            $this->success("添加分类成功","collect/mstowtype");

        }else {

            $this->error("添加分类失败");

        }

    }

    //执行删除分类

    public function delClass(){

        //从URL获取ID

        $request=Request::instance();



        $id=$request->only('id');



        if(is_numeric($id['id'])){

            

            $exc_del=Db::name("collect_stowtype")->delete($id);

            if($exc_del){

                $this->success("删除分类成功","collect/mstowtype?aid=0&typeid=0&action=yes");

            }else {

                $this->error("删除分类失败");

            }

        }

    }

    //加载修改分类页面

    public function editClass(){

        $id=input('id');

        $db=Db::name("collect_stowtype")->where('id',$id)->field('id,type_name')->find();

        //分配变量

        $this->assign("list",$db);

        return view();

    }

    //执行修改分类

    public function updateClass(){



        //获取修改数据的ID

        $id=input("id");

        $typename=input("typename");

        $db=Db::name("collect_stowtype")->where('id',$id)->update(['type_name'=>$typename]);

        if($db){

                $this->success("修改分类成功","collect/mstowtype");

            }else {

                $this->error("修改分类失败");

            }

    }

    //从收藏页面加载【移动BD状态】页面

    public function sowtype(){

        //把aid,typeid从请求中获取到

        $request=Request::instance();

        $co_id=$request->only('id,aid,typeid');

        

        $this->assign("id",$co_id['id']);

        $this->assign("aid",$co_id['aid']);

        $this->assign("stowtypeid",$co_id['typeid']);

        //从数据库获取分类数据做下拉菜单

        $sel_data=Db::name("collect_stowtype")->order('type_name asc')->select();

        $this->assign("dclass",$sel_data);

       

        return $this->fetch();

    }

    //执行移动BD状态

    public function moveType(){

        //获取接收的数据

        $request=Request::instance();

        $data_req=$request->param();



        //验证数据

        $rule=[

            

            ['id','number'],

            ['aid','number'],

            ['mid','number'],

            ['stowtypeid','number','请选择分类'],

        ];

        $scene=[

            

            'id'=>$data_req['id'],

            'aid'=>$data_req['aid'],

            'mid'=>$data_req['mid'],

            'stowtypeid'=>$data_req['stowtypeid'],

        ];

        $validate=new Validate($rule);

        if(!$validate->check($scene)){

            $this->error($validate->getError());

        }

        $scene['bd_time']=time();

        //调用collect_stow表中的收藏ID，如果是null就加1

        $data_usp=Db::name("collect_stow")->where('id',$data_req['id'])->field('stowtypeid')->find();

        if(empty($data_usp['stowtypeid'])){

             //当前员工信息统计分类数量加1

            Db::name("stat")->where('mid',session('userid'))->setInc('stat_class',1);

        }

        

        

        //更新collect_stow表

        $data_up=Db::name("collect_stow")->where('id',$data_req['id'])->update($scene);

        //更新company表

        $data_ups=Db::name("company")->where('id',$data_req['aid'])->update(['stowtype' => $data_req['stowtypeid'],'mid'=>$data_req['mid']]);



        if($data_up && $data_ups){

            

           

            $this->redirect('collect/stow');



        }else {

            $this->error("添加BD状态失败");

        }

    }

    //从我的客户页面删除单个数据

    public function delStow(){
        //接收数据
        $request=Request::instance();
        $data=$request->only('id');

        if(is_numeric($data['id'])){

             //通过collect_stow表中的id获取company中的aid
            $db_stow=Db::name("collect_stow")->field('aid')->find($data['id']);

            //删除收藏表的数据
            $data_del=Db::name("collect_stow")->delete($data['id']);

            if($data_del){

                //还要更新company表中的数据MID、stowtype、notes，
                Db::name("company")->where('id',$db_stow['aid'])->update(['mid'=>0,'stowtype'=>0]);

                //信息统计各个字段数量减1
                $db_stat=db("stat")->where("mid",session("userid"))->find();

                if($db_stat['stat_note']!=0){
                    Db::name("stat")->where('mid',session('userid'))->setDec('stat_note',1);
                }

                if($db_stat['stat_stow']!=0){
                    Db::name("stat")->where('mid',session('userid'))->setDec('stat_stow',1);
                }

                if($db_stat['stat_class']!=0){
                    Db::name("stat")->where('mid',session('userid'))->setDec('stat_class',1);
                }

                if($db_stat['stat_b2']!=0){
                    Db::name("stat")->where('mid',session('userid'))->setDec('stat_b2',1);
                }

                $this->success("成功删除收藏数据！","collect/stow");

            }else {

                $this->error("删除收藏数据失败！");

            }

        }

    }

    //从数据列表页面删除数据

    public function delIndexData(){

        //接收数据

        $request=Request::instance();

        $data=$request->only('id');

        if(is_numeric($data['id'])){

            //通过id删除数据表的数据

            $data_del=Db::name("company")->delete($data['id']);

            if($data_del){

                //接着删除收藏表中的数据

                Db::name("collect_stow")->where('aid',$data['id'])->delete();

                $this->success("成功删除数据！",'collect/index');

            }else {

                $this->error("删除数据失败！");

            }

        }

    }

    //多条数据删除

    public function delcompany_do(){

        //获取请求的数据

        $request=Request::instance();

        $data=$request->only('qstr');

        $ex_arr=explode('`',$data['qstr']);

        

        for ($i=0; $i < count($ex_arr); $i++) { 

            $res_del=Db::name("company")->where('id',$ex_arr[$i])->delete();

            if($res_del){

                //接着删除收藏表中的数据

                Db::name("collect_stow")->where('aid',$ex_arr[$i])->delete();



            }

        }

        $this->success("成功删除".count($ex_arr)."条数据！",'collect/index');



    }

    //在数据列表页面中把多条数据放入收藏列表

    public function putInDo(){

        //获取请求的数据

        $request=Request::instance();

        $data=$request->only('qstr');

        $ex_arr=explode('`',$data['qstr']);



        for ($i=0; $i < count($ex_arr); $i++) { 

            $map['aid']=$ex_arr[$i];
            $map['mid']=session("userid");
            $map['add_time']=time();

            $efg=db("collect_stow")->where('mid',session('userid'))->where('stowtypeid','null')->field("count(id) as idnum")->find();//2,获取未设置BD状态的数量
            $stow_li=config("sys_stow_limit");//3,限制每天放入的数量
            
            if($efg['idnum']<=$stow_li){
                Db::name("collect_stow")->insert($map);//插入数据到表
                Db::name("company")->where('id',$map['aid'])->update(['mid'=>$map['mid']]);//更新company表的MID
                //信息统计收藏数量加1
                Db::name("stat")->where('mid',session('userid'))->setInc('stat_stow',1);
            }else {
                $this->error("您还有很多客户没有联系！");
            }

        }



        $this->success("成功放入收藏列表".count($ex_arr)."条数据！",'collect/index');



    }

    //执行添加,客户公海页面手动添加数据

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
            $db_in=Db::name("collect_stow")->insert(['mid'=>$data['mid'],'stowtypeid'=>$data['stowtype'], 'aid'=>$company_id,'add_time'=>time(),'bd_time'=>time(),'source'=>'录单']);

            if($db_in){
                $this->redirect("collect/stow");
            }else {
                $this->error("添加失败！");
            }
        }else {
            $this->error("添加失败！");
        }

    }
    //
    public function editcom(){
        //接收GET过来的数据
        $request=Request::instance();
        $param=$request->param();

        $db=db("company")->where('id',$param['aid'])->find();

        //获取分类数据
        $data_stow=Db::name("collect_stowtype")->order('type_name asc')->select();
        $this->assign("datastow",$data_stow);//搜索条件中下拉菜单
        $this->assign("param",$param);//分配变量：接收GET过来的变量
        $this->assign("db",$db);
        return view();
    }
    public function updatecom(){
        //接收POST提交的数据
        $request=Request::instance();
        $param=$request->param();
        $db=db("company")->update([
            'company_name'=>$param['company_name'],
            'linkman'=>$param['linkman'],
            'telphone'=>$param['telphone'],
            'qq'=>$param['qq'],
            'address'=>$param['address'],
            'barrier'=>$param['barrier'],
            'stowtype'=>$param['stowtype'],
            'id'=>$param['id']
        ]);
        if($db){
            $this->success("修改成功","collect/stow");
        }else {
            $this->error("修改失败");
        }
    }
}

