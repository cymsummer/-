<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');
Route::get('upd_img','index/front.Content/upd_img');

return [

];


//use think\Route;
//
////登陆
//Route::rule('login','index/front.Login/index');
//
////页面展
//Route::rule('show_index','index/front.Show/index');//内容列表
//Route::rule('show_delete','index/front.Show/delete');//内容删除
//Route::rule('show_one','index/front.Show/getid');//单个内容展示
//
////文章发布
//Route::rule('content_index','index/front.Content/index');//文章页面展示
//Route::rule('content_add','index/front.Content/add');//文章添加
//Route::rule('content_publish','index/front.Content/publish');//文章发布
//Route::rule('content_insertimg','index/front.Content/insertimg');//插入图片


//初始化信息

