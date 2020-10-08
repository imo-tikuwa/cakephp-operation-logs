<?php
namespace OperationLogs\Command;

use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Core\Configure;

/**
 * 1時間毎集計コマンド
 * @author tikuwa
 *
 * @property \OperationLogs\Model\Table\OperationLogsTable $OperationLogs
 * @property \OperationLogs\Model\Table\OperationLogsHourlyTable $OperationLogsHourly
 */
class HourlySummaryCommand extends Command
{
	private $start_msg = "############ hourly summary command start. #############";
	private $end_msg   = "############ hourly summary command end.   #############";

	public function __construct() {
		$this->OperationLogs = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogs');
		$this->OperationLogsHourly = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogsHourly');
	}

	/**
	 * 集計処理
	 * {@inheritDoc}
	 * @see \Cake\Console\Command::execute()
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$io->out($this->start_msg);

		// 集計対象日
		if ($args->hasOption('target_ymd')) {
			try {
				$target_ymd = new \DateTime($args->getOption('target_ymd'));
				$target_ymd = $target_ymd->format('Y-m-d');
			} catch (\Exception $e) {
				$io->error('target_ymd is invalid.');
				$this->abort();
			}
		} else {
			// 対象日が未指定のときは現在日-1
			$target_ymd = new \DateTime();
			$target_ymd = $target_ymd->modify('-1 days')->format('Y-m-d');
		}
		$io->out("target_ymd = {$target_ymd}");

		// いったん対象日のデータを全削除
		$this->OperationLogsHourly->deleteAll([
				'target_time >=' => $target_ymd . " 00:00:00",
				'target_time <=' => $target_ymd . " 23:59:59"
		]);

		$operation_logs_hourly_entities = [];
		for ($hour = 0; $hour <= 23; $hour++) {

			// 1時間毎で集計対象データを取得していく
			$target_h = sprintf('%02d', $hour);
			$operation_logs = $this->OperationLogs->find()->select(['id', 'client_ip', 'user_agent', 'request_url', 'request_time'])->where([
					'request_time >=' => "{$target_ymd} {$target_h}:00:00",
					'request_time <=' => "{$target_ymd} {$target_h}:59:59"
			])
			->enableHydration(false)
			->toArray();
			if ($operation_logs == null || count($operation_logs) <= 0) {
				$io->out("{$hour} o'clock operation_logs not found.");
				continue;
			}
			$operation_logs_count = count($operation_logs);
			$io->out("operation_logs {$operation_logs_count} records found.");

			// 集計(全体)
			$operation_logs_hourly_entities[] = $this->OperationLogsHourly->newEntity([
					'target_time' => "{$target_ymd} {$target_h}:00:00",
					'summary_type' => OL_SUMMARY_TYPE_ALL,
					'groupedby' => null,
					'counter' => $operation_logs_count
			]);

			// IPアドレス/ユーザーエージェント/リクエストURLごとの集計データを作成
			foreach (Configure::read('OperationLogs.summary_types') as $summary_type => $summary_column) {
				if ($summary_type == OL_SUMMARY_TYPE_ALL) {
					continue;
				}
				$grouped_logs = Hash::combine($operation_logs, '{n}.id', '{n}', "{n}.{$summary_column}");
				foreach ($grouped_logs as $groupedby => $grouped_data) {
					// ユーザーエージェントなんかは空のパターンがある。
					// 空の時はHash関数のグルーピングによって0となるので空文字に置き換える
					if ($groupedby === 0) {
						$groupedby = '';
					}
					$operation_logs_hourly_entities[] = $this->OperationLogsHourly->newEntity([
							'target_time' => "{$target_ymd} {$target_h}:00:00",
							'summary_type' => $summary_type,
							'groupedby' => $groupedby,
							'counter' => count($grouped_data)
					]);
				}
				$io->out("operation_logs groupd by {$summary_column} to makes " . count($grouped_logs) . " records.");
			}
		}

		// 保存
		$this->OperationLogsHourly->saveMany($operation_logs_hourly_entities);
		$io->out("operation_logs_hourly " . count($operation_logs_hourly_entities) . " records registered.");
		$io->out($this->end_msg);
	}

	/**
	 * オプションパーサー
	 * {@inheritDoc}
	 * @see \Cake\Console\Command::buildOptionParser()
	 */
	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser
		->addOption('target_ymd', [
				'help' => 'input summary target date.',
		]);

		return $parser;
	}
}