DROP TABLE IF EXISTS `operation_logs`;
CREATE TABLE `operation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `client_ip` text NOT NULL COMMENT 'クライアントIP',
  `user_agent` text COMMENT 'ユーザーエージェント',
  `request_url` varchar(255) NOT NULL COMMENT 'リクエストURL',
  `request_time` datetime(3) NOT NULL COMMENT 'リクエスト日時',
  `response_time` datetime(3) NOT NULL COMMENT 'レスポンス日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログ';
