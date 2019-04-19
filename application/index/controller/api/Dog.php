<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/11/17
 * Time: 17:14
 */

namespace app\index\controller\api;

use think\Db;
use think\Request;

class Dog extends Base
{
    public function index(Request $request)
    {
        $act = $request->get("act")?:"show";
        if ($act == "show") {
            $num = Db::table("luobo_data")->where("id", "1")->field("luobo_dianzan")->find();
            return json_encode(array("code" => "1", "msg" => "成功", "data" => $num));
        } elseif ($act == "add") {
            Db::table("luobo_data")->where("id", "1")->setInc("luobo_dianzan");
            return json_encode(array("code" => "1", "msg" => "成功"));
        }
    }
}

//参数
//add 人数增加
//show 人数展示
