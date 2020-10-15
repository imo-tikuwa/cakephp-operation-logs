<?php
namespace OperationLogs\Model\Entity;

use Cake\ORM\Entity;
use OperationLogs\Util\OperationLogsUtils;

/**
 * OperationLog Entity
 *
 * @property int $id
 * @property string $client_ip
 * @property string $user_agent
 * @property string $request_url
 * @property \Cake\I18n\FrozenTime $request_time
 * @property \Cake\I18n\FrozenTime $response_time
 *
 * @property float $exec_time
 */
class OperationLog extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'client_ip' => true,
        'user_agent' => true,
        'request_url' => true,
        'request_time' => true,
        'response_time' => true
    ];

    /**
     * リクエストの実行時間を返す(単位：秒)
     * @return float
     */
    protected function _getExecTime()
    {
        // Ymはさすがにいらないと思うので省略
        $request_time = $this->request_time->format('dHis.u');
        $response_time = $this->response_time->format('dHis.u');
        $diff = $response_time - $request_time;
        if ($diff <= 0) {
            return 0;
        }

        // リクエスト日時、レスポンス日時のデータの末尾を元に四捨五入の桁を設定
        $precision = (OperationLogsUtils::endsWith($request_time, '000') && OperationLogsUtils::endsWith($response_time, '000')) ? 3 : 6;
        $diff = round($diff, $precision, PHP_ROUND_HALF_UP);

        return $diff;
    }
}
