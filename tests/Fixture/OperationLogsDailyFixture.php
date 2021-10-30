<?php
declare(strict_types=1);

namespace OperationLogs\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OperationLogsDailyFixture
 */
class OperationLogsDailyFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'operation_logs_daily';

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
