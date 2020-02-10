# OperationLogs plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require imo-tikuwa/cakephp-operation-logs
```

## How to Use
append middleware.

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