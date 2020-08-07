<?php declare(strict_types = 1);

namespace App\Base\Interfaces;

interface ControllerInterface
{
	/** CRUD */
    public function index();
    public function create();
    public function edit();
    public function delete();
    
    /** API */
    public function list();
    public function get();
    public function post();
    public function update();
    public function patch();
    public function destroy();   
}

