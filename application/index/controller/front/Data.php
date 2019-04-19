<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/31
 * Time: 18:08
 */
namespace app\index\controller\front;

use think\Db;

class Data extends Base
{
    /*
     * 数据统计
     * @param page 页码
     * @param title 搜索内容
     * @param page_info 分页内容
     * @return info 返回信息
     * */
    public function index(){
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        $title = $this->request->get("title") ? $this->request->get("title") : "";
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $count=Db::name("data")->count();
        $page_info = $this->page($current_page,$count, 4);
        $list = Db::name("content")->where('luobo_info|luobo_title', 'like', "%" . $title . "%")->whereTime('publish_date', '<', date("Y-m-d H:i:s", time()))->whereOr("luobo_state = 1")->limit($page_info['pagesize'], 4)->order("publish_date desc")->select();
        foreach ($list as$k=> $v){
            $list[$k]["pl"]=Db::name("pl")->where("pl_id=".$v["pl_id"])->count();
        }

        $url=$this->request->server("HTTP_HOST");
        $url=$url.self::url;
        $this->assign("url", $url);
        $this->assign("info", $list);
        $this->assign("page_info", $page_info);
        $this->assign("title", $title);
        $this->assign("page", $current_page);
        return $this->fetch("front/data/luobo_data");
    }
}