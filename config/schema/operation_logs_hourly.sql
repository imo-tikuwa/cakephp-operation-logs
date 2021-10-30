DROP TABLE IF EXISTS `operation_logs_hourly`;
CREATE TABLE `operation_logs_hourly` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_time` datetime NOT NULL COMMENT '対象日時',
  `summary_type` varchar(20) NOT NULL COMMENT '集計タイプ',
  `groupedby` varchar(255) DEFAULT NULL COMMENT 'グループ元',
  `counter` int(11) NOT NULL COMMENT 'カウンタ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログの集計(1時間毎)';
