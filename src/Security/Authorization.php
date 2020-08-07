<?php declare(strict_types = 1);

namespace WB\Security;

class Authorization
{
    public static function getRole(): string
    {
        return $_SESSION['AUTHORIZATION_ROLE'] ?? Roles::ANONYMOUS;
    }
    
    public function setRole(string $role): Authorization
    {
        $_SESSION['AUTHORIZATION_ROLE'] = strtoupper($role);
        
        return $this;
    }
    
    public static function getAccess(): string
    {
        return $_SESSION['AUTHORIZATION_ACCESS'] ??  Access::PUBLIC;
    }
    
    public function setAccess(string $access): Authorization
    {
        $_SESSION['AUTHORIZATION_ACCESS'] =  strtoupper($access);
        
        return $this;
    }
    
    public function setControllersAccess(array $controllers)
    {
        $_SESSION['AUTHORIZATION_CONTROLLERS'] =  $controllers;
	}
	
    public static function hasControllerAcceses(string $namespace, string $method)
    {
        if ( Roles::MASTER === $_SESSION['AUTHORIZATION_ROLE'] 
            || ( isset($_SESSION['AUTHORIZATION_CONTROLLERS'][$namespace]) && in_array($method, $_SESSION['AUTHORIZATION_CONTROLLERS'][$namespace]) )
        ) {
			return true;
		};
		
		return false;
	}
    
    public function destroy(): void
    {
        session_destroy();
    }
}
