<?php declare(strict_types = 1);

namespace WB\Utils;

class Validator
{
    /**
     * @var array
     */
    private $errorList = [];
    
    public function isValid(array $request, array $rules): bool
    {
        $this->errorList = [];
        foreach ($rules as $key => $keyRules) {
            if (is_array($keyRules)) {
                foreach ($keyRules as $keyRuleName => $keyRule) {
                    call_user_func_array([$this, $keyRuleName], [$key, $request, $keyRule]);
                }
            } else {
                call_user_func_array([$this, $keyRules], [$key, $request, $keyRules]);
            }
        }
        
        return empty($this->errorList);
    }
    
    public function getErrors(): array
    {
        return $this->errorList;
    }
    
    private function minlength($key, $request, $keyRule)
    {
        $keyValue = $request[$key];
        if (mb_strlen($keyValue) < (int) $keyRule) {
            $this->errorList[$key] = [
                'minlength' =>  $keyRule,
                'data' => $keyValue,
                'message' => 'Minimum length is ' . $keyRule
            ];
        }
    }
    
    private function maxlength($key, $request, $keyRule)
    {
        $keyValue = $request[$key];
        if (mb_strlen($keyValue) > (int) $keyRule) {
            $this->errorList[$key] = [
                'maxlength' =>  $keyRule,
                'data' => $keyValue,
                'message' => 'Maximim length is ' . $keyRule
            ];
        }
    }
    
    
    private function equalTo($key, $request, $keyRule)
    {
        $keyValue = $request[$key];
        if ($keyValue !== $request->get(strtr($keyRule, ["#" => ""]))) {
            $this->errorList[$key] = [
                'equalTo' =>  strtr($keyRule, ["#" => ""]),
                'data' => $keyValue,
                'message' => 'Value does not match'
            ];
        };
    }
    
    private function required($key, $request, $keyRule)
    {
        $keyValue = $request[$key];
        if (null === $keyValue) {
            $this->errorList[$key] = [
                'required' =>  $keyRule,
                'data' => $keyValue,
                'message' => 'This is required field'
            ];
        };
    }
    
    private function email($key, $request, $keyRule)
    {
        $keyValue = $request[$key];
        if (!filter_var($keyValue, FILTER_VALIDATE_EMAIL)) {
            $this->errorList[$key] = [
                'email' =>  $keyRule,
                'data' => $keyValue,
                'message' => 'Email is in wrong format'
            ];
        }
    }
}
