<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Google\Cloud\Firestore\FirestoreClient;

class Scraping extends Command
{
    /**
     * the name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scraping';

    /**
     * the console command description.
     *
     * @var string
     */
    protected $description = 'command description';

    protected $client;
    protected $db;
    protected $crawler;
    protected $projectId = 'laraton-opas-dev';

    /**
     * shortener
     */
    protected $gym_shortener = [
        'スポーツセンター' => '',
        'ゼット' => '',
        'プール' => '',
        'サンエイワーク' => '',
        'フィットネス２１' => '',
        'ＨＳＴ' => '',
        '丸善インテックアリーナ大阪（中央体育館）' => 'インテックアリーナ',
        '体育館' => '',
        '体育場' => '',
        '明治スポーツプラザ浪速' => '浪速',
        '明治スポーツプラザ' => '',
        '　' => ' ',
    ];
    protected $room_shortener = [
        'サブアリーナ' => '',
        '体育場' => '',
        '１／２面' => '',
        '体育館' => '',
        '第１' => '1',
        '第２' => '2',
    ];


    /**
     * Firestore Information
     * @var string
     */
    protected $collection = 'reservations';

    /**
     * create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
        $this->db = new FirestoreClient([
            'projectId' => $this->projectId,
        ]);
        date_default_timezone_set('Asia/Tokyo');
    }

    /**
     * execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->login();
        $this->yoyaku();
        $this->show_reservation(4);
        $this->get_reservations();
    }

    private function login()
    {
        try {
            $login_page = $this->client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
            $form = $login_page->filter('#formMain')->form();
            $form['action'] = 'Enter';
            $form['txtProcId'] = '/menu/Login';
            $form['txtRiyoshaCode'] = env('txtRiyoshaCode');
            $form['txtPassWord'] = env('txtPassWord');
            $this->crawler = $this->client->submit($form);
        } catch (exception $e) {
            error_log(__method__ . ' exception was encountered: ' . get_class($e) . ' ' . $e->getmessage());
        }
    }

    private function yoyaku()
    {
        try {
            $form = $this->crawler->filter('form#formMain')->form();
            $form['action'] = 'Enter';
            $form['txtProcId'] = '/menu/Menu';
            $form['txtFunctionCode'] = 'YoyakuQuery';
            $this->crawler = $this->client->submit($form);
        } catch (exception $e) {
            error_log(__method__ . ' exception was encountered: ' . get_class($e) . ' ' . $e->getmessage());
        }
    }

    private function show_reservation($month)
    {
        // 抽選日(13日)より前は当月、抽選日(13日)以降は翌月
        if (date('d') < '13') {
            $month = date("Ym");
        } else {
            $month = date("Ym", strtotime("1 month"));
        }
        try {
            $form = $this->crawler->filter('form#formMain')->form();
            $form['txtProcId'] = '/yoyaku/RiyoshaYoyakuList';
            $form['action'] = 'Setup';
            $form['selectedYoyakuUniqKey'] = '';
            $form['hiddenCorrectRiyoShinseiYM'] = '';
            $form['hiddenCollectDisplayNum'] = '5';
            $form['pageIndex'] = '1';
            $form['printedFlg'] = '';
            $form['riyoShinseiYM']->select($month);
            $form['reqDisplayInfoNum']->select('50');
            $this->crawler = $this->client->submit($form);
        } catch (exception $e) {
            error_log(__method__ . ' exception was encountered: ' . get_class($e) . ' ' . $e->getmessage());
        }
    }

    private function get_reservations()
    {
        try {
            $reservations = array();
            $this->crawler->filter('div.tablebox table tr')->each(function ($tr) use (&$reservations) {
                if (strpos($tr->text(), 'バドミントン') !== false && strpos($tr->text(), '取消済み') === false) {
                    $reservations[] = $this->get_reservation($tr->children());
                }
            });

            var_export($reservations);
            $now = date('Ymd_His');
//            $doc = $this->db->collection($this->collection)->document($now);
//            $doc->set($reservations);
//            foreach ($reservations as $reservation) {
//                $doc = $this->db->collection($this->collection)->document($now);
//                $doc->set($reservation);
//            }
        } catch (Exception $e) {
            error_log(__METHOD__ . ' Exception was encountered: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }

    private function get_reservation($rd_list): array
    {
        $date_str_jp = $rd_list->eq(0)->text();
        preg_match("/([0-9]*)年([0-9]*)月([0-9]*)日/", $date_str_jp, $date_ary);
        $date_str_en = sprintf("%04.4d%02.2d%02.2d", $date_ary[1], $date_ary[2], $date_ary[3]);

        $gym_and_room = explode(' ', $rd_list->eq(1)->text());
        $gym = $this->shorten($gym_and_room[0], $this->gym_shortener);
        $room = $this->shorten($gym_and_room[1], $this->room_shortener);

        $timeframe = str_replace(' ', '', $rd_list->eq(2)->text());

        $fee = str_replace('円 全額未納', '', $rd_list->eq(4)->text());
        $fee = str_replace(',', '', $fee);

        return [
            'date' => $date_str_en,
            'gym' => $gym,
            'room' => $room,
            'timeframe' => $timeframe,
            'fee' => $fee
        ];
    }

    private function shorten($str, $shortener)
    {
        $search = array_keys($shortener);
        $replace = array_values($shortener);
        return str_replace($search, $replace, $str);
    }
}
