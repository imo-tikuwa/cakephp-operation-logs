<?php
namespace OperationLogs\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * OperationLogsSimple middleware
 *
 * @property \OperationLogs\Model\Table\OperationLogsTable $OperationLogs
 */
class OperationLogsSimpleMiddleware
{

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
		$request_url = $request->getUri()->getPath();
		$user_agent = @$request->getHeader('User-Agent')[0];

		$request_time = $this->getCurrentDateTime();
		$response = $next($request, $response);
		$response_time = $this->getCurrentDateTime();

		$client_ip = Router::getRequest()->clientIp();

		$this->saveLog($client_ip, $user_agent, $request_url, $request_time, $response_time);

		return $response;
	}

	/**
	 * 保存処理
	 * @param string $client_ip
	 * @param string $user_agent
	 * @param string $request_url
	 * @param \DateTime $request_time
	 * @param \DateTime $response_time
	 */
	protected function saveLog($client_ip = "", $user_agent = null, $request_url = "", $request_time, $response_time)
	{
		$this->OperationLogs = TableRegistry::getTableLocator()->get('OperationLogs.OperationLogs');
		$entity = $this->OperationLogs->newEntity([
				'client_ip' => $client_ip,
				'user_agent' => $user_agent,
				'request_url' => $request_url,
				'request_time' => $request_time,
				'response_time' => $response_time,
		], ['validate' => false]);
		$this->OperationLogs->save($entity, ['validate' => false]);
	}

	/**
	 * マイクロ秒(小数点6桁)付きのDateTimeオブジェクトを返す
	 * @return \DateTime
	 */
	protected function getCurrentDateTime()
	{
		list($microtime, $unixtime) = explode(" ", microtime(false));
		$milliseconds = substr($microtime, 1, 7);
		$datetimes = date('Y-m-d H:i:s', $unixtime);
		return (new \DateTime("{$datetimes}{$milliseconds}"))->format('Y-m-d H:i:s.u');
	}
}
