<?php
namespace OperationLogs\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OperationLogsMonthly Model
 *
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly get($primaryKey, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly newEntity($data = null, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly[] newEntities(array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly[] patchEntities($entities, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLogsMonthly findOrCreate($search, callable $callback = null, $options = [])
 */
class OperationLogsMonthlyTable extends Table
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

        $this->setTable('operation_logs_monthly');
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
            ->integer('target_ym')
            ->requirePresence('target_ym', 'create')
            ->notEmpty('target_ym');

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
