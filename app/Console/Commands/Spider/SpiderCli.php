<?php

namespace App\Console\Commands\Spider;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\Spider\SpiderInterface;


class SpiderCli extends SpiderInterface
{
    
    protected $doc;
    protected $target;

    public function load($target){
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $target);
        $content = $response->getBody()->getContents();
        $content = preg_replace('|<\s|','&lt;',$content); //todo:文章中含有><导致无法解析HTML,需转义正文中的符号
        $content = preg_replace('|\s>|','&lt;',$content); //临时解决
        $dom = new \PHPHtmlParser\Dom;
        $this->doc = $dom->loadStr($content);
        $this->target = $target;
    }

    public function getTitle(){
        $title = $this->doc->find('title')[0];
        return empty($title)? '' : trim($title->text);
    }

    public function getIcon(){
        $icon = '';
        $link = $this->doc->find('link');
        foreach($link as $m){
            $k = $m->getAttribute('rel');
            if(strpos($k,'icon') > 0){
                $icon = $this->url($m->getAttribute('href'));
                break;
            }
        }
        if(empty($icon)){
            $meta = $this->doc->find('meta');
            foreach($meta as $m){
                $k = $m->getAttribute('property');
                if($k=='og:image'){
                    $icon = $this->url($m->getAttribute('content'));
                    break;
                }
            }
        }
        if(empty($icon)){
            $icon = trim($this->baseurl,'/').'/favicon.ico';
        }
        return $icon;
    }

    public function getMeta($name){
        if(empty($this->metas)){
            $this->metas = $this->getMetas();
        }
        $ret = isset($this->metas[$name])? $this->metas[$name] : '';
        return $ret;
    }

    public function getList($selector){
        $ret = [];
        $list = $this->doc->find($selector);
        foreach($list as $a){
            $title = mb_substr(trim($a->innerText),0,100);
            $url = $a->getAttribute('href');
            if(empty($url)) continue; //空内容
            $ret[] = $this->listItem($this->url($url),$title);
        }
        return $ret;
    }

    public function getMain($selector){
        $art = $this->doc->find($selector)[0];
        if(empty($art)){
            $art = $this->doc->find('body')[0];
        }
        if($art){
            $copy = $art->find('.copyright')[0];
            if(!empty($copy)){
                $copy->delete();
            }
            $main = $art->innerHtml;
        }
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext($selector){
        $url = '';
        if($selector){
            $dom = $this->doc->find($selector)[0];
            if(!empty($dom)){
                $url = $dom->getAttribute('href');
                $url = $this->url($url);
            }
        }
        if(trim($this->baseurl,'/') == trim($url,'/')){
            $url = '';
        }
        return $url;
    }

    protected function getMetas(){
        $info = [];
        $meta = $this->doc->find('meta');
        foreach($meta as $m){
            $k = $m->getAttribute('name');
            $v = $m->getAttribute('content');
            if($k){
                $info[$k] = $v;
            }
        }
        return $info;
    }
}