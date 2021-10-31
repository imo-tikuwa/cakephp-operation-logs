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
        $io = $event->getIO();
        static::copySchemaFiles($io);
    }

    /**
     * Copy the schema file of the table used by OperationLogs to the config/schema directory of the APP.
     *
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function copySchemaFiles($io)
    {
        // コピー処理を走らせる前に事前確認
        $validator = function ($arg) {
            if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                return $arg;
            }
            throw new Exception('This is not a valid answer. Please choose Y or n.');
        };
        $setFolderPermissions = $io->askAndValidate(
            '<info>Do you want to copy the schema file of the table used by the OperationLogs plugin to the application dir ? (Default to Y)</info> [<comment>Y,n</comment>]? ',
            $validator,
            10,
            'Y'
        );
        if (in_array($setFolderPermissions, ['n', 'N'])) {
            return;
        }

        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $plugin_root_dir = dirname(__DIR__, 2);
        $schema_dir = $plugin_root_dir . DS . 'config' . DS . 'schema' . DS;

        // plugins以下で開発した場合と、composerでインストールした場合でさかのぼるディレクトリの数が異なる
        // プラグインのルートの一つ上のディレクトリ名がpluginsかどうかで相対レベルを分岐
        $plugin_parent_dirname = basename(dirname($plugin_root_dir));
        $relative_level = $plugin_parent_dirname === 'plugins' ? 2 : 3;
        $app_schema_dir = dirname($plugin_root_dir, $relative_level) . DS . 'config' . DS . 'schema' . DS;

        // ディレクトリが存在しない場合は確認のうえ作成
        if (!file_exists($app_schema_dir)) {
            $io->write([
                'The path was not found.',
                "path = {$app_schema_dir}"
            ]);
            $validator = function ($arg) {
                if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                    return $arg;
                }
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
            $setFolderPermissions = $io->askAndValidate(
                '<info>Do you want to create a schema directory ? (Default to Y)</info> [<comment>Y,n</comment>]? ',
                $validator,
                10,
                'Y'
            );
            if (in_array($setFolderPermissions, ['n', 'N'])) {
                return;
            }
            mkdir($app_schema_dir);
        }

        foreach (glob($schema_dir . '*.sql') as $schema_file_path) {
            $schema_file_name = basename($schema_file_path);
            copy($schema_file_path, $app_schema_dir . $schema_file_name);
        }
    }
}
