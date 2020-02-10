# OperationLogs plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer config repositories.imo-tikuwa/cakephp-operation-logs vcs https://github.com/imo-tikuwa/cakephp-operation-logs
composer require imo-tikuwa/cakephp-operation-logs:dev-master
```

## How to Use
Append middleware to Application.php

```
⁺use OperationLogs\Middleware\OperationLogsMiddleware;

    public function middleware($middlewareQueue)
    {
        $middlewareQueue
+            // Add operation_logs middleware.
+            ->add(OperationLogsMiddleware::class)
            ;
        return $middlewareQueue;
    }
```
