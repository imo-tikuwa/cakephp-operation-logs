<?php
namespace OperationLogs\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Database\Schema\TableSchema;

/**
 * OperationLogs Model
 *
 * @method \OperationLogs\Model\Entity\OperationLog get($primaryKey, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog newEntity($data = null, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog[] newEntities(array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog[] patchEntities($entities, array $data, array $options = [])
 * @method \OperationLogs\Model\Entity\OperationLog findOrCreate($search, callable $callback = null, $options = [])
 */
class OperationLogsTable extends Table
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

        $this->setTable('operation_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * TableSchemaの初期化処理
     * {@inheritDoc}
     * @see \Cake\ORM\Table::_initializeSchema()
     */
    protected function _initializeSchema(TableSchema $schema) {

        // リクエスト日時とレスポンス日時のカラムタイプ設定をデフォルトのdatetimeから独自定義したdatetimemicroへ上書き
        $schema->setColumnType("request_time", 'datetimemicro');
        $schema->setColumnType("response_time", 'datetimemicro');
        return $schema;
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
            ->scalar('client_ip')
            ->requirePresence('client_ip', 'create')
            ->notEmpty('client_ip');

        $validator
            ->scalar('user_agent')
            ->requirePresence('user_agent', 'create')
            ->notEmpty('user_agent');

        $validator
            ->scalar('request_url')
            ->maxLength('request_url', 255)
            ->requirePresence('request_url', 'create')
            ->notEmpty('request_url');

        $validator
            ->dateTime('request_time')
            ->requirePresence('request_time', 'create')
            ->notEmpty('request_time');

        $validator
            ->dateTime('response_time')
            ->requirePresence('response_time', 'create')
            ->notEmpty('response_time');

        return $validator;
    }
}
