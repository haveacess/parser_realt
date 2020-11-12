<?php

include __DIR__ . '/TestClass/apples.php';
include __DIR__ . '/TestClass/fruits.php';

use Fruits\Basket;
use Fruits\Apples;
// use Fruits\Red\Apples; //ругатся что ты заюзал уже яблоки, зачем тебе еще одни

echo "Fruits\Red\Apples \n";
$a = new Fruits\Red\Apples(15);
$b = $a->get(1);
var_dump($b);

echo "\n";

echo "Fruits\Basket \n";
$c = new Fruits\Basket(1);
$e = $c->get();
echo "Old: {$e} \n";
$c->Add(3);
$e = $c->get();
echo "New: {$e} \n";

echo "\n";

echo "Fruits\Apples \n"; 
/* 
	тут именно проверка. 
	У нас есть рабочая область фрукты\Красные с классом Apple. 

	А есть область просто Фрукты. Но так же с классом Apple
*/
$q = new Fruits\Apples();
$s = $q->get();
echo "You have: {$s} apples \n";


?>