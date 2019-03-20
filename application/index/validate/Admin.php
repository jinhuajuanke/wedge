<?php
namespace app\index\validate;
use think\Validate;
class Admin extends Validate
{
    protected $rule = [
        'nickname'  =>  'require|max:25',
        'email' =>  'email',
        'telphone' =>  'max:6,11',
    ];
    
    protected $message = [
        'nickname.require'  =>  '用户名必须',
        'email' =>  '邮箱格式错误',
        'telphone' =>  '6到11位之间',
    ];
    
    protected $scene = [
        'add'   =>  ['nickname','email'],
        'edit'  =>  ['email'],
    ];    
}
?>