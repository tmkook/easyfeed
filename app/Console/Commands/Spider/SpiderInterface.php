<?php

namespace App\Console\Commands\Spider;

abstract class SpiderInterface
{
    protected $baseurl;
    public function __construct($baseurl){
        $this->baseurl = $baseurl;
    }

    abstract public function load($url);

    abstract public function getList($selector);

    abstract public function getMain($selector);

    abstract public function getNext($selector);

    abstract public function getMeta($name);

    abstract public function getIcon();

    abstract public function getTitle();

    protected function getSummary($main){
        $main = str_replace(' ','',$main);
        return mb_substr(strip_tags($main),0,64);
    }

    protected function getCover($main){
        if(empty($this->cover)){
            $this->fixImages($main);
        }
        $cover = $this->cover;
        $this->cover = [];
        return $cover;
    }

    protected function fixImages($main){
        preg_match_all('|<img.*?src="([^"]*)"[^>]*>|i', $main, $cover);
        $cover = $cover[1];
        foreach($cover as $k=>$v){
            $img = $this->url($v);
            $cover[$k] = $img;
            $main = str_replace($v,$img,$main);
        }
        $this->cover = array_slice($cover,0,3);
        return $main;
    }

    protected function listItem($url,$title){
        return ['url'=>$url,'title'=>$title];
    }

    protected function mainItem($main){
        $main = $this->fixImages($main);
        $cover = $this->getCover($main);
        $summary = $this->getSummary($main);
        return ['main'=>$main,'summary'=>$summary,'cover'=>$cover];
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
