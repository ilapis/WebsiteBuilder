<?php declare(strict_types = 1);

namespace WB\Models;

use WB\Models\MySqliModel;

class DefaultModel
{
    protected $database;
    protected $stmt;
    
    public function __construct(MySqliModel $database)
    {
        $this->database = $database->getConnection();
    }
    
    protected function prepare(string $query)
    {
		$this->stmt = $this->database->prepare($query) OR $this->response($this->database->error);
		
		return $this;
	}
	
    protected function bind_param(array $bind_param)
    {
		$this->stmt->bind_param(... $bind_param) OR $this->response($this->database->error);
		
		return $this;
	}
	
    protected function bind_result(array $bind_result)
    {
		$this->stmt->bind_result(... $bind_result) OR $this->response($this->database->error);
		
		return $this;
	}
	
    protected function execute()
    {
		$this->stmt->execute() OR $this->response($this->database->error);
		
		return $this->result();
	}
	
	protected function result()
	{
		return [
			"affected_rows" => $this->stmt->affected_rows,
			"insert_id" => $this->stmt->insert_id,
		];
	}
	
	protected function response(string $message, string $type = "error" ): void
	{
		$response = [];
		
		switch ($type) {
			case "error":
				$response = ["response" => "error", "message" => $message];
				break;
			default:
				$response = ["response" => "ok", "message" => $message];
				break;
		}
		
		print(json_encode($response));
		exit(0);
	}
}
