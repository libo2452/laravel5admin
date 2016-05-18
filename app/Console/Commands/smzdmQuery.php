<?php
namespace App\Console\Commands;

use App\Models\SmzdmFeed;
use Illuminate\Console\Command;

class smzdmQuery extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smzdm:query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $smzdmQueryUrl = 'http://feed.smzdm.com/';
        $response = file_get_contents($smzdmQueryUrl);
        $parser = xml_parser_create();
        //xml_parser_set_option -- 为指定 XML 解析进行选项设置
        xml_parser_set_option($parser , XML_OPTION_SKIP_WHITE , 1);
        //xml_parse_into_struct -- 将 XML 数据解析到数组$values中
        xml_parse_into_struct($parser , $response , $values , $idx);
        //xml_parser_free -- 释放指定的 XML 解析器
        xml_parser_free($parser);
        $title = $link = $focus_pic = $pubdate = $description = $content = '';
        $is_item = 0;
        foreach ( $values as $val ) {
            $tag = $val["tag"];
            $type = $val["type"];
            if ( isset($val['value']) ) {
                $value = $val["value"];
            }
            //标签统一转为小写
            $tag = strtolower($tag);
            $this->info($tag . $type);
            if ( $tag == "item" && $type == "open" ) {
                $is_item = 1;
            } else {
                if ( $tag == "item" && $type == "close" ) {
                    $is_item = 0;
//                    dd($title , $link , $focus_pic , $pubdate , $description , $content);
                    $isExist = SmzdmFeed::where('link',$link)->count();
                    if( $isExist ){
                       continue;
                    }
                    $data = [
                        'title'       => $title ,
                        'link'        => $link ,
                        'focus_pic'   => $focus_pic ,
                        'pubdate'     => strtotime($pubdate) ,
                        'description' => $description ,
                        'content'     => $content ,
                    ];
                    SmzdmFeed::create($data);
//                SmzdmFeed::create($data);

            }
            }
            //仅读取item标签中的内容
            if ( $is_item == 1 ) {
                if ( $tag == "title" ) {
                    $title = $value;
                }
                if ( $tag == "link" ) {
                    $link = $value;
                }
                if ( $tag == "focus_pic" ) {
                    $focus_pic = $value;
                }
                if ( $tag == "pubdate" ) {
                    $pubdate = $value;
                }
                if ( $tag == "description" ) {
                    $description = $value;
                }
                if ( $tag == "content:encoded" ) {
                    $content = $value;
                }
            }
        }
        dd(__LINE__);
//        dd($values);
        $content = (array)simplexml_load_string($content);
        $items = $content['channel']->item;
//        dd(count($items));
        foreach ( $items as $item ) {
            $item = (array)$item;
            dd($item);
        }
    }
}
