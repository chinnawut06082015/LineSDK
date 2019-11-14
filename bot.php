<?php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
// include composer autoload
require_once __DIR__ . '/vendor/autoload.php';
 
// การตั้งเกี่ยวกับ bot
require_once 'bot_settings.php';
 
// กรณีมีการเชื่อมต่อกับฐานข้อมูล
//require_once("dbconnect.php");
 
///////////// ส่วนของการเรียกใช้งาน class ผ่าน namespace
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
//use LINE\LINEBot\Event;
//use LINE\LINEBot\Event\BaseEvent;
//use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
 
// เชื่อมต่อกับ LINE Messaging API
$httpClient = new CurlHTTPClient(LINE_MESSAGE_ACCESS_TOKEN);
$bot = new LINEBot($httpClient, array('channelSecret' => LINE_MESSAGE_CHANNEL_SECRET));
 
// คำสั่งรอรับการส่งค่ามาของ LINE Messaging API
$content = file_get_contents('php://input');
 
// แปลงข้อความรูปแบบ JSON  ให้อยู่ในโครงสร้างตัวแปร array
$events = json_decode($content, true);
if(!is_null($events)){
    // ถ้ามีค่า สร้างตัวแปรเก็บ replyToken ไว้ใช้งาน
    $replyToken = $events['events'][0]['replyToken'];
    $typeMessage = $events['events'][0]['message']['type'];
    $userMessage = $events['events'][0]['message']['text'];
    switch ($typeMessage){
        case 'text':
            switch ($userMessage) {
                case "เบอร์ติดต่อแต่ละสาขา":
                    $textReplyMessage = "สาขาแจ้งวัฒนะ 2533"."\n"."สาขาหนองแขม 3533"."\n"."สาขาบางกอกน้อย 4533"."\n"."สาขาหนองแขม 3533";
                    break;
                case "B":
                    $textReplyMessage = "คุณพิมพ์ B";
                    break;
                case (preg_match('/^create-/',$userMessage) ? true : false): // เมื่อพิมพ์คำว่า create- เข้ามา
                    $respRichMenu = $bot->createRichMenu(
                            new RichMenuBuilder(
                                new RichMenuSizeBuilder(1686,2500), // ขนาด rich menu ปกติจะไม่เปลี่ยน แปลง
                                true, // เปิดให้แสดง * จะไม่แสดงทันที 
                                "Rich Menu 1", // ชื่อ rich menu
                                "เมนู", // ข้อความที่จะแสดงที่แถบเมนู
                                array( // array ของ action แต่ละบริเวณ
                                    new RichMenuAreaBuilder( // action ที่ 1
                                        new RichMenuAreaBoundsBuilder(0,0,1250,1686),// พื้นที่ A (x,y,width,height)
                                        new MessageTemplateActionBuilder('m','Area A') // เปลี่ยนเฉพาะตัวที่ 2 ตามต้องการ 'Area A'
                                    ),
                                    new RichMenuAreaBuilder( // action ที่ 2
                                        new RichMenuAreaBoundsBuilder(1250,0,1250,1686), // พื้นที่ B (x,y,width,height)
                                        new UriTemplateActionBuilder('u','http://niik.in') // เปลี่ยนเฉพาะตัวที่ 2 ตามต้องการ 'http://niik.in'
                                    ),                                                                                  
                                )
                            )
                        );          
                        // ให้ bot แจ้งกลับเกี่ยวกับ สถานะการสร้าง
                        $textReplyMessage = " การสร้าง Rich Menu ".$respRichMenu->getRawBody();
                        $replyData = new TextMessageBuilder($textReplyMessage);                                                     
                    break;
                default:
                    $textReplyMessage = " คุณไม่ได้พิมพ์ A และ B";
                    break;                                      
            }
            break;
        default:
            $textReplyMessage = json_encode($events);
            break;  
    }
}
// ส่วนของคำสั่งจัดเตียมรูปแบบข้อความสำหรับส่ง
$textMessageBuilder = new TextMessageBuilder($textReplyMessage);
 
//l ส่วนของคำสั่งตอบกลับข้อความ
$response = $bot->replyMessage($replyToken,$textMessageBuilder);
if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}
 
// Failed
echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
?>