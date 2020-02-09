# OperationLogs plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require imo-tikuwa/cakephp-operation-logs
```

## How to Use
create table.

```
CREATE TABLE `operation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `client_ip` text NOT NULL COMMENT 'クライアントIP',
  `user_agent` text NOT NULL COMMENT 'ユーザーエージェント',
  `request_url` varchar(255) NOT NULL COMMENT 'リクエストURL',
  `request_time` datetime NOT NULL COMMENT 'リクエスト日時',
  `response_time` datetime NOT NULL COMMENT 'レスポンス日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='操作ログ';
```

and append middleware.

```
use OperationLogs\Middleware\OperationLogsMiddleware;

    public function middleware($middlewareQueue)
    {
        $middlewareQueue
+            // Add operation_logs middleware.
+            ->add(OperationLogsMiddleware::class)
            ;
        return $middlewareQueue;
    }
```