<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/25
 * Time: 10:34
 */

namespace app\index\controller\api;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;

    public function __initialize(Request $request)
    {

    }
    /**
     * curl请求
     * @param url 链接地址
     * @return output 获取结果
     * */
    public function get_info($url)
    {
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        return $output;
    }
}