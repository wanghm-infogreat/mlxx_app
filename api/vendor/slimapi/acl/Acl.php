<?php
namespace SlimApi\Acl;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class Acl extends ZendAcl
{
	/**
	 * set the default role
	 */
	public $default_role = 'guest';
	
	/**
	 * set the base path of app
	 */
	protected $basepath = '/';
	
    /**
     * @param  Array         $configuration   An array of ignorable routes
     * @param  Role          $currentUserRole The current user's role
     */
    public function __construct($configuration)
    {
        // set $defaultrole
    	if (isset($configuration['default_role'])) {
    		$this->default_role = $configuration['default_role'];
    	}
    	
    	// set basepath
    	if (isset($configuration['basepath'])) {
    		$this->basepath = $configuration['basepath'];
    	}
    	
        // setup roles
        foreach ($configuration['roles'] as $role => $parents) {
            $this->addRole(new Role($role), $parents);
        }

        // setup resources
        foreach ($configuration['resources'] as $resource => $parent) {
        	if ($parent != null) {
        		$parent = $this->basepath.$parent;
        	}
            $this->addResource(new Resource($this->basepath.$resource), $parent);
        }

        // setup allows
        foreach ($configuration['allows'] as $resource => $allow) {
        	if (1 === count($allow)) {
        		$this->allow($allow[0], $this->basepath.$resource, null);
        	} else {
        		foreach($allow[1] as $privilege) {
        			$this->allow($allow[0], $this->basepath.$resource, $privilege);
        		}
        	}
        }

        // setup denies
        foreach ($configuration['denies'] as $resource => $deny) {
        	if (1 === count($deny)) {
        		$this->deny($deny[0], $this->basepath.$resource, null);
        	} else {
        		foreach($allow[1] as $privilege) {
        			$this->deny($deny[0], $this->basepath.$resource, $privilege);
        		}
        	}
        }
        
        return $this;
    }
}
