<?php
namespace OperationLogs\Command;

use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * 初期化コマンド
 *
 * 実行するとDROP TABLE → CREATE TABLEの順番でアクセス記録テーブルと集計テーブルを生成します。
 *
 * @author tikuwa
 *
 */
class InitOperationLogsCommand extends Command
{
	private $start_msg = "############ init operation_logs command start. #############";
	private $end_msg   = "############ init operation_logs command end.   #############";

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        // マイクロ秒まで記録するオプション(有効なときdatetime(3)ではなくdatetime(6)でテーブルを作成する)
        $parser->addOption('enable_micro', [
            'help' => 'record up to microseconds for the request_time and response_time columns.',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
    	$io->out($this->start_msg);

    	$connection = ConnectionManager::get('default');

    	// いったん全テーブル削除
    	$connection->execute("DROP TABLE IF EXISTS `operation_logs`;");
    	$connection->execute("DROP TABLE IF EXISTS `operation_logs_hourly`;");
    	$connection->execute("DROP TABLE IF EXISTS `operation_logs_daily`;");
    	$connection->execute("DROP TABLE IF EXISTS `operation_logs_monthly`;");

    	// アクセス記録テーブル作成
    	$datetime_column_definition = $args->getOption('enable_micro') ? "datetime(6)" : "datetime(3)";
    	$query = <<<EOL
CREATE TABLE `operation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `client_ip` text NOT NULL COMMENT 'クライアントIP',
  `user_agent` text DEFAULT NULL COMMENT 'ユーザーエージェント',
  `request_url` varchar(255) NOT NULL COMMENT 'リクエストURL',
  `request_time` {$datetime_column_definition} NOT NULL COMMENT 'リクエスト日時',
  `response_time` {$datetime_column_definition} NOT NULL COMMENT 'レスポンス日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログ';
EOL;
    	$connection->execute($query);
    	$io->out("`operation_logs` table created.");

    	// アクセス集計テーブル(1時間毎)作成
    	$query = <<<EOL
CREATE TABLE `operation_logs_hourly` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_time` datetime NOT NULL COMMENT '対象日時',
  `summary_type` varchar(20) NOT NULL COMMENT '集計タイプ',
  `groupedby` varchar(255) DEFAULT NULL COMMENT 'グループ元',
  `counter` int(11) NOT NULL COMMENT 'カウンタ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログの集計(1時間毎)';
EOL;
    	$connection->execute($query);
    	$io->out("`operation_logs_hourly` table created.");

    	// アクセス集計テーブル(日毎)作成
    	$query = <<<EOL
CREATE TABLE `operation_logs_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_ymd` date NOT NULL COMMENT '対象日',
  `summary_type` varchar(20) NOT NULL COMMENT '集計タイプ',
  `groupedby` varchar(255) DEFAULT NULL COMMENT 'グループ元',
  `counter` int(11) NOT NULL COMMENT 'カウンタ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログの集計(日毎)';
EOL;
    	$connection->execute($query);
    	$io->out("`operation_logs_daily` table created.");

    	// アクセス集計テーブル(月毎)作成
    	$query = <<<EOL
CREATE TABLE `operation_logs_monthly` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_ym` int(6) NOT NULL COMMENT '対象年月',
  `summary_type` varchar(20) NOT NULL COMMENT '集計タイプ',
  `groupedby` varchar(255) DEFAULT NULL COMMENT 'グループ元',
  `counter` int(11) NOT NULL COMMENT 'カウンタ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログの集計(月毎)';
EOL;
    	$connection->execute($query);
    	$io->out("`operation_logs_monthly` table created.");

    	$io->out($this->end_msg);
    }
}
