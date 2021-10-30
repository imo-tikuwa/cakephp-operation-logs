<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OperationLogs\Console;

if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin', 'r'));
}

use Cake\Core\Configure;
use Cake\Utility\Security;
use Composer\Script\Event;
use Exception;

/**
 * Provides installation hooks for when this application is installed through
 * composer. Customize this class to suit your needs.
 */
class Installer
{
    /**
     * Does some routine installation tasks so people don't have to.
     *
     * @param \Composer\Script\Event $event The composer event object.
     * @throws \Exception Exception raised by validator.
     * @return void
     */
    public static function postInstall(Event $event)
    {
        static::copySchemaFiles();
    }

    /**
     * Copy the schema file of the table used by OperationLogs to the config/schema directory of the APP.
     *
     * @return void
     */
    public static function copySchemaFiles()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $plugin_root_dir = dirname(__DIR__, 2);
        $schema_dir = $plugin_root_dir . DS . 'config' . DS . 'schema' . DS;

        // 相対参照するがplugins以下にインストールした場合と、composerでインストールした場合でさかのぼるディレクトリの数が異なる
        // プラグインのルートの一つ上のディレクトリ名がpluginsかどうかで相対レベルを分岐
        $plugin_parent_dirname = basename(dirname($plugin_root_dir));
        $relative_level = $plugin_parent_dirname === 'plugins' ? 2 : 4;
        $app_schema_dir = dirname($plugin_root_dir, $relative_level) . DS . 'config' . DS . 'schema' . DS;

        foreach (glob($schema_dir . '*.sql') as $schema_file_path) {
            $schema_file_name = basename($schema_file_path);
            copy($schema_file_path, $app_schema_dir . $schema_file_name);
        }
    }
}
