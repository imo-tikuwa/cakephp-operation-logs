DROP TABLE IF EXISTS `operation_logs_daily`;
CREATE TABLE `operation_logs_daily` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_ymd` date NOT NULL COMMENT '対象日',
  `summary_type` varchar(20) NOT NULL COMMENT '集計タイプ',
  `groupedby` varchar(255) DEFAULT NULL COMMENT 'グループ元',
  `counter` int(11) NOT NULL COMMENT 'カウンタ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログの集計(日毎)';
