<?php

/**

*登录页面

*@file login.php

*@date 2019.2.19

*@author yajun li<312690422@qq.com>

*/

namespace app\index\controller;

use think\Controller;

use think\Request;

use think\Validate;

header("Content-type:text/html;charset=utf-8");
class Login extends Controller
{

    public function index(){
       
       //加载模板
    	return view();
    }

    public function log(){
    	$captcha=input('code');  //获取用户输入的验证码  	

    	if(!captcha_check($captcha)){ 
		 	//验证失败
    		$this->error("验证码错误！请重新输入！");
		};

		//获取用户输入的用户名及密码
		$request=Request::instance();
		$post = $request->param();

        //验证
        $rule = [
            'username'  => 'require|length:4,20|alphaDash',
            'pwd' => 'require'
        ];

        $data = [
            'username'  => $post['username'],
            'pwd' => $post['pwd']
        ];

        $msg = [

            'username.require'   => '登录账号必须填写',
            'username.length'    => '登录账号最多20个字符，最少4个字符',
            'username.alphaDash' => '账号只能包含字母和数字，下划线_及破折号-',
            'pwd.require'        => '密码必须填写' 
        ];

        $validate=new Validate($rule,$msg);
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
		

        $info=db('admin')->field('id,username,pwd,nickname')->where('username',$post['username'])->find();
        if(!$info){
        	$this->error("用户名不存在！");
        }



        if(md5($post['pwd'])!=$info['pwd']){
        	$this->error("密码不正确！");
        } else {          
        	session("nickname",$info['nickname']);
            session("userid",$info['id']);  

            //更新用户登录时间
            db("admin")->where('id',$info['id'])->update(['logintime'=>$request->time(),'status'=>1]);
        	$this->redirect("Index/index");
        }

    }

    //退出

    public function logout(){
        db("admin")->where("id",session('userid'))->update(['status'=>0]);

        session("nickname",null);
        session("userid",null);
    	session("page",null);

        setcookie('nickname','',time()-1,'/');
        setcookie('userid','',time()-1,'/');
    	setcookie('page','',time()-1,'/');     

    	$this->redirect("Login/index");
    }    

}

