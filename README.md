# OperationLogs plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require imo-tikuwa/cakephp-operation-logs
```

## How to Use
Load plugin to bootstrap.php
```
Plugin::load('OperationLogs', ['bootstrap' => true]);
```

Execute the database table initialization command.   
※Executing the command will delete & create operation_logs, operation_logs_hourly, operation_logs_daily, operation_logs_monthly tables.  
※If you want to record up to microseconds, specify the `--enable_micro` option.
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
                'exclude_ips' => [
                    '192.168',
                    '::'
                ],
                'exclude_user_agents' => [
                    'Firefox/73'
                ]
            ]))
            ;
        return $middlewareQueue;
    }
```

## Options.
| option name | option type | default | example | memo |
| - | - | - | - | - |
| exclude_urls | string array | \[ '/debug-kit' \] | \[ '/debug-kit', '/admin' \] | Exclude with prefix match |
| exclude_ips | string array | \[\] | \[ '192.168', '::' \] | Exclude with prefix match |
| exclude_user_agents | string array | \[\] | \[ 'Safari', 'Edge' \] | Exclude with broad match |

## Data summary commands.
daily_summaryコマンド、monthly_summaryコマンド、hourly_summaryコマンドがあります。  
operation_logsテーブルのデータを元にクライアントIP、ユーザーエージェント、リクエストURLなどでグルーピングしたデータを集計します。  

### daily_summary command.
--target_ymdオプションで集計日を設定可能。  
未指定のときは前日のデータを集計します。  
データはoperation_logs_dailyテーブルに記録されます。  
```
cake daily_summary --target_ymd=2020-02-13
```

### monthly_summary command.
--target_ymオプションで集計年月を6桁の数字で設定可能。  
未指定の時は先月のデータを集計します。  
データはoperation_logs_monthlyテーブルに記録されます。  
```
cake monthly_summary --target_ym=202002
```

### hourly_summary command.
--target_ymdオプションで集計日を設定可能。  
未指定の時は前日のデータを集計します。  
1時間単位でデータを集計します。  
データはoperation_logs_hourlyテーブルに記録されます。
```
cake hourly_summary --target_ymd=2020-02-13
```
