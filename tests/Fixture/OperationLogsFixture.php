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
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => 'ID', 'autoIncrement' => true, 'precision' => null],
        'client_ip' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'クライアントIP', 'precision' => null],
        'user_agent' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ユーザーエージェント', 'precision' => null],
        'request_url' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'リクエストURL', 'precision' => null],
        'request_time' => ['type' => 'datetimefractional', 'length' => null, 'precision' => 3, 'null' => false, 'default' => null, 'comment' => 'リクエスト日時'],
        'response_time' => ['type' => 'datetimefractional', 'length' => null, 'precision' => 3, 'null' => false, 'default' => null, 'comment' => 'レスポンス日時'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // phpcs:enable
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
