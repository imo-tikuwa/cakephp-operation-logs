<?php
namespace OperationLogs\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OperationLogsHourly Model
 *
 * @method \OperationLogs\Model\Entity\OperationLogsHourly get($primaryKey, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly newEntity($data = null, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly[] newEntities(array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly[] patchEntities($entities, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsHourly findOrCreate($search, callable $callback = null, $options = [])
 */
class OperationLogsHourlyTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('operation_logs_hourly');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->dateTime('target_time')
            ->requirePresence('target_time', 'create')
            ->notEmpty('target_time');

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
