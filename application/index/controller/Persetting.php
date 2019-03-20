<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Validate;
class Persetting extends Allow{
	
    public function index(){
    	$result=db("admin")->find(session('userid'));
    	//输出变量
    	$this->assign("data",$result);
       //加载个人设置模板
    	return view();
    }
    //执行修改个人设置
    public function update(){
       
    	$code=input('code');
    	if(!captcha_check($code)){
    		$this->error("验证码错误！");
    	}
        //获取提交的所有数据
        $data=Request::instance();
        $post=$data->param();

        $file=$data->file("face");

        if($file){

            $path=ROOT_PATH.'public'.DS.'static'.DS.'admin'.DS.'userup'.DS.session('userid');
            if(!empty($post['oldface'])){
                if(is_file($path.'\\'.$post['oldface'])){
                    unlink($path.'\\'.$post['oldface']);

                }
            }
            $info=$file->validate(['size'=>2097152,'ext'=>'jpg,png,gif,jpeg'])->move($path);
               
            if($info){
                // $image=\think\Image::open($path.'\\'.$info->getSaveName());

                // $image->thumb(150,150)->save($path.'\\'.date('Ymd').'\\thumb_'.$info->getFileName());

                $mysql['face']=$info->getSaveName();
            }else{
                // 上传失败获取错误信息
              
                $this->error($file->getError());
            }
        }

       
        if(!empty($post['pwd'])&&!empty($post['repwd'])){
            
            if($post['pwd']!=$post['repwd']){
                $this->error("两次密码不相同");
            }else {
                $mysql['pwd']=md5($post['pwd']);
            }
        }
        //验证数据
        $rule=[
            ['username','require|alpha','用户名必须填写|用户名必须是字母'],
            ['nickname','require'],
            ['telphone','number','手机号必须是数字'],
            ['email','email','邮箱格式不对']
        ];
        $scene=[
           'username'=>$post['username'],
           'nickname'=>$post['nickname'],
           'telphone'=>$post['telphone'],
           'email'=>$post['email'],
        ];
        //实例化验证类
        $validate=new Validate($rule);
        $check_res=$validate->check($scene);
        if(!$check_res){
            $this->error($validate->getError());
        }
        $mysql['username']=$post['username'];
        $mysql['nickname']=$post['nickname'];
        $mysql['telphone']=$post['telphone'];
        $mysql['email']=$post['email'];
    	$result=db("admin")->where('id',session("userid"))->update($mysql);
    	if(!$result){
           
    		$this->error("更新数据失败");
    	}else {
             //如果修改了密码就让当前账户的ID退出，设置status=0
            db("admin")->where('id',session('userid'))->update(['status'=>0]);
    		$this->success("成功更新数据！","Persetting/index");
    	}
    }
}
