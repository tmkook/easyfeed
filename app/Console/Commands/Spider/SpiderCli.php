<?php

namespace App\Console\Commands\Spider;

use App\Console\Commands\Spider\SpiderInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class SpiderCli extends SpiderInterface
{
    protected $doc;
    protected $target;

    public function __construct($baseurl){
        parent::__construct($baseurl);
        $this->client = new \GuzzleHttp\Client();
    }

    public function load($target){
        $headers = [
            'USER-AGENT'=>'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
            'CLIENT-IP'=>'40.221.206.111',
            'X-FORWARDED-FOR'=>'40.221.206.111',
        ];
        $response = $this->client->request('GET',$target,['headers'=>$headers,'referer'=>true,'verify'=>false]);
        $content = $response->getBody()->getContents();
        $content = preg_replace('|<\s|','&lt;',$content); //todo:文章中含有><导致无法解析HTML,需转义正文中的符号    
        $this->doc = new Crawler($content);
        $this->target = $target;
    }

    public function getTitle(){
        $title = $this->doc->filter('title');
        return trim($title->html());
    }

    public function getIcon(){
        $icon = '';
        $link = $this->doc->filter('link');
        foreach($link as $m){
            $k = $m->attr('rel');
            if(strpos($k,'icon') > 0){
                $icon = $this->url($m->attr('href'));
                break;
            }
        }
        if(empty($icon)){
            $meta = $this->doc->filter('meta');
            foreach($meta as $m){
                $k = $m->attr('property');
                if($k=='og:image'){
                    $icon = $this->url($m->attr('content'));
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
        $ret = $this->doc->filter($selector)->each(function($list){
            $title = mb_substr(trim($list->text()),0,100);
            $url = $this->url($list->attr('href'));
            return $this->listItem($url,$title);
        });
        return $ret;
    }

    public function getMain($selector,$del){
        $art = $this->doc->filter($selector);
        if($del){
            $del = explode(',',$del);
            foreach($del as $ad){
                $rm = $art->filter($ad);
                foreach($rm as $a){
                    $a->parentNode->removeChild($a);
                }
            }
        }
        $rm = $art->filter('div');
        foreach($rm as $a){
            if(trim($a->textContent) == ''){
                $a->parentNode->removeChild($a);
            }
        }
        $rm = $art->filter('p');
        foreach($rm as $a){
            if(trim($a->textContent) == ''){
                $a->parentNode->removeChild($a);
            }
        }
        $main = $art->html();
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext($selector){
        $url = '';
        if($selector){
            $dom = $this->doc->filter($selector);
            if(!empty($dom)){
                $url = $dom->attr('href');
                $url = $this->url($url);
            }
        }
        if($url && trim($this->baseurl,'/') == trim($url,'/')){
            $url = '';
        }
        return $url;
    }

    protected function getMetas(){
        $info = [];
        $meta = $this->doc->filter('meta');
        foreach($meta as $m){
            $k = $m->attr('name');
            $v = $m->attr('content');
            if($k){
                $info[$k] = $v;
            }
        }
        return $info;
    }
}