<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Console\Commands\Spider\SpiderCli;
use App\Models\Feed;
use App\Models\News;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:spider {method} {arg}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'spider fetch url';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $method = $this->argument('method');
        $this->{$method}();
        return 0;
    }

    //新站点全站抓取
    public function sitespider(){
        $feed = Feed::where('state',Feed::CHECK);
        foreach($feed->cursor() as $item){
            $cli = new SpiderCli($item);
            $this->updateFeeds($item,$cli,$item->url);
        }
    }

    //老站点更新首页
    public function newlyspider(){
        $time = time();
        $newly = Feed::where('state',Feed::SUCCESS)->where('update_next','<',$time);
        foreach($newly->cursor() as $item){
            $cli = new SpiderCli($item);
            $cli->load($item->url);
            $this->updateFeed($item,$cli);
        }
    }

    //递归更新
    public function feeds(){
        $id = $this->argument('arg');
        $feed = Feed::find($id);
        $cli = new SpiderCli($feed);
        $this->updateFeeds($feed,$cli,$feed->url);
    }

    //更新一页
    public function feed(){
        $id = $this->argument('arg');
        $feed = Feed::find($id);
        $cli = new SpiderCli($feed);
        $cli->load($feed->url);
        $this->updateFeed($feed,$cli);
    }

    //更新文章
    public function main(){
        $id = $this->argument('arg');
        $news = News::find($id);
        $feed = Feed::find($news->feed_id);
        $cli = new SpiderCli($feed);
        $cli->load($news->url);
        $this->updateMain($feed,$news,$cli);
    }

    //抓取文章
    protected function updateMain($feed,$news,$cli){
        $main = $cli->getMain();
        if(!empty($main)){
            $news->main = $main['main'];
            $news->cover = $main['cover'];
            $news->summary = $main['summary'];
            $news->state = News::SUCCESS;
            $news->save();
        }else{
            $news->state = News::FAIL;
            $news->save();
        }
        return true;
    }

    //递归抓取全站
    protected function updateFeeds($feed,$cli,$url){
        $cli->load($url);
        $next = $this->updateFeed($feed,$cli);
        if($next){
            $this->updateFeeds($feed,$cli,$next);
        }
    }

    //抓取一页
    protected function updateFeed($feed,$cli){
        $meta = $cli->getMeta();
        $list = $cli->getList();
        $next = $cli->getNext();
        if(isset($meta['title'])){
            $feed->title = $meta['title'];
        }
        if(isset($meta['description'])){
            $feed->description = $meta['description'];
        }
        if(isset($meta['icon'])){
            $feed->icon = $meta['icon'];
        }
        $newly = 0;
        if(empty($list)){
            $feed->state = Feed::FAIL;
        }else{
            $feed->state = Feed::SUCCESS;
            foreach($list as $item){
                $uuid = md5($item['url']);
                $news = News::where('uuid',$uuid)->where('feed_id',$feed->id)->first();
                if(empty($news)){
                    $newly++;
                    $news = new News;
                }
                $news->feed_id = $feed->id;
                $news->uuid = $uuid;
                $news->url = $item['url'];
                $news->title = $item['title'];
                $news->state = News::CHECK;
                $news->save();
            }
        }
        if($newly > 0){
            $feed->update_wait = 1;
        }else{
            $feed->update_wait += 1;
        }
        $feed->update_next = time() + 86400 * $feed->update_wait;
        $feed->save();
        return $next;
    }
}