<?php declare(strict_types = 1);

namespace App\Base\Controllers;

use \App\Base\Interfaces\ControllerInterface;

abstract class AbstractController implements ControllerInterface
{
	protected $layout = "default";
	
    /** CRUD */
    public function index()
    {
		$this->view(["index"]);
	}
	
    public function create()
    {
		$this->view(["create"]);
	}
	
    public function edit()
    {
		$this->view(["edit"]);
	}
	
    public function delete()
    {
		$this->view(["delete"]);
	}
    
    /** API */
    public function list(){}
    public function get(){}
    public function post(){}
    public function update(){}
    public function patch(){}
    public function destroy(){}
    
    protected function view(array $templates)
    {	
		include WEBSITE_TEMPLATE_PATH . "/" . $this->layout . "/partials/page_begin.tpl";
		
		foreach ($templates as $template => $model) {
			if ( is_string($template) ) {
				include WEBSITE_TEMPLATE_PATH . $this->layout .  "/" . $template . ".tpl";
			} else {
				$template = $model;
				$model = null;
				
				include WEBSITE_TEMPLATE_PATH . $this->layout . "/" . $template . ".tpl";
			}
		}
		
		include WEBSITE_TEMPLATE_PATH . "/" . $this->layout . "/partials/page_end.tpl";
	}
	
    protected function response(array $response, int $httpResponseCode = 200): void
    {
        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

