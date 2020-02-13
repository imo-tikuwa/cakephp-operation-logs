<?php
namespace OperationLogs\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class MonthlySummaryCommand extends Command
{
	private $start_msg = "############ monthly summary command start. ############";
	private $end_msg   = "############ monthly summary command end.   ############";

	public function __construct() {
		$this->OperationLogsDaily = TableRegistry::getTableLocator()->get('OperationLogsDaily');
		$this->OperationLogsMonthly = TableRegistry::getTableLocator()->get('OperationLogsMonthly');
	}

	/**
	 * 集計処理
	 * {@inheritDoc}
	 * @see \Cake\Console\Command::execute()
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$io->out($this->start_msg);

		// 集計対象年月
		if ($args->hasOption('target_ym')) {
			try {
				$input_target_ym = $args->getOption('target_ym');
				if (strlen($input_target_ym) != 6) {
					throw new \Exception();
				}
				$target_ym_date = new \DateTime("{$input_target_ym}01");
			} catch (\Exception $e) {
				$io->error('target_ym is invalid.');
				$this->abort();
			}
		} else {
			// 対象年月が未指定のときは先月
			$target_ym_date = new \DateTime();
			$target_ym_date = $target_ym_date->modify('first day of last month');
		}
		$target_ym = $target_ym_date->format('Ym');
		$io->out("target_ym = {$target_ym}");

		// 集計対象データを取得
		$daily_operation_logs = $this->OperationLogsDaily->find()->select(['id', 'summary_type', 'groupedby', 'counter'])->where([
				'summary_type <>' => 'all',
				'target_ymd >=' => $target_ym_date->format('Y-m-d'),
				'target_ymd <=' => $target_ym_date->modify('last day of this month')->format('Y-m-d')
		])
		->enableHydration(false)
		->toArray();
		if ($daily_operation_logs == null || count($daily_operation_logs) <= 0) {
			$io->out("operation_logs_daily not found.\n{$this->end_msg}");
			$this->abort();
		}
		$daily_operation_logs_count = count($daily_operation_logs);
		$io->out("daily_operation_logs {$daily_operation_logs_count} records found.");

		// いったん対象年月のデータを全削除
		$this->OperationLogsMonthly->deleteAll(['target_ym' => $target_ym]);

		// 集計データを作成
		$operation_logs_monthly_entities = [];

		// IPアドレス/ユーザーエージェント/リクエストURLごとの集計データを作成
		$grouped_logs = Hash::combine($daily_operation_logs, '{n}.id', '{n}', "{n}.summary_type");

		// 集計(全体)はとりあえずIPアドレスの集計データを元に作成
		$total_count = 0;
		foreach ($grouped_logs['ip'] as $id => $data) {
			$total_count += $data['counter'];
		}
		$operation_logs_monthly_entities[] = $this->OperationLogsMonthly->newEntity([
				'target_ym' => $target_ym,
				'summary_type' => 'all',
				'groupedby' => null,
				'counter' => $total_count
		]);

		foreach ($grouped_logs as $summary_type => $summary_data) {
			$grouped_data = Hash::combine($summary_data, '{n}.id', '{n}', '{n}.groupedby');
			foreach ($grouped_data as $groupedby => $each_group_data) {
				$counter = 0;
				foreach($each_group_data as $each_data) {
					$counter += $each_data['counter'];
				}
				$operation_logs_monthly_entities[] = $this->OperationLogsMonthly->newEntity([
					'target_ym' => $target_ym,
					'summary_type' => $summary_type,
					'groupedby' => $groupedby,
					'counter' => $counter
				]);
			}
			$io->out("daily_operation_logs groupd by {$summary_type} to makes " . count($each_group_data) . " records.");
		}

		// 保存
		$this->OperationLogsMonthly->saveMany($operation_logs_monthly_entities);
		$io->out("operation_logs_monthly " . count($operation_logs_monthly_entities) . " records registered.");
		$io->out($this->end_msg);
	}

	/**
	 * オプションパーサー
	 * {@inheritDoc}
	 * @see \Cake\Console\Command::buildOptionParser()
	 */
	protected function buildOptionParser(ConsoleOptionParser $parser)
	{
		$parser
		->addOption('target_ym', [
				'help' => 'input summary target year and month with 6 digits. example 202001',
		]);

		return $parser;
	}
}