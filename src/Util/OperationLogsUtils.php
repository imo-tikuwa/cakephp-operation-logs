<?php

namespace OperationLogs\Util;

/**
 * プラグイン内で使用する関数をまとめたUtilクラス
 * @author tikuwa
 *
 */
class OperationLogsUtils {

	/**
	 * 文字列$haystackは$needleで始まる？
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function starts_with($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	/**
	 * 文字列$haystackは$needleで終わる？
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function ends_with($haystack, $needle) {
		return $needle === "" || substr($haystack, - strlen($needle)) === $needle;
	}
}



