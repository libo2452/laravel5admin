<?php

namespace App\Console\Commands;
use App\Jobs\GetUrlContentAndSaveToArticle;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Cache;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\DomCrawler\Crawler;
use Readability\Readability;

use Illuminate\Console\Command;

class Crawl_toutiao extends Command
{
    use DispatchesJobs;
    private $concurrency    = 5;  // 同时并发抓取
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:toutiao';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取头条数据';

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
     * @return mixed
     */
    public function handle()
    {
        $today = Date('Y-m-d');
        $url = 'http://toutiao.io/prev/2016-06-30';
        $url = 'https://zhuanlan.zhihu.com/p/21463650?hmsr=toutiao.io&utm_medium=toutiao.io&utm_source=toutiao.io';
        $url = 'http://toutiao.io/j/bxbm63';
        $urls = [];
        for( $i=0;$i<10;$i++ ){
            $urls[] = 'http://toutiao.io/prev/'.date('Y-m-d',strtotime("-$i days"));
        }
        $this->getMultiHttpRequest($urls);
        exit;
        dd($urls);
        $contentCacheKey = __CLASS__.md5($url);
        $htmlContent = Cache::get($contentCacheKey,function() use($contentCacheKey,$url){
            $client = new Client();
            $response = $client->get($url);
            $htmlContent = $response->getBody()->getContents();
            Cache::put($contentCacheKey,$htmlContent,60*4);
            return $htmlContent;
        });
//        var_dump($htmlContent);
        $this->getclearHtml($htmlContent,$url);exit;
        $this->getLinksFromHtmlContent($htmlContent,$url);
    }

    private function getMultiHttpRequest($urls){
        $this->info('异步请求开始:'.count($urls));
        $client = new Client();
        $requests = function ($urls) use ($client) {
            foreach ($urls as $url) {
                yield function() use ($client, $url) {
                    return $client->getAsync($url);
                };
            }
        };
        $pool = new Pool($client, $requests($urls), [
            'concurrency' => $this->concurrency,
            'fulfilled'   => function ($response, $index){
                $res = $response->getBody()->getContents();
//                $this->getclearHtmlFromResponse($res,$index);
                $this->info("请求第 $index 个请求");
                $urls1 = $this->getLinksFromHtmlContent($res,$index);
                $this->info('获取url列表为'.implode(PHP_EOL,$urls1));
//                $this->getMultiHttpRequest($urls1);
//                $this->countedAndCheckEnded();
            },
            'rejected' => function ($reason, $index){
                $this->error("rejected" );
                $this->error("$index rejected reason: " . $reason );
//                $this->countedAndCheckEnded();
            },
        ]);
        // 开始发送请求
        $promise = $pool->promise();
        $promise->wait();
    }



    private function getclearHtmlFromResponse($response,$index){
        Log::info($response);
        $readability = new Readability($response);
// $readability = new Readability($html, $url, 'libxml', false);
        $result = $readability->init();
        if ($result) {
            echo $readability->getTitle()->textContent;
//            echo $readability->getContent()->textContent;
        } else {
            echo 'Looks like we couldn\'t find the content. :(';
        }
    }

    private function getclearHtml($html,$url){
        $readability = new Readability($html, $url);
        $logger = new Logger('readability');
        $logger->pushHandler(new StreamHandler(storage_path().'/this.log', Logger::DEBUG));
        $readability->setLogger($logger);
// or without Tidy
// $readability = new Readability($html, $url, 'libxml', false);
        $result = $readability->init();
        if ($result) {
            // display the title of the page
            echo $readability->getTitle()->textContent;
            // display the *readability* content
            echo $readability->getContent()->textContent;
        } else {
            echo 'Looks like we couldn\'t find the content. :(';
        }
    }

    private function getLinksFromHtmlContent($html){
        $urls = [];
        $crawler = new Crawler($html);
        $crawler->filter('div[class="content"]')->each(function (Crawler $node) use (&$urls) {
//            $href = $node->nodeName;
            $url = $node->attr('data-url');
            $url = str_replace('/posts/','/j/',$url);
            $urls[] = $url;
            $this->info($url);
            $this->dispatch(new GetUrlContentAndSaveToArticle($url));
//            var_dump($node->attr('data-url'),$node->text());
//            dd($node->filter('h3 > a')->text());
//            dd($node);
        });
//        $this->info(implode(PHP_EOL,$urls));
        return $urls;
    }
}
