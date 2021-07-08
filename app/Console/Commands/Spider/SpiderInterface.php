<?php

namespace App\Console\Commands\Spider;

abstract class SpiderInterface
{
    protected $baseurl;
    public function __construct($baseurl){
        $this->baseurl = $baseurl;
    }

    abstract public function load($url);

    abstract public function getMeta();

    abstract public function getList($selector);

    abstract public function getMain($selector);

    abstract public function getNext($selector);

    protected function listItem($url,$title){
        return ['url'=>$url,'title'=>$title];
    }

    protected function mainItem($main){
        $summary = $this->summary($main);
        $cover = $this->cover($main);
        return ['main'=>$main,'summary'=>$summary,'cover'=>$cover];
    }

    protected function summary($main){
        return trim(mb_substr(strip_tags($main),0,100));
    }

    protected function cover($main){
        preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $main, $cover);
        $cover = array_slice($cover[1],0,3);
        foreach($cover as $k=>$v){
            $cover[$k] = $this->url($v);
        }
        return $cover;
    }

    protected function url($link){
        $site = parse_url($this->baseurl);
        $info = parse_url($link);

        //relate url
        if(empty($info['host'])){
            $info['scheme'] = $site['scheme'];
            $info['host'] = $site['host'];
        }
        $rel = empty($site['path'])? '/' : $site['path'];
        $path = empty($info['path'])? '/' : $info['path'];
        $first = substr($path,0,1);
        if($first == '/'){
            $info['path'] = $path;
        }else if($first == '.'){
            $num = count(explode('../',$path)) - 1;
            if($num > 0){
                while($num--){
                    $rel = dirname($rel);
                }
            }
            $path = str_replace('../','',$path);
            $path = str_replace('./','',$path);
            $path = $rel . $path;
        }else{
            $path = $rel.$path;
        }

        //build url
        $info['path'] = $path;
        $url = $info['scheme'].'://'.$info['host'];
        if(!empty($info['port'])){
            $url = $url.":".$info['port'];
        }
        $url = $url.$info['path'];
        if(!empty($info['query'])){
            $url = $url.'?'.$info['query'];
        }
        if(!empty($info['fragment'])){
            $url = $url.'#'.$info['fragment'];
        }
        
        return urldecode($url);
    }

}
