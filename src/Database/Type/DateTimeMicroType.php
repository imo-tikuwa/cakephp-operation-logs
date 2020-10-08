<?php
namespace OperationLogs\Database\Type;

use Cake\Database\Type\DateTimeType;
use Cake\Database\DriverInterface;

/**
 * マイクロ秒まで保持するDateTime型のタイプ
 *
 */
class DateTimeMicroType extends DateTimeType {

	protected $_format = 'Y-m-d H:i:s.u';

	/**
     * {@inheritDoc}
     *
     * @param mixed $value Value to be converted to PHP equivalent
     * @param \Cake\Database\DriverInterface $driver Object from which database preferences and configuration will be extracted
     * @return \DateTimeInterface|null
     */
	public function toPHP($value, DriverInterface $driver) {
		if ($value === null || strpos($value, '0000-00-00') === 0) {
			return null;
		}

		// if (strpos($value, '.') !== false) {
		// 	list($value) = explode('.', $value);
		// }

		$instance = clone $this->_datetimeInstance;
		$tmp_obj = $instance->createFromFormat('Y-m-d H:i:s.u', $value);
		return $tmp_obj;
	}

}