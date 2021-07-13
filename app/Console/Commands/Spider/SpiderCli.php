<?php

namespace App\Console\Commands\Spider;

use App\Console\Commands\Spider\SpiderInterface;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class SpiderCli extends SpiderInterface
{
    protected $doc;
    protected $next;
    public function __construct($baseurl){
        $options = [
            'delay' => 1000,
            'timeout' => 15,
            'verify' => false,
            'allow_redirects' => [
                'max' => 2,
                'referer' => true,
            ],
            'headers' => [
                'USER-AGENT'=>'Mozilla/5.0 (Spider/1.0)',
                'CLIENT-IP'=>'40.221.206.111',
                'X-FORWARDED-FOR'=>'40.221.206.111',
            ]
        ];
        $this->client = new \GuzzleHttp\Client($options);
        parent::__construct($baseurl);
        $this->next = [];
    }

    public function load($target){
        $response = $this->client->get($target);
        $content = $response->getBody()->getContents();
        $this->doc = new Crawler($content);
    }

    public function getTitle(){
        $title = $this->doc->filter('title');
        return trim($title->text());
    }

    public function getIcon(){
        $link = $this->doc->filter('head > link')->each(function($m){
            return $m;
        });
        foreach($link as $item){
            $k = $item->attr('rel');
            if(strpos($k,'icon') > 0){
                $icon = $item->attr('href');
                break;
            }
        }
        if(empty($icon)){
            $meta = $this->doc->filter('meta')->each(function($m){
                return $m;
            });
            foreach($meta as $m){
                $k = $m->attr('property');
                if($k=='og:image'){
                    $icon = $this->url($m->attr('content'));
                    break;
                }
            }
        }
        if(empty($icon)){
            $icon = '/favicon.ico';
        }
        return $this->url($icon);
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
        $this->removeTags($art,['p','a','div','script','style']);
        $main = $art->html();
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext($selector){
        $url = '';
        if($selector){
            $dom = $this->doc->filter($selector);
            if(!empty($dom)){
                $url = $dom->attr('href');
                $url = trim($this->url($url),'/');
            }
        }
        if($this->baseurl == $url || in_array($url,$this->next)){
            $url = '';
        }else{
            $this->next[] = $url;
        }
        return $url;
    }

    protected function getMetas(){
        $info = [];
        $meta = $this->doc->filter('meta')->each(function($m){
            return $m;
        });
        foreach($meta as $m){
            $k = $m->attr('name');
            $v = $m->attr('content');
            if($k){
                $info[$k] = $v;
            }
        }
        return $info;
    }

    protected function removeTags($art,$arr){
        foreach($arr as $tag){
            $art->filter($tag)->each(function($ele){
                if(trim($ele->html()) == ''){
                   foreach($ele as $a){
                        $a->parentNode->removeChild($a);
                   }
                }
            });
        }
    }
}