<?php
namespace OperationLogs\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Exception\InternalErrorException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * OperationLogs middleware
 *
 * @property \OperationLogs\Model\Table\OperationLogsTable $OperationLogs
 *
 * @see https://book.cakephp.org/3/ja/controllers/middleware.html
 */
class OperationLogsMiddleware extends OperationLogsSimpleMiddleware implements MiddlewareInterface
{
    use InstanceConfigTrait;

    /**
     * default configs.
     * @var array
     */
    protected $_defaultConfig = [
        // データベースコネクション
        'connection' => 'default',
        // モード設定
        // exclude or include
        // デフォルトはexclude
        // excludeのときexclude_〇〇のチェックを実施、includeのときinclude_〇〇のチェックを実施
        // excludeのときinclude_〇〇のオプションは無視、includeのときexclude_〇〇のオプションは無視
        'mode' => 'exclude',
        // 除外URL設定
        // 以下に含まれるURLと前方一致のリクエストは記録しない
        'exclude_urls' => [
            '/debug-kit',
        ],
        // 除外IP設定
        // 以下に含まれるIPと前方一致のリクエストは記録しない
        'exclude_ips' => [
        ],
        // 除外ユーザーエージェント設定
        // 以下に含まれるユーザーエージェントと部分一致のリクエストは記録しない
        'exclude_user_agents' => [
        ],
        // 包含URL設定
        // 以下に含まれるURLと前方一致のリクエストのみ記録する
        'include_urls' => [
        ],
        // 包含IP設定
        // 以下に含まれるIPと前方一致のリクエストのみ記録する
        'include_ips' => [
        ],
        // 包含ユーザーエージェント設定
        // 以下に含まれるユーザーエージェントと部分一致のリクエストのみ記録する
        'include_user_agents' => [
        ],
    ];

    /**
     * constructer
     * @param array $config 設定配列
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (isset($config['mode'])) {
            if (!in_array($config['mode'], ['exclude', 'include'], true)) {
                throw new InternalErrorException(__('OperationLogsMiddleware mode option is invalid.'));
            }
            $this->setConfig('mode', $config['mode'], false);
        }
        if (isset($config['exclude_urls'])) {
            $this->setConfig('exclude_urls', $config['exclude_urls'], false);
        }
        if (isset($config['exclude_ips'])) {
            $this->setConfig('exclude_ips', $config['exclude_ips'], false);
        }
        if (isset($config['exclude_user_agents'])) {
            $this->setConfig('exclude_user_agents', $config['exclude_user_agents'], false);
        }
        if (isset($config['include_urls'])) {
            $this->setConfig('include_urls', $config['include_urls'], false);
        }
        if (isset($config['include_ips'])) {
            $this->setConfig('include_ips', $config['include_ips'], false);
        }
        if (isset($config['include_user_agents'])) {
            $this->setConfig('include_user_agents', $config['include_user_agents'], false);
        }
    }

    /**
     * Record request information in the OperationLogs table as needed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // リクエスト前処理
        // ------------------------------
        // リクエストURL
        $request_url = $this->getRequestUrl($request);
        if ($this->_checkUrl($request_url)) {
            return $handler->handle($request);
        }

        // ユーザーエージェント
        $user_agent = $this->getUserAgent($request);
        if ($this->_checkUserAgent($user_agent)) {
            return $handler->handle($request);
        }

        // リクエスト処理
        // ------------------------------
        $request_time = $this->getCurrentDateTime();
        $response = $handler->handle($request);
        $response_time = $this->getCurrentDateTime();

        // クライアントIP
        $client_ip = $this->getClientIp();
        if ($this->_checkIp($client_ip)) {
            return $response;
        }

        // リクエスト後処理
        // ------------------------------
        // ログを保存
        $this->saveLog($client_ip, $user_agent, $request_url, $request_time, $response_time);

        return $response;
    }

    /**
     * URLチェック
     * @param string $request_url リクエストURL
     * @return boolean
     */
    private function _checkUrl($request_url = null)
    {
        $mode = $this->getConfig('mode');
        if ($mode === 'include') {
            return !$this->__checkIncludeUrls($request_url);
        } else {
            return $this->__checkExcludeUrls($request_url);
        }
    }

    /**
     * IPチェック
     * @param string $client_ip クライアントIP
     * @return boolean
     */
    private function _checkIP($client_ip = null)
    {
        $mode = $this->getConfig('mode');
        if ($mode === 'include') {
            return !$this->__checkIncludeIps($client_ip);
        } else {
            return $this->__checkExcludeIps($client_ip);
        }
    }

    /**
     * ユーザーエージェントチェック
     * @param string $user_agent ユーザーエージェント
     * @return boolean
     */
    private function _checkUserAgent($user_agent = null)
    {
        $mode = $this->getConfig('mode');
        if ($mode === 'include') {
            return !$this->__checkIncludeUserAgents($user_agent);
        } else {
            return $this->__checkExcludeUserAgents($user_agent);
        }
    }

    /**
     * 除外設定のURLと前方一致するかチェック
     * @param string $request_url リクエストURL
     * @return boolean
     */
    private function __checkExcludeUrls($request_url = null)
    {
        $exclude_urls = $this->getConfig('exclude_urls');
        if (is_null($request_url) || empty($exclude_urls)) {
            return false;
        }
        foreach ($exclude_urls as $exclude_url) {
            if (str_starts_with($request_url, $exclude_url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 除外設定のIPと前方一致するかチェック
     * @param string $client_ip クライアントIP
     * @return boolean
     */
    private function __checkExcludeIps($client_ip = null)
    {
        $exclude_ips = $this->getConfig('exclude_ips');
        if (is_null($client_ip) || empty($exclude_ips)) {
            return false;
        }
        foreach ($exclude_ips as $exclude_ip) {
            if (str_starts_with($client_ip, $exclude_ip)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 除外設定のユーザーエージェントと部分一致するかチェック
     * @param string $user_agent ユーザーエージェント
     * @return boolean
     */
    private function __checkExcludeUserAgents($user_agent = null)
    {
        $exclude_user_agents = $this->getConfig('exclude_user_agents');
        if (is_null($user_agent) || empty($exclude_user_agents)) {
            return false;
        }
        foreach ($exclude_user_agents as $exclude_user_agent) {
            if (str_contains($user_agent, $exclude_user_agent)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 包含設定のURLと前方一致するかチェック
     * @param string $request_url リクエストURL
     * @return boolean
     */
    private function __checkIncludeUrls($request_url = null)
    {
        $include_urls = $this->getConfig('include_urls');
        if (is_null($request_url) || empty($include_urls)) {
            return false;
        }
        foreach ($include_urls as $include_url) {
            if (str_starts_with($request_url, $include_url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 包含設定のIPと前方一致するかチェック
     * @param string $client_ip クライアントIP
     * @return boolean
     */
    private function __checkIncludeIps($client_ip = null)
    {
        $include_ips = $this->getConfig('include_ips');
        if (is_null($client_ip) || empty($include_ips)) {
            return false;
        }
        foreach ($include_ips as $include_ip) {
            if (str_starts_with($client_ip, $include_ip)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 包含設定のユーザーエージェントと部分一致するかチェック
     * @param string $user_agent ユーザーエージェント
     * @return boolean
     */
    private function __checkIncludeUserAgents($user_agent = null)
    {
        $include_user_agents = $this->getConfig('include_user_agents');
        if (is_null($user_agent) || empty($include_user_agents)) {
            return false;
        }
        foreach ($include_user_agents as $include_user_agent) {
            if (str_contains($user_agent, $include_user_agent)) {
                return true;
            }
        }
        return false;
    }
}
