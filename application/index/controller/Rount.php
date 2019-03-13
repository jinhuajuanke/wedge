<?php

namespace app\index\controller;


class Rount extends Controller{


	$v = request()->header('version');
	var_dump($v);
	if($v){
	    Route::rule('api/:c/:a', 'api/'.$v.'.:c/:a');
	    Route::rule('api', 'api/'.$v.'.index/index');
	}
}

