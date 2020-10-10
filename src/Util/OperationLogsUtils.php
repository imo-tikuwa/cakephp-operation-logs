<?php

namespace OperationLogs\Util;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * プラグイン内で使用する関数をまとめたUtilクラス
 * @author tikuwa
 */
class OperationLogsUtils {

	/**
	 * 文字列$haystackは$needleで始まる？
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	/**
	 * 文字列$haystackは$needleで終わる？
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function endsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, - strlen($needle)) === $needle;
	}

	/**
	 * 集計データを取得する
	 * @param string $date_type
	 * @param string $summary_type
	 * @param \DateTime $target_date
	 * @return NULL
	 */
	public static function findSummaryLogs($date_type = OL_DATE_TYPE_DAILY, $summary_type = OL_SUMMARY_TYPE_ALL, \DateTime $target_date = null)
	{
		if (is_null($target_date)) {
			$target_date = new \DateTime();
		}

		$summary_logs = null;
		switch ($date_type) {
			case OL_DATE_TYPE_HOURLY:
				$summary_logs = self::findHourlySummaryLogs($summary_type, $target_date);
				break;
			case OL_DATE_TYPE_DAILY:
				$summary_logs = self::findDailySummaryLogs($summary_type, $target_date);
				break;
			case OL_DATE_TYPE_MONTHLY:
				$summary_logs = self::findMonthlySummaryLogs($summary_type, $target_date);
				break;
			default:
				break;
		}
		return $summary_logs;
	}

	/**
	 * 1時間毎の集計データを取得する
	 *
	 * 引数の日付の集計データを0時～23時で分けて取得します
	 *
	 * @param string $summary_type グルーピング定数 以下のいずれかを指定してください
	 * OL_SUMMARY_TYPE_ALL = グルーピングしなかった際の集計データを返します
	 * OL_SUMMARY_TYPE_IP = IPアドレスでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_USER_AGENT = ユーザーエージェントでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_URL = リクエストURLでグルーピングした集計データを返します
	 *
	 * @param \DateTime $target_date 基準日
	 */
	public static function findHourlySummaryLogs($summary_type = OL_SUMMARY_TYPE_ALL, \DateTime $target_date = null)
	{
		if (is_null($target_date)) {
			$date = new \DateTime();
		} else {
			// 引数のDateTimeオブジェクトへのmodifyによる影響を残さないようcloneする
			$date = clone $target_date;
		}

		$operation_logs_hourly = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogsHourly');
		$format_date = $date->format('Y-m-d');
		$slash_format_date = $date->format('Y/m/d');
		$summary_logs = $operation_logs_hourly->find()->where([
				'summary_type' => $summary_type,
				'target_time >=' => "{$format_date} 00:00:00",
				'target_time <=' => "{$format_date} 23:59:59"
		])
		->orderAsc('target_time')
		->enableHydration(false)
		->toArray();

		// グルーピングをキーとした配列に持ち替え、グルーピング内のデータはKey:日付、Value:カウンタ
		$summary_logs = Hash::combine($summary_logs, '{n}.id', '{n}', '{n}.groupedby');
		foreach ($summary_logs as $groupedby => $summary_log) {
			$summary_logs[$groupedby] = Hash::combine($summary_log, '{n}.target_time', '{n}.counter');
		}

		// 抜けてる時間をカウント0で補間
		foreach ($summary_logs as $groupedby => $summary_log) {
			for ($hour = 0; $hour <= 23; $hour++) {
				$search_date = $slash_format_date . " " . sprintf('%02d', $hour) . ":00";
				if (!array_key_exists($search_date, $summary_log)) {
					$summary_log[$search_date] = 0;
				}
			}
			ksort($summary_log);
			$summary_logs[$groupedby] = $summary_log;
		}

		return $summary_logs;
	}

	/**
	 * 日毎の集計データを取得する
	 *
	 * 引数の日付を含む月の集計データを取得します。
	 *
	 * @param string $summary_type グルーピング定数 以下のいずれかを指定してください
	 * OL_SUMMARY_TYPE_ALL = グルーピングしなかった際の集計データを返します
	 * OL_SUMMARY_TYPE_IP = IPアドレスでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_USER_AGENT = ユーザーエージェントでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_URL = リクエストURLでグルーピングした集計データを返します
	 *
	 * @param \DateTime $target_date 基準日
	 */
	public static function findDailySummaryLogs($summary_type = OL_SUMMARY_TYPE_ALL, \DateTime $target_date = null)
	{
		if (is_null($target_date)) {
			$date = new \DateTime();
		} else {
			// 引数のDateTimeオブジェクトへのmodifyによる影響を残さないようcloneする
			$date = clone $target_date;
		}

		$operation_logs_daily = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogsDaily');
		$target_ym = $date->format('Y/m');
		$from_date = $date->format('Y-m-01');
		$to_date = $date->modify('last day of this month')->format('Y-m-d');
		$last_day = $date->format('d');
		$summary_logs = $operation_logs_daily->find()->where([
				'summary_type' => $summary_type,
				'target_ymd >=' => $from_date,
				'target_ymd <=' => $to_date
		])
		->orderAsc('target_ymd')
		->enableHydration(false)
		->toArray();

		// グルーピングをキーとした配列に持ち替え、グルーピング内のデータはKey:日付、Value:カウンタ
		$summary_logs = Hash::combine($summary_logs, '{n}.id', '{n}', '{n}.groupedby');
		foreach ($summary_logs as $groupedby => $summary_log) {
			$summary_logs[$groupedby] = Hash::combine($summary_log, '{n}.target_ymd', '{n}.counter');
		}

		// 抜けてる日付をカウント0で補間
		foreach ($summary_logs as $groupedby => $summary_log) {
			for ($day = 1; $day <= $last_day; $day++) {
				$search_date = $target_ym . "/" . sprintf('%02d', $day);
				if (!array_key_exists($search_date, $summary_log)) {
					$summary_log[$search_date] = 0;
				}
			}
			ksort($summary_log);
			$summary_logs[$groupedby] = $summary_log;
		}

		return $summary_logs;
	}

	/**
	 * 月毎の集計データを取得する
	 *
	 * 引数の日付を含む月を基準として過去12か月の集計データを取得します。
	 *
	 * @param string $summary_type グルーピング定数 以下のいずれかを指定してください
	 * OL_SUMMARY_TYPE_ALL = グルーピングしなかった際の集計データを返します
	 * OL_SUMMARY_TYPE_IP = IPアドレスでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_USER_AGENT = ユーザーエージェントでグルーピングした集計データを返します
	 * OL_SUMMARY_TYPE_URL = リクエストURLでグルーピングした集計データを返します
	 *
	 * @param \DateTime $target_date 基準日
	 */
	public static function findMonthlySummaryLogs($summary_type = OL_SUMMARY_TYPE_ALL, \DateTime $target_date = null)
	{
		if (is_null($target_date)) {
			$date = new \DateTime();
		} else {
			// 引数のDateTimeオブジェクトへのmodifyによる影響を残さないようcloneする
			$date = clone $target_date;
		}

		$operation_logs_monthly = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogsMonthly');
		$to_ym = $date->format('Ym');
		$from_ym = $date->modify('-12 month')->format('Ym');
		$summary_logs = $operation_logs_monthly->find()->where([
				'summary_type' => $summary_type,
				'target_ym >=' => $from_ym,
				'target_ym <=' => $to_ym
		])
		->orderAsc('target_ym')
		->enableHydration(false)
		->toArray();

		// グルーピングをキーとした配列に持ち替え、グルーピング内のデータはKey:日付、Value:カウンタ
		$summary_logs = Hash::combine($summary_logs, '{n}.id', '{n}', '{n}.groupedby');
		foreach ($summary_logs as $groupedby => $summary_log) {
			$summary_logs[$groupedby] = Hash::combine($summary_log, '{n}.target_ym', '{n}.counter');
		}

		// 抜けてる月をカウント0で補間
		foreach ($summary_logs as $groupedby => $summary_log) {
			$search_month_date = new \DateTime("{$from_ym}01");
			$one_year_count_arr = [];
			for ($add_month = 0; $add_month < 12; $add_month++) {
				$search_month_date->modify('+1 month');
				$search_month_ym = $search_month_date->format('Ym');
				$one_year_count_arr[$search_month_ym] = 0;
			}
			// https://stackoverflow.com/questions/162032/merge-two-arrays-as-key-value-pairs-in-php
			$keys = array_merge(array_keys($one_year_count_arr), array_keys($summary_log));
			$vals = array_merge($one_year_count_arr, $summary_log);
			$summary_logs[$groupedby] = array_combine($keys, $vals);
		}

		return $summary_logs;
	}
}
