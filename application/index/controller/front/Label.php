<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/7/21
 * Time: 22:44
 */

namespace app\index\controller\front;

use think\cache\driver\Redis;
use think\Db;

class Label extends Base
{
    /*
     * 标签管理
     * */
    public function index()
    {
        $url = $this->request->server("HTTP_HOST");
        $title = $this->request->get("title") ? $this->request->get("title") : "";
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        $count = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr(["luobo_state" => "1"])->count();
        $info = $this->page($current_page, $count, 4);
        $list = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr(["luobo_state" => "1"])->limit($info["pagesize"], 4)->order("id desc")->select();
        foreach ($list as $k => $v) {
            $list[$k]["lable"] = Db::connect("db_config1")->table("small_program_label")->where('label_type', '=', "1")->where("label_id", "=", $v['label_id'])->find();;
        }

        $url = $url . self::url;
        $this->assign("url", $url);
        $this->assign("title", $title);
        $this->assign("page", $current_page);
        $this->assign("page_info", $info);
        $this->assign("list", $list);
        return $this->fetch("front/label/luobo_label");
    }

    /*
     * 标签添加
     * */
    public function add()
    {
        $redis = new Redis();
        $id = $this->request->post("id");
        $act = $this->request->post("act");
        $data["label_name"] = $this->request->post("label_name");
        $data["label_type"] = "1";
        $data["label_time"] = time();
        $update["label_id"] = $redis->inc("program_tab_id");
        $label_id = Db::name("content")->field("label_id")->find($id);
        if (empty($label_id["label_id"])) {
            if (Db::name("content")->where("id",$id)->update($update)) {
                $data["label_id"] = $redis->get("program_tab_id");
                if (Db::connect("db_config1")->table("small_program_label")->insert($data)) {
                    return json_encode(["code" => "1", "msg" => "打标成功"]);
                } else {
                    return json_encode(["code" => "-1", "msg" => "打标失败"]);
                };
            } else {
                return json_encode(["code" => "-1", "msg" => "打标失败"]);
            };
        }else {
            //添加逻辑
            if($act=="update"){
                $new_label_name =$data["label_name"];
            }else{     //修改逻辑
                $new_label_name="";
                $label_name = Db::connect("db_config1")->table("small_program_label")->where("label_id",$label_id["label_id"])->find();
                $new_label_name .= $label_name["label_name"] . "，".$data["label_name"];
            }
            if (Db::connect("db_config1")->table("small_program_label")->where("label_id=" . $label_id["label_id"])->update(["label_name"=>$new_label_name])) {
                return json_encode(["code" => "1", "msg" => "打标成功"]);
            } else {
                return json_encode(["code" => "-1", "msg" => "打标失败"]);
            };
        }

    }
}