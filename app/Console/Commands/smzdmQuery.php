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
        //xml_parser_set_option -- Ϊָ�� XML ��������ѡ������
        xml_parser_set_option($parser , XML_OPTION_SKIP_WHITE , 1);
        //xml_parse_into_struct -- �� XML ���ݽ���������$values��
        xml_parse_into_struct($parser , $response , $values , $idx);
        //xml_parser_free -- �ͷ�ָ���� XML ������
        xml_parser_free($parser);
        $title = $link = $focus_pic = $pubdate = $description = $content = '';
        $is_item = 0;
        foreach ( $values as $val ) {
            $tag = $val["tag"];
            $type = $val["type"];
            if ( isset($val['value']) ) {
                $value = $val["value"];
            }
            //��ǩͳһתΪСд
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
            //����ȡitem��ǩ�е�����
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
