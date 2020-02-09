<?php
namespace OperationLogsPlugin\Model\Entity;

use Cake\ORM\Entity;

/**
 * OperationLog Entity
 *
 * @property int $id
 * @property string $client_ip
 * @property string $user_agent
 * @property string $request_url
 * @property \Cake\I18n\FrozenTime $request_time
 * @property \Cake\I18n\FrozenTime $response_time
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
}
