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
		$exclude_urls = $this->getConfig('exclude_urls');
		if (!empty($exclude_urls)) {
			foreach ($exclude_urls as $exclude_url) {
				if (OperationLogsUtils::startsWith($request_url, $exclude_url)) {
					return $next($request, $response);
				}
			}
		}

		// 除外設定(ユーザーエージェント)
		$user_agent = @$request->getHeader('User-Agent')[0];
		$exclude_user_agents = $this->getConfig('exclude_user_agents');
		if (!is_null($user_agent) && !empty($exclude_user_agents)) {
			foreach ($exclude_user_agents as $exclude_user_agent) {
				if (strpos($user_agent, $exclude_user_agent) !== false) {
					return $next($request, $response);
				}
			}
		}

		$request_time = $this->_getCurrentDateTime();

		// リクエスト処理
		// ------------------------------
		$response = $next($request, $response);

		// リクエスト後処理
		// ------------------------------
		// 除外設定(クライアントIP)
		$client_ip = Router::getRequest()->clientIp();
		$exclude_ips = $this->getConfig('exclude_ips');
		if (!empty($exclude_ips)) {
			foreach ($exclude_ips as $exclude_ip) {
				if (OperationLogsUtils::startsWith($client_ip, $exclude_ip)) {
					return $response;
				}
			}
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
	 * ミリ秒(小数点3桁)付きのDateTimeオブジェクトを返す
	 * @return \DateTime
	 */
	private function _getCurrentDateTime() {
		list($microtime, $unixtime) = explode(" ", microtime(false));
		$milliseconds = substr($microtime, 1, 4);
		$datetimes = date('Y-m-d H:i:s', $unixtime);
		return (new \DateTime("{$datetimes}{$milliseconds}"))->format('Y-m-d H:i:s.u');
	}
}
