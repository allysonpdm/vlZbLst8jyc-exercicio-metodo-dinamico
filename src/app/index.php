<?php

require_once('vendor/autoload.php');

use App\Database\Connection\User;


$name = 'teste';
$email = 'email@example.com';
$user = new User();

var_dump($user->findByName($name));
var_dump($user->findByEmail($email));
var_dump($user->findByEmailAndPassword($email, 'teste'));
var_dump($user->findByNameAndEmailOrPassword('carlos', 'teste@teste', '123'));
