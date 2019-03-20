<?php

/**

* 系统用户管理

*@file admin.php

*@date 2019/2/20

*@author Yajun Li<312690422@qq.com>

*/

namespace app\index\controller;

use think\Controller;

use think\Request;

use think\Validate;

header("Content-type:text/html;charset=utf-8");

class Admin extends Allow{

	

    public function index(){
        
        $nickname=input('nickname');
        $group_id=input('group_id');
        $where=array();
        if(!empty($nickname)){
            $where['nickname']=$nickname;
        }
        if(!empty($group_id)){
            $where['group_id']=$group_id;
        }
        if(session("userid")==1){
            $data=db("admin")
            ->alias('a')
            ->where($where)
            ->join('admingroup ag','a.group_id=ag.id')
            ->field('*,a.id as adminid')
            ->order('a.id asc')
            ->paginate(10);

        }else {
            $data=db("admin")
            ->alias('a')
            ->where($where)
            ->join('admingroup ag','a.group_id=ag.id')
            ->field('*,a.id as adminid')
            ->order('a.id asc')
            ->where('group_id','not in','1,2')
            ->where('p_id',session('userid'))
            ->paginate(10);
        }
    	//分配变量
    	$this->assign("data",$data);
        
            $db=db("admingroup")->field("id,groupname")->order('id asc')->select();
           
        $this->assign("list",$db);//分配成员组变量做搜索下拉菜单

        $this->assign("nickname",$nickname);//用于显示搜索
        $this->assign("group_id",$group_id);//用于显示搜索
       //加载模板
    	return view();

    }

    //加载添加页面

    public function add(){

        //获取分组的数据做下拉菜单
        $db=db("admingroup")->field('id,groupname')->order('id asc')->select();

        //分配用户组数据变量
        $this->assign("list",$db);
        //缓存
        
            //获取所属团队的数据做下拉菜单
            $db_dui=db("admin")->where('group_id',2)->field('id,nickname')->order('id asc')->select();
           
        //分配变量
        $this->assign("team",$db_dui);

    	return view();
    }

    //执行添加账户

    public function insert(){

    	//获取数据
    	$result=Request::instance();
    	$datas=$result->param();

    	if($datas['pwd']!=$datas['repwd']){
            $this->error("两次密码不正确");
        }
        //缓存
        
            $db_username=db("admin")->field('username')->where('username',$datas['username'])->find();
           
       
        if($db_username){
            $this->error("账号重复，请更换一个账号名称！");
        }

    	//验证数据

    	$rule=[
    		['username','require|alphaDash|length:4,20','登录账号不能为空|登录账号应该包含字母和数字，下划线_及破折号-|登录账号长度为大于4个字符于20个'],
    		['pwd','require|length:4,30','密码不能为空|密码长度为大于4个字符于30个' ],
    		['nickname','require|chsAlphaNum','昵称不能为空|昵称只能填写汉字或字母数字' ],
    		['telphone','number|length:8,11','手机号只能为数字|手机号长度必须为8到11位'],
    		['email','email','邮箱必须为email地址']
    	];

    	$data=[
    		'username' => $datas['username'],
    		'pwd' => $datas['pwd'],
    		'nickname' => $datas['nickname'],
    		'telphone' => $datas['telphone'],
            'email' => $datas['email'],
    		'group_id' => $datas['group_id'],   		

    	];

    	$validate=new Validate($rule);
    	$val_result=$validate->check($data);
    	if(!$val_result){
    		$this->error($validate->getError());
    	}

    	$data['loginip']=$result->ip();
        $data['logintime']=time();
        $data['condition']=1;
        $data['p_id']=$datas['p_id'];
        $data['pwd']=md5($datas['pwd']);

    	//插入数据
    	$data_in=db("admin")->insert($data);
    	if($data_in){
           
            db("stat")->insert(['stat_stow'=>0,'stat_class'=>0,'stat_note'=>0]);
    		$this->success("成功创建账户","admin/index");
    	} else {
    		$this->error("创建账户失败");
    	}
    }

    //加载修改账户页面

    public function edit(){

        //获取分组的数据做下拉菜单
        $db=db("admingroup")->field('id,groupname')->select();

        //分配变量
        $this->assign("grouplist",$db);
        
        
            //获取所属团队的数据做下拉菜单
            $db_dui=db("admin")->where('group_id',2)->field('id,nickname')->order('id asc')->select();
           
        
        //分配变量
        $this->assign("team",$db_dui);


    	//获取要修改的ID数据
    	$id=Request::instance()->param('id');
    	$result=db("admin")->find($id);

    	//分配变量
    	$this->assign("list",$result);
    	return view();

    }

    //执行修改账户

    public function update(){

    	//获取数据
    	$result=Request::instance();
    	$datas=$result->param();
    	$id=$datas['id'];

    	//验证数据
    	$rule=[
    		['username','require|alphaDash|length:4,20','登录账号不能为空|登录账号应该包含字母和数字，下划线_及破折号-|登录账号长度为大于4个字符于20个'],
            ['pwd','length:4,32','密码不能为空|密码长度为大于4个字符于32个' ],  
    		['nickname','require|chsAlphaNum','昵称不能为空|昵称只能填写汉字或字母' ],
    		['telphone','number|length:8,11','手机号只能为数字|手机号长度必须为8到11位'],
    		['email','email','邮箱必须为email地址']
    	];

    	$data=[

    		'username' => $datas['username'],
    		'nickname' => $datas['nickname'],
    		'telphone' => $datas['telphone'],
    		'email' => $datas['email'],
    		'pwd' => $datas['pwd'],
    	];
        if(session("userid")==1){
            $data['group_id']=$datas['group_id'];
        }else {
            if($datas['group_id']==1){
                $this->error("您没有权限设置总管理员");
            } else{
                $data['group_id']=$datas['group_id'];

            }

        }
        $data['condition']=$datas['condition'];
    	$data['p_id']=$datas['p_id'];    	

    	$validate=new Validate($rule);
    	$val_result=$validate->check($data);
    	if(!$val_result){
    		$this->error($validate->getError());
    	}
       
            //获取当前ID账户的密码并且和输入的密码作判断
            $db_id=db("admin")->field('pwd')->find($id);
           
        

        if($datas['pwd']==''){
            $data['pwd']=$db_id['pwd'];
        }else {
            //如果修改了密码就让当前账户的ID退出，设置status=0

            db("admin")->where('id',$id)->update(['status'=>0]);
            $data['pwd']=md5($datas['pwd']);
        }
       

    	//更新数据
    	$data_up=db("admin")->where('id',$id)->update($data);
    	if($data_up){
           
    		$this->success("成功修改管理员","admin/index");

    	} else {

    		$this->error("修改管理员失败");

    	}

    }

    //执行删除账户

    public function del(){

        $id=input('id');
        //删除账户也必须删除collet_stow收藏表、stat表、collect_stowtype表中的MID数据及更新company表中的mid,stowtype
       
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
       

        $db_ad=db("admin")->where('id',$id)->delete();
        if($db_ad){           
           
            $this->success("删除成功！","admin/index");

        }else {

            $this->error("删除失败！");

        }

    }

}

