<?php declare(strict_types = 1);

namespace WB\Http;

use WB\Utils\Validator;

class Request
{
    private $parameters;
    private $post;
    private $json;
    private $headers;
    private $validator;
    
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
        $this->parameters = $this->processParameters();
        $this->post = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS, true);
        $this->json = json_decode(file_get_contents('php://input'), true);
        $this->headers = getallheaders();
    }
    
    public function hasHeader($key): bool
    {
        return isset($this->headers[$key]);
    }
    
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getHeader($name)
    {
        foreach (getallheaders() as $key => $value) {
            if ($name === $key) {
                return $value;
            }
        }
        
        return null;
    }

    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    public function getUriWithoutParameters()
    {
        $url = $_SERVER['REQUEST_URI'];
        
        if ( strpos($url, '?') !== false ) {
            $exploded = explode("?", $_SERVER['REQUEST_URI']);
            return $exploded[0];
        }
        if ( strpos($url, '&') !== false ) {
            $exploded = explode("&", $_SERVER['REQUEST_URI']);
            return $exploded[0];
        }
        
        return $url;
    }
    
    private function processParameters()
    {
        $url = $_SERVER['REQUEST_URI'];
        
        if (strpos($url, '?') > -1) {
            $parameters = explode('&', explode('?', $url)[1]);
        } else {
            $parameters = explode('&', $url);
            unset($parameters[0]);
        }
        
        $parameters = array_filter($parameters);
        
        $result = [];
        foreach ($parameters as $parameter) {
            $parameter = explode('=', $parameter);
            $result[$parameter[0]] = $parameter[1];
        }
        
        return $result;
    }
    
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    public function getRouterParameters(): array
    {
        return WB_ROUTER_PARAMETERS;
    }
    
    public function getRouterParameter(string $key): ?string
    {
        return htmlentities(WB_ROUTER_PARAMETERS[$key], ENT_COMPAT, "UTF-8") ?? null;
    }
    
    public function hasRouterParameter(string $key): bool
    {
        return isset(WB_ROUTER_PARAMETERS[$key]);
    }
    
    public function hasParameter(string $key): bool
    {
        return isset($this->parameters[$key]);
    }
    
    public function getParameter(string $key, ?array $strtr = null): ?string
    {
        return isset($this->parameters[$key]) ? isset($strtr) ? strtr(htmlentities($this->parameters[$key], ENT_COMPAT, "UTF-8"), $strtr) : htmlentities($this->parameters[$key], ENT_COMPAT, "UTF-8") : null;
    }
    
    public function jsonDecodeParameter(string $key): array
    {
		return json_decode(strtr($this->getParameter($key) ?? "[]", ["%20" => " ", "%22" => "\""] ), true);
    }
    
    public function getPosts(): ?array
    {
        if ( $this->hasHeader('Content-Type') && $this->getHeader('Content-Type') == 'application/json' ) {
			return $this->json;
		} else {
			return $this->post;
		}
    }

    public function hasPost(string $key): ?string
    {
        return $this->post[$key] ?? null;
    }
    
    public function getPost(string $key): ?string
    {
        return htmlentities($this->post[$key], ENT_COMPAT, "UTF-8") ?? null;
    }
    
    public function hasJson(string $key): ?string
    {
        return htmlentities($this->json[$key], ENT_COMPAT, "UTF-8") ?? null;
    }
    
    public function validate(array $rules) {
        if ( $this->hasHeader('Content-Type') && $this->getHeader('Content-Type') == 'application/json' ) {
            return $this->validator->isValid($this->json, $rules) ? $this->json : $this->validator->getErrors();
		} else {
			return $this->validator->isValid($this->post, $rules) ? $this->post : $this->validator->getErrors();
		}
    }

    public function isValidJSON(array $rules) {
        return $this->validator->isValid($this->json, $rules);
    }

    public function getJson(string $key): ?string
    {
        return htmlentities($this->json[$key], ENT_COMPAT, "UTF-8") ?? null;
    }
    
    public function getValidatorErrors(): array
    {
		return $this->validator->getErrors();
	}
    /*
    private function validate(array $rules) {
        
        $this->validator->isValid($this->post, $rules) ?: $this->redirect($this->getUri());
    }
    
    private function validateJSON(array $rules) {
        
        $this->validator->isValid($this->json, $rules) ?: $this->redirect($this->getUri());
    }
    */
}
