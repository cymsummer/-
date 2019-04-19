<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/24
 * Time: 18:49
 */

namespace app\index\controller\front;

use think\Db;

class Show extends Base
{
    /**
     * 内容列表
     * @param act 请求类型
     * @param page 页码
     * @param title 搜索内容
     * @return
     * */
    public function index()
    {
        $act = $this->request->request("act") ? $this->request->request("act") : "publish";
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $title = $this->request->get("title") ? $this->request->get("title") : "";
        //定时发布
        if ($act == "timing") {
            // 获取总记录数
            $timing_count = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '>', date("Y-m-d H:i:s", time()))->where("luobo_state = 3")->count();
            $info = $this->page($current_page, $timing_count, 3);
            //列表
            $list = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '>', date("Y-m-d H:i:s", time()))->where("luobo_state = 3")->limit($info['pagesize'], 3)->order("publish_date desc")->select();
        } elseif ($act == "publish") {
            //已发布
            // 获取总记录数
            $publish_count = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr("luobo_state = 1")->count();
            $info = $this->page($current_page, $publish_count, 3);
            //列表
            $list = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr("luobo_state = 1")->limit($info['pagesize'], 3)->order("publish_date desc")->select();
//            print_r($list);die;
        } else {
            //草稿箱
            // 获取总记录数
            $draft_count = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->where("luobo_state = 2 AND publish_date is null")->count();
            $info = $this->page($current_page, $draft_count, 3);
            //列表
            $list = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->where("luobo_state = 2 AND publish_date is null")->limit($info['pagesize'], 3)->order("id desc")->select();
        }

        $url = $this->request->server("HTTP_HOST");
        $url = $url . self::url;
        $this->assign("url", $url);
        $this->assign("page", $current_page);
        $this->assign("title", $title);
        $this->assign("act", $act);
        $this->assign("page_info", $info);
        $this->assign("list", $list);
        return $this->fetch("front/show/luobo_list");
    }

    /**
     * 文章删除功能
     * @param id 文章id
     * @return int
     * */
    public function delete()
    {
        $wz_id = $this->request->get("id");
        if (Db::name("content")->where(["id" => $wz_id])->update(["luobo_state"=>"4","publish_date"=>null])) {
            //跳转到列表展示
            return json_encode(array("code"=>"1","msg"=>"删除成功"));
        };
    }

    /*
     * 内容展示
     * */
    public function getid()
    {
        $id = $this->request->get("id");
        $info["code"] = "1";
        $info["msg"] = "成功";
        if (empty($id)) {
            $info["data"] = Db::name("content")->order("publish_date desc")->limit(10)->select();
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
            $trans = array_flip(get_html_translation_table(HTML_ENTITIES));
            $info["data"][$k]["new_content"] = strtr($v['luobo_content'], $trans);
            $info["data"][$k]["new_luobo_cover_img"] = str_replace("upload","big",$v["luobo_cover_img"]);
        }
//        print_r($info);die;
        $this->assign("info", $info);
        return $this->fetch("front/show/detail_list");
    }
}