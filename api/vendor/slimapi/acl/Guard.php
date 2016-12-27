<?php
namespace SlimApi\Acl;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SlimApi\Acl\Acl;

class Guard
{
    /**
     * @param  Array $configuration An array of ignorable routes
     */
    public function __construct($configuration)
    {
        $this->acl             = new Acl($configuration);
    }

    /**
     * Invoke middleware
     *
     * @param  RequestInterface  $request  PSR7 request object
     * @param  ResponseInterface $response PSR7 response object
     * @param  callable          $next     Next middleware callable
     *
     * @return ResponseInterface PSR7 response object
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $isAllowed = false;
    	
    	// get current user role
    	$this->currentUserRole = $this->acl->default_role;
    	$token = $request->getAttribute('token');
    	if ($token != null) {
    		$this->currentUserRole = $token->role;
    	}
    	
    	// get request resource
    	$resource = $request->getUri()->getBasePath().'/';

    	/* add if want control sub path */
    	/*
    	$path = split('/', $request->getUri()->getBasePath().$request->getUri()->getPath());
    	if (count($path) > 1) {
    		array_pop($path);
    		$resource = implode('/', $path).'/';
    	}
    	*/

    	if ($this->acl->hasResource($resource)) {
            $isAllowed = $isAllowed || $this->acl->isAllowed($this->currentUserRole, $resource, strtolower($request->getMethod()));
        }

        if (!$isAllowed) {
            return $response->withStatus(403, $this->currentUserRole.' is not allowed access to this location.');
        }
        return $next($request, $response);
    }

}
