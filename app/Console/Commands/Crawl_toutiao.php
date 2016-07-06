<?php

namespace App\Console\Commands;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Cache;
use Symfony\Component\DomCrawler\Crawler;
use Readability\Readability;

use Illuminate\Console\Command;

class Crawl_toutiao extends Command
{
    private $concurrency    = 10;  // 同时并发抓取
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
        $contentCacheKey = __CLASS__.md5($url);
        $htmlContent = Cache::get($contentCacheKey,function() use($contentCacheKey,$url){
            $client = new Client();
            $response = $client->get($url);
            $htmlContent = $response->getBody()->getContents();
            Cache::put($contentCacheKey,$htmlContent,60*4);
            return $htmlContent;
        });
        var_dump($htmlContent);
        $this->getclearHtml($htmlContent,$url);exit;
        $this->getLinksFromHtmlContent($htmlContent,$url);
    }

    private function getclearHtml($html,$url){

        $readability = new Readability($html, $url);
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
        $crawler = new Crawler($html);
        $crawler->filter('div[class="content"]')->each(function (Crawler $node) {
//            $href = $node->nodeName;
            var_dump($node->attr('data-url'),$node->text());
            dd($node->filter('h3 > a')->text());
//            dd($node);
        });
        exit;
        $crawler = $crawler->filter('div');
        foreach ($crawler as $domElement) {
            var_dump($domElement->nodeName);
            dd($domElement);
        }
    }
}
