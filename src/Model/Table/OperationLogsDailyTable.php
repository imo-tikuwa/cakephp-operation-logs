<?php
namespace OperationLogs\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OperationLogsDaily Model
 *
 * @method \OperationLogs\Model\Entity\OperationLogsDaily get($primaryKey, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily newEntity($data = null, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily[] newEntities(array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily[] patchEntities($entities, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsDaily findOrCreate($search, callable $callback = null, $options = [])
 */
class OperationLogsDailyTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('operation_logs_daily');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->date('target_ymd')
            ->requirePresence('target_ymd', 'create')
            ->notEmpty('target_ymd');

        $validator
            ->scalar('summary_type')
            ->maxLength('summary_type', 20)
            ->requirePresence('summary_type', 'create')
            ->notEmpty('summary_type');

        $validator
            ->scalar('groupedby')
            ->maxLength('groupedby', 255)
            ->allowEmpty('groupedby');

        $validator
            ->integer('counter')
            ->requirePresence('counter', 'create')
            ->notEmpty('counter');

        return $validator;
    }
}
