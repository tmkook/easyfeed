<?php

namespace App\Services\Spider;
use App\Models\Feed;
use App\Models\News;

abstract SpiderInterface
{
    protected $site;
    public function __construct($base){
        $this->site = parse_url($base);
    }

    protected function url($link){
        $info = parse_url($link);
        if(empty($info['host'])){//相对路径
            $info['scheme'] = $this->site['scheme'];
            $info['host'] = $this->site['host'];
            $info = $this->relative($info);
        }
        $url = 
    }

    //相对
    protected function relative($info){
        $rel = $this->site['path'];
        $path = $info['path'];
        $num = count(explode('../',$path)) - 1;
        if($num > 0){
            foreach($num as $i){
                $rel = dirname($rel);
            }
            $rel = trim($rel,'/');
            $path = trim(str_replace('../','',$path),'/');
        }
        $path = str_replace('./','',$path);
        $info['path'] = $path;
    }

    protected function autoUrl($url_arr){
        $new_url = $url_arr['scheme'] . "://".$url_arr['host'];
        if(!empty($url_arr['port']))
            $new_url = $new_url.":".$url_arr['port'];
        $new_url = $new_url . $url_arr['path'];
        if(!empty($url_arr['query']))
            $new_url = $new_url . "?" . $url_arr['query'];
        if(!empty($url_arr['fragment']))
            $new_url = $new_url . "#" . $url_arr['fragment'];
        return $new_url;
    }

    abstract public function feeds(Feed $item,String $target);

    abstract public function news(News $item,String $target);
}