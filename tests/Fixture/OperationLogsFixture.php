<?php
declare(strict_types=1);

namespace OperationLogs\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OperationLogsFixture
 */
class OperationLogsFixture extends TestFixture
{
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
