# OperationLogs plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer config repositories.imo-tikuwa/cakephp-operation-logs vcs https://github.com/imo-tikuwa/cakephp-operation-logs
composer require imo-tikuwa/cakephp-operation-logs:dev-master
```

## How to Use
Load plugin to bootstrap.php
```
Plugin::load('OperationLogs', ['bootstrap' => true]);
```

Create MySQL for operation_logs.
```
cake init_operation_logs
```

Append middleware to Application.php
```
use OperationLogs\Middleware\OperationLogsMiddleware;

    public function middleware($middlewareQueue)
    {
        $middlewareQueue
            // Add operation_logs middleware.
            ->add(new OperationLogsMiddleware([
                'exclude_urls' => [
                    '/debug-kit',
                    '/cake3-admin-baker',
                    '/api'
                ]
            ]))
            ;
        return $middlewareQueue;
    }
```

## Data summary commands.
daily_summaryコマンド、monthly_summaryコマンド、hourly_summaryコマンドがあります。  
operation_logsテーブルのデータを元にクライアントIP、ユーザーエージェント、リクエストURLなどでグルーピングしたデータを集計します。  

daily_summaryコマンドは--target_ymdオプションで集計日を設定可能。  
未指定のときは前日のデータを集計します。  
データはoperation_logs_dailyテーブルに記録されます。  

monthly_summaryコマンドは--target_ymオプションで集計年月を6桁の数字で設定可能。  
未指定の時は先月のデータを集計します。  
データはoperation_logs_monthlyテーブルに記録されます。  

hourly_summaryコマンドは--target_ymdオプションで集計日を設定可能。  
未指定の時は前日のデータを集計します。  
1時間単位でデータを集計します。  
データはoperation_logs_hourlyテーブルに記録されます。
```
cake daily_summary --target_ymd=2020-02-13
cake monthly_summary --target_ym=202002
cake hourly_summary --target_ymd=2020-02-13
```
