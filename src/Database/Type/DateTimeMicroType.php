<?php
namespace OperationLogs\Database\Type;

use Cake\Database\Type\DateTimeType;
use Cake\Database\Driver;

/**
 * マイクロ秒まで保持するDateTime型のタイプ
 * @see https://teratail.com/questions/67705
 *
 */
class DateTimeMicroType extends DateTimeType {

	protected $_format = 'Y-m-d H:i:s.u';

	public function toPHP($value, Driver $driver) {
		if ($value === null || strpos($value, '0000-00-00') === 0) {
			return null;
		}

		if (strpos($value, '.') !== false) {
			//list($value) = explode('.', $value);
		}

		$instance = clone $this->_datetimeInstance;
		$tmp_obj = $instance->createFromFormat('Y-m-d H:i:s.u', $value);
		return $tmp_obj;
	}

}