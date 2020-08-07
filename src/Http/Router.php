<?php declare(strict_types = 1);

namespace WB\Http;

use WB\Security\Authorization;
use WB\Container\DefaultContainer;

class Router
{
    private $routes;
    private $pageNotFound;
    private $pageNotAuthorized;
    private $parameters = [];
    private $reqest;
    private $authorization;
    private $container;
    
    public function __construct(
        Request $request,
        Authorization $authorization,
        DefaultContainer $container
    ) {
        $this->request = $request;
        $this->authorization = $authorization;
        $this->container = $container;
    }
    
    public function add(array $route): Router
    {
        $this->routes[] = $route;
        return $this;
    }
    
    public function process(): ?string
    {
        /*
         *  TODO, make as url related array, ROUTER_PARAMETERS
         */
        if ($this->routes === null) { return $this->pageNotFound; }
        
        foreach ($this->routes as $route) {
            $this->parameters = [
                "{METHOD}" => strtolower($this->request->getMethod())
            ];
            
            if (!$this->supportsMethod($route) || !$this->isAuthorized($route)) {
                continue;
            }
            
            if ( isset($route["WEBSITE_TYPE"]) && $route["WEBSITE_TYPE"] !== WEBSITE_TYPE ) {
				continue;
			}
            
            /**
             * If PATTERN is EQUAL to URI and is not redirect
             */
            if ( isset($route["PATTERN"]) ) {
				if (in_array($route["PATTERN"], [$this->request->getUri(), $this->request->getUriWithoutParameters()]) && !isset($route["REDIRECT"])) {
					return $this->returnNamespace($route);
				}
				
				$pattern = explode('/', $route["PATTERN"]);
				
				$uri = explode('/', $this->request->getUriWithoutParameters());
				
				if (count($pattern) !== count($uri)) {
					continue;
				}
				
				$counter = 0;
				
				foreach ($pattern as $key => $value) {
					if ($value === $uri[$key]) {
						$counter++;
					}
					
					if (isset($uri[$key]) && strpos($value, '{') === 0) {
						$counter++;
						$this->parameters[$value] = ucfirst($uri[$key]);
					}
				}

				/**
				 * Redirect if redirection present
				 */
				/*
				if ( count($pattern) === $counter && isset($route["REDIRECT"]) )
				{ 
					$this->redirect(strtr($route['REDIRECT'], $this->parameters)); 
				};
				*/
				
				/**
				 * Return Namespace of controller
				 */
				if (count($pattern) === $counter) {
					return  $this->returnNamespace($route);
				}
			}
			
            if (isset($route["REGEX_PATTERN"])) {
				preg_match($route["REGEX_PATTERN"], $this->request->getUriWithoutParameters(), $matches);
				if ( !empty($matches) ) {
					define("WB_ROUTER_REGEX_PATTERN", $matches);
					return  $this->returnNamespace($route);
				}
			}
        }
        
        return $this->isAuthorized($route) ? $this->pageNotFound :  $this->pageNotAuthorized;
        
    }
    
    public function setPageNotFound(string $pageNotFound): Router
    {
        $this->pageNotFound = $pageNotFound;
        
        return $this;
    }
    
    public function setPageNotAuthorized(string $pageNotAuthorized): Router
    {
        $this->pageNotAuthorized = $pageNotAuthorized;
        
        return $this;
    }
    
    private function returnNamespace(array $route) {
        
        if ( isset($route['AUTHORIZE']) ) {
            $this->container->executeMethod($route['AUTHORIZE'].'::process');
        }
        
        define("WB_ROUTER_PARAMETERS", $this->clearParameters());
        return strtr($route['CONTROLLER'], $this->parameters);
    }
    
    private function clearParameters() : array
    {
        $result = [];
        
        foreach ($this->parameters as $key => $parameter) {
            $key_new = strtolower(strtr($key, ["{" => "", "}" => ""]));
            $result[$key_new] = strtolower($parameter);
        }
        
        return $result;
    }
    
    private function isAuthorized(array $route)
    {
        return !isset($route["ACCESS"]) || in_array($this->authorization->getAccess(), $route["ACCESS"]);
    }
    
    private function supportsMethod(array $route)
    {
        return isset($route["METHOD"]) && in_array($this->request->getMethod(), $route["METHOD"]);
    }
}
