<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/24
 * Time: 20:01
 */

namespace app\index\controller\front;

use think\Db;

class Review extends Base
{
    /**
     * 评论管理
     * @param page 页码
     * @param title 搜索内容
     * @param page_info 分页内容
     * @return info 返回信息
     * */
    public function index()
    {
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        $title = $this->request->get("title") ? $this->request->get("title") : "";
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $count=Db::name("content")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr(["luobo_state" => "1"])->where("luobo_title like '%$title%'")->count();
        $page_info = $this->page($current_page,$count, 4);
        $wz_data=Db::name("content")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr(["luobo_state" => "1"])->where("luobo_title like '%$title%'")->limit($page_info['pagesize'],4)->select();
        foreach ($wz_data as$k=> $v){
            $wz_data[$k]["wz_info"]=Db::name("pl")->where("pl_id",$v["pl_id"])->find();
        }
//        print_r($wz_data);die;
        $url=$this->request->server("HTTP_HOST");
        $url=$url.self::url;
        $this->assign("url", $url);
        $this->assign("info", $wz_data);
        $this->assign("page_info", $page_info);
        $this->assign("title", $title);
        $this->assign("page", $current_page);
        return $this->fetch("front/review/luobo_pl");
    }

    /**
     * 评论删除
     * @param
     * @return
     * */
    public function delete(){
        $where = array();
        $wz_id = $this->request->get("id");
        $where["luobo_state"] = "1";
        if (Db::name("pl")->where(["id" => $wz_id])->update($where)) {
            //跳转到列表展示
            echo "1";
            die;
        };
    }

}