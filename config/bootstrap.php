<?php

use Cake\Core\Configure;
use Cake\Database\Type;

// DateTimeMicroType追加
Type::map('datetimemicro', 'OperationLogs\Database\Type\DateTimeMicroType');
Type::build('datetimemicro')->useImmutable();

define('OL_SUMMARY_TYPE_ALL',			"all");
define('OL_SUMMARY_TYPE_IP',			"ip");
define('OL_SUMMARY_TYPE_USER_AGENT',	"ua");
define('OL_SUMMARY_TYPE_URL',			"url");

define('OL_DATE_TYPE_HOURLY',			"hourly");
define('OL_DATE_TYPE_DAILY',			"daily");
define('OL_DATE_TYPE_MONTHLY',			"monthly");

try {
	// 操作ログのコード定義作成
	Configure::write('OperationLogs', [
			// 集計種別
			"summary_types" => [
					OL_SUMMARY_TYPE_ALL			=> OL_SUMMARY_TYPE_ALL,
					OL_SUMMARY_TYPE_IP			=> "client_ip",
					OL_SUMMARY_TYPE_USER_AGENT	=> "user_agent",
					OL_SUMMARY_TYPE_URL			=> "request_url"
			],
			// 集計種別(日本語)
			"summary_types_jp" => [
					OL_SUMMARY_TYPE_ALL			=> "なし",
					OL_SUMMARY_TYPE_IP			=> "IPアドレス別",
					OL_SUMMARY_TYPE_USER_AGENT	=> "ユーザーエージェント別",
					OL_SUMMARY_TYPE_URL			=> "リクエストURL別"
			],
			// 集計間隔
			"date_type" => [
					OL_DATE_TYPE_HOURLY 		=> OL_DATE_TYPE_HOURLY,
					OL_DATE_TYPE_DAILY			=> OL_DATE_TYPE_DAILY,
					OL_DATE_TYPE_MONTHLY		=> OL_DATE_TYPE_MONTHLY,
			],
			// 集計間隔(日本語)
			"date_type_jp" => [
					OL_DATE_TYPE_HOURLY 		=> "1時間毎",
					OL_DATE_TYPE_DAILY			=> "日毎",
					OL_DATE_TYPE_MONTHLY		=> "月毎",
			]
	]);
} catch (\Exception $e) {
	exit($e->getMessage() . "\n");
}
