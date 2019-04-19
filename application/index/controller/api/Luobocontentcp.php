<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/6/6
 * Time: 17:35
 */

namespace app\index\controller\api;

use think\Db;

class Luobocontentcp extends Base
{
    /*
    * 获取文章信息
    * */
    public function getinfo()
    {
        $id = $this->request->get("id");
        $page = $this->request->get("page") ? $this->request->get("page") : "1";
        $info["code"] = "1";
        $info["msg"] = "成功";
        $config = config("img_config");
        if (empty($id)) {
            $info["page"] = $page;
            $info["pagecount"] = (string)ceil(Db::name("content")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr("luobo_state = 1")->count() / 3);
            $info["lastpage"] = (string)($page - 1);
            if ($info["lastpage"] < 2) {
                $info["lastpage"] = "1";
            }
            $info["nextpage"] = (string)($page + 1);
            if ($info["nextpage"] > $info["pagecount"]) {
                $info["nextpage"] = (string)$info["pagecount"];
            }
            $info["data"] = Db::name("content")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr("luobo_state = 1")->order("publish_date desc")->page($page, "3")->select();

        } else {
            $info["data"] = Db::name("content")->where("id=$id")->select();
        }
        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        foreach ($info["data"] as $k => $v) {
            if (date("Y-m-d", strtotime($v['publish_date'])) == date("Y-m-d")) {
                $info["data"][$k]["new_date"] = "Today";
            } else {
                $info["data"][$k]["new_date"] = "星期" . $weekarray[date("w", strtotime($v['publish_date']))];
            }
            $info["data"][$k]["new_publish_date"] = date("Y年n月j日", strtotime($v['publish_date']));
            if (empty($id)) {
                unset($info["data"][$k]["luobo_content"]);
            } else {
                $trans = array_flip(get_html_translation_table(HTML_ENTITIES));
                $info["data"][$k]["luobo_content"] = strtr($v['luobo_content'], $trans);
            }
            $info["data"][$k]["titleSize"] = "12";
            $info["data"][$k]["new_luobo_cover_img"] = $config["big_src"] . "/"  . substr($v["luobo_cover_img"], strrpos($v["luobo_cover_img"], "/"));

           // $info["data"][$k]["new_luobo_cover_img"] = 'http://' . $_SERVER['HTTP_HOST'] . "/luobo/public/upload/big" . substr($v["luobo_cover_img"], strrpos($v["luobo_cover_img"], "/"));
        }
        return json_encode($info);
    }

}
