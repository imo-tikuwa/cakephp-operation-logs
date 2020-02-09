<?php
namespace OperationLogsPlugin\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\I18n\Time;

/**
 * OperationLogs middleware
 *
 * @property \OperationLogsPlugin\Model\Table\OperationLogsTable $OperationLogs
 *
 * @see https://book.cakephp.org/3/ja/controllers/middleware.html
 */
class OperationLogsMiddleware
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
		$request_time = Time::now();

		$response = $next($request, $response);

		$response_time = Time::now();

		$this->OperationLogs = TableRegistry::getTableLocator()->get('operation_logs');
		$entity = $this->OperationLogs->newEntity([
				'client_ip' => Router::getRequest()->clientIp(),
				'user_agent' => $request->getHeader('User-Agent')[0],
				'request_url' => $request->getUri()->getPath(),
				'request_time' => $request_time,
				'response_time' => $response_time,
		]);
		$this->OperationLogs->save($entity);

		return $response;
	}
}
