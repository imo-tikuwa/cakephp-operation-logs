<?php
declare(strict_types=1);

namespace OperationLogs\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OperationLogsHourlyFixture
 */
class OperationLogsHourlyFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'operation_logs_hourly';

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}
