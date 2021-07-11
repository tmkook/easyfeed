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
            try{
                $cli = new SpiderCli($item->url);
                $this->updateFeeds($item,$cli,$item->url);
            }catch(\Exception $e){
                $item->state = Feed::FAIL;
                $item->save();
            }
        }
    }

    //老站点更新首页
    public function newlyspider(){
        $time = time();
        $newly = Feed::where('state',Feed::SUCCESS)->where('update_next','<',$time);
        foreach($newly->cursor() as $item){
            try{
                $cli = new SpiderCli($item->url);
                $cli->load($item->url);
                $this->updateFeed($item,$cli);
            }catch(\Exception $e){
                sleep(1);
                $item->state = Feed::FAIL;
                $item->save();
                $cli->load($item->url);
                $this->updateFeed($item,$cli);
            }
        }
    }

    public function mainspiderone(){
        $news = News::where('state',News::CHECK)->inRandomOrder()->first();
        if(empty($news)) return;
        try{
            $feed = Feed::find($news->feed_id);
            $cli = new SpiderCli($feed->url);
            $cli->load($news->url);
            $this->updateMain($feed,$news,$cli);
        }catch(\Exception $e){
            sleep(1);
            $cli->load($news->url);
            $this->updateMain($feed,$news,$cli);
        }
    }

    //更新全文
    public function mainspider(){
        $upfeed = 0;
        $newly = News::where('state',News::CHECK)->limit(1000)->inRandomOrder();
        foreach($newly->cursor() as $news){
            try{
                $feed = Feed::find($news->feed_id);
                //同站点间隔抓取
                if($feed->id == $upfeed && $feed->net_wait > 0){
                    sleep($feed->net_wait);
                }
                $cli = new SpiderCli($feed->url);
                $cli->load($news->url);
                $this->updateMain($feed,$news,$cli);
                $upfeed = $feed->id;
            }catch(\Exception $e){
                $news->state = News::FAIL;
                $news->save();
            }
        }
    }

    //递归更新
    public function feeds(){
        $id = $this->argument('arg');
        $feed = Feed::find($id);
        $cli = new SpiderCli($feed->url);
        $this->updateFeeds($feed,$cli,$feed->url);
    }

    //更新一页
    public function feed(){
        $id = $this->argument('arg');
        $feed = Feed::find($id);
        $cli = new SpiderCli($feed->url);
        $cli->load($feed->url);
        $this->updateFeed($feed,$cli);
    }

    //更新文章
    public function main(){
        $id = $this->argument('arg');
        $news = News::find($id);
        $feed = Feed::find($news->feed_id);
        $cli = new SpiderCli($feed->url);
        $cli->load($news->url);
        $this->updateMain($feed,$news,$cli);
    }

    //更新文章内容
    protected function updateMain($feed,$news,$cli){
        $main = $cli->getMain($feed->main_dom,$feed->del_dom);
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
        $this->info($news->url.' -- '.$news->state);
        return true;
    }

    //递归更新全站
    protected function updateFeeds($feed,$cli,$url){
        $cli->load($url);
        $next = $this->updateFeed($feed,$cli);
        if($next){
            //同站点间隔抓取
            $this->info($next);
            if($feed->net_wait > 0){
                sleep($feed->net_wait);
            }
            $this->updateFeeds($feed,$cli,$next);
        }
    }

    //更新一页
    protected function updateFeed($feed,$cli){
        $newly = 0;
        $list = $cli->getList($feed->list_dom);
        $next = $cli->getNext($feed->next_dom);
        if(empty($feed->title)){
            $feed->title = $cli->getTitle();
            $feed->icon = $cli->getIcon();
            $feed->description = $cli->getMeta('description');
        }
        if(empty($list)){
            $feed->state = Feed::FAIL;
        }else{
            $feed->state = Feed::SUCCESS;
            foreach($list as $item){
                $uuid = md5($item['url']);
                $news = News::where('uuid',$uuid)->where('feed_id',$feed->id)->first();
                if(empty($item['title'])){
                    continue;
                }else if(empty($news)){
                    $newly++;
                    $news = News::onlyTrashed()->first();
                    $news = $news? $news : new News;
                }else if($news->state == News::SUCCESS){
                    continue;
                }
                $news->feed_id = $feed->id;
                $news->uuid = $uuid;
                $news->url = $item['url'];
                $news->title = $item['title'];
                $news->state = News::CHECK;
                $news->deleted_at = null;
                $news->save();
                $this->info($news->url.' -- '.$news->title);
            }
        }
        if($newly > 0){
            $feed->update_wait = 1;
        }else{
            $feed->update_wait += 1;
        }
        if($feed->update_wait > 168){
            $feed->update_wait = 168;
        }
        $feed->update_next = time() + 3600 * $feed->update_wait;
        $feed->save();
        return $next;
    }

    public function test(){
        // $news = News::onlyTrashed()->first();
        // $news = $news? $news : new News;
        // dd($news);
        // $cli = new SpiderCli('https://ganjiacheng.cn');
        // $uri = 'test/艺术硕士';
        // echo $cli->url($uri);
        // $item = Feed::find(9);
        // $url = 'https://www.ruanyifeng.com/blog/2004/01/';
        // $cli = new SpiderCli($item->url);
        // $this->updateFeeds($item,$cli,$url);

        
    }
}