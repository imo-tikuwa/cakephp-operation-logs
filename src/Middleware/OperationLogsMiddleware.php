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
	];

	/**
	 * constructer
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		$this->setConfig($config);
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

		// 除外設定
		$request_url = $request->getUri()->getPath();
		foreach ($this->getConfig('exclude_urls') as $exclude_url) {
			if (OperationLogsUtils::startsWith($request_url, $exclude_url)) {
				return $next($request, $response);
			}
		}

		$request_time = $this->_getCurrentDateTime();

		$response = $next($request, $response);

		// リクエスト後処理
		$response_time = $this->_getCurrentDateTime();

		$this->OperationLogs = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogs');
		$entity = $this->OperationLogs->newEntity([
				'client_ip' => Router::getRequest()->clientIp(),
				'user_agent' => @$request->getHeader('User-Agent')[0],
				'request_url' => $request_url,
				'request_time' => $request_time,
				'response_time' => $response_time,
		]);
		$this->OperationLogs->save($entity);

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
