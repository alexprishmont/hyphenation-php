<?php
namespace Validation;
require_once (__DIR__.'/validation.php');
class EmailValidation implements Validation {
    private $email;
    public function __construct(string $email) { $this->email = $email; }
    public function validate():int {
        return preg_match("/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+.[a-zA-Z0-9-.]+$/", $this->email);
    }
}
