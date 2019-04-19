<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/11/10
 * Time: 20:58
 */

namespace app\index\controller\front;

use think\cache\driver\Redis;
use think\Request;

class Ini extends Base{
    //获取redis数据
    public function index(){
        $redis = new Redis();
        print_r("program_comment_id : ".$redis->get("program_comment_id"));//自增的评论总id
        echo "<br>";
        print_r("program_tab_id : ".$redis->get("program_tab_id"));//自增的标签总id

    }
    //添加redis数据
    public function add(Request $request){
        $id1=$request->get("id1");
        $id2=$request->get("id2");
        $redis = new Redis();
        $redis->set("program_comment_id",$id1);
        $redis->set("program_tab_id",$id2);
        print_r("program_comment_id : ".$redis->get("program_comment_id"));//自增的评论总id
        echo "\n";
        print_r("program_tab_id : ".$redis->get("program_tab_id"));//自增的标签总id
    }
}