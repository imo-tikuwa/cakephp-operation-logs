<?php
namespace OperationLogs\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\I18n\Time;
use Cake\Core\InstanceConfigTrait;
use OperationLogs\Util\OperationLogsUtils;
use Cake\Datasource\ConnectionManager;

/**
 * OperationLogs middleware
 *
 * @property \OperationLogs\Model\Table\OperationLogsTable $OperationLogs
 *
 * @see https://book.cakephp.org/3/ja/controllers/middleware.html
 */
class OperationLogsMiddleware
{
	use InstanceConfigTrait;

	/**
	 * default configs.
	 * @var array
	 */
	protected $_defaultConfig = [
			'exclude_urls' => [
					'/debug-kit',
			],
			'exclude_ips' => [
			],
			'exclude_user_agents' => [
			]
	];

	/**
	 * constructer
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		if (isset($config['exclude_urls'])) {
			$this->setConfig('exclude_urls', $config['exclude_urls'], false);
		}
		if (isset($config['exclude_ips'])) {
			$this->setConfig('exclude_ips', $config['exclude_ips'], false);
		}
		if (isset($config['exclude_user_agents'])) {
			$this->setConfig('exclude_user_agents', $config['exclude_user_agents'], false);
		}
	}

	/**
	 * Invoke method.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request The request.
	 * @param \Psr\Http\Message\ResponseInterface $response The response.
	 * @param callable $next Callback to invoke the next middleware.
	 * @return \Psr\Http\Message\ResponseInterface A response
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		// リクエスト前処理
		// ------------------------------
		// 除外設定(リクエストURL)
		$request_url = $request->getUri()->getPath();
		if ($this->_checkExcludeUrls($request_url)) {
			return $next($request, $response);
		}

		// 除外設定(ユーザーエージェント)
		$user_agent = @$request->getHeader('User-Agent')[0];
		if ($this->_checkExcludeUserAgents($user_agent)) {
			return $next($request, $response);
		}

		$request_time = $this->_getCurrentDateTime();

		// リクエスト処理
		// ------------------------------
		$response = $next($request, $response);

		// リクエスト後処理
		// ------------------------------
		// 除外設定(クライアントIP)
		$client_ip = Router::getRequest()->clientIp();
		if ($this->_checkExcludeIps($client_ip)) {
			return $response;
		}

		$response_time = $this->_getCurrentDateTime();

		$this->OperationLogs = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogs');
		$entity = $this->OperationLogs->newEntity([
				'client_ip' => $client_ip,
				'user_agent' => $user_agent,
				'request_url' => $request_url,
				'request_time' => $request_time,
				'response_time' => $response_time,
		], ['validate' => false]);
		$this->OperationLogs->save($entity, ['validate' => false]);

		return $response;
	}

	/**
	 * 除外設定のURLと前方一致するかチェック
	 * @param string $request_url
	 * @return boolean
	 */
	private function _checkExcludeUrls($request_url = null) {

		$exclude_urls = $this->getConfig('exclude_urls');
		if (is_null($request_url) || empty($exclude_urls)) {
			return false;
		}
		foreach ($exclude_urls as $exclude_url) {
			if (OperationLogsUtils::startsWith($request_url, $exclude_url)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 除外設定のURLと前方一致するかチェック
	 * @param string $client_ip
	 * @return boolean
	 */
	private function _checkExcludeIps($client_ip = null) {

		$exclude_ips = $this->getConfig('exclude_ips');
		if (is_null($client_ip) || empty($exclude_ips)) {
			return false;
		}
		foreach ($exclude_ips as $exclude_ip) {
			if (OperationLogsUtils::startsWith($client_ip, $exclude_ip)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 除外設定のユーザーエージェントと部分一致するかチェック
	 * @param string $user_agent
	 * @return boolean
	 */
	private function _checkExcludeUserAgents($user_agent = null) {

		$exclude_user_agents = $this->getConfig('exclude_user_agents');
		if (is_null($user_agent) || empty($exclude_user_agents)) {
			return false;
		}
		foreach ($exclude_user_agents as $exclude_user_agent) {
			if (strpos($user_agent, $exclude_user_agent) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * マイクロ秒(小数点6桁)付きのDateTimeオブジェクトを返す
	 * @return \DateTime
	 */
	private function _getCurrentDateTime() {
		list($microtime, $unixtime) = explode(" ", microtime(false));
		$milliseconds = substr($microtime, 1, 7);
		$datetimes = date('Y-m-d H:i:s', $unixtime);
		return (new \DateTime("{$datetimes}{$milliseconds}"))->format('Y-m-d H:i:s.u');
	}
}
