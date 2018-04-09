<?php
class A extends \EPF\StdClass
{
	private static $_FRIENDS = array(
		B::class
	);
	
	protected $property = 'A: property';
}

class B extends \EPF\StdClass
{
	public function print_property(A $instance)
	{
		echo $instance->property ."\n";
	}
}

$a = new A();
$b = new B();
$b->print_property($a);
?>
