<?php
namespace OperationLogs\Model\Entity;

use Cake\ORM\Entity;

/**
 * OperationLogsMonthly Entity
 *
 * @property int $id
 * @property int $target_ym
 * @property string $summary_type
 * @property string|null $groupedby
 * @property int $counter
 */
class OperationLogsMonthly extends Entity
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
        'target_ym' => true,
        'summary_type' => true,
        'groupedby' => true,
        'counter' => true
    ];
}
