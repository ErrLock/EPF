<?php
/**
 * @file APIServerTest.php
 * 
 * @copyright ISC License
 * @parblock
 * Copyright (c) 2018 ErrLock <dev@errlock.org>
 * 
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 * @endparblock
 * 
 * @brief Test unit for EPF\API\Server.
 * @author Garinot Pierre <garinot.pierre@errlock.org>
 * @version 0.1
 */

declare(strict_types=1);

namespace EPF;

require_once("EPF/Autoload.php");

use PHPUnit\Framework\TestCase;

class Base extends StdClass
{
    private $secret = 'Base: private';
    protected $accessible = 'Base: protected';
 
    protected function get_secret()
    {
        return $this->secret;
    }
}

class A extends Base
{
	private static $_FRIENDS = array(
		Friend::class
	);
	
	protected $property = 'A: initial';
	
	public function get_property($instance = null)
	{
		if(isset($instance))
		{
			return $instance->property;
		}
		
		return $this->property;
	}
	
	protected function call_method()
	{
		return 'A: method';
	}
}

class Friend extends StdClass
{
	protected $property = 'Friend: initial';
	
	public function get_property($instance, string $name = 'property')
	{
		return $instance->$name;
	}
	
	public function set_property(
		$instance, string $value, string $name = 'property'
	)
	{
		$instance->$name = $value;
	}
	
	public function call_method($instance, string $name = 'call_method')
	{
		return $instance->$name();
	}
}
 
class Transitive extends StdClass
{
	private static $_FRIENDS = array(
		A::class
	);
 
    protected $property = 'Transitive: initial';
}

class Inherited extends A
{
    protected $property = 'foo';
}

/**
 * @brief Friendship tests
 * @see
 * <a href="https://wiki.php.net/rfc/friend-classes">PHP RFC: Class Friendship</a>
 */
final class FriendTest extends TestCase
{
	/**
	 * @brief Test if friendship works
	 */
	public function testCanBeFriend()
	{
		$a = new A();
		$b = new Friend();
		$this->assertEquals($b->get_property($a), 'A: initial');
		$b->set_property($a, 'A: changed');
		$this->assertEquals($a->get_property(), 'A: changed');
		$this->assertEquals($b->call_method($a), 'A: method');
	}
	
	/**
	 * @brief Test if friendship is not symmetric
	 */
	public function testFriendshipIsNotSymmetric()
	{
		$a = new A();
		$b = new Friend();
		
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$a->get_property($b);
	}
	
	/**
	 * @brief Test if friendship is not transitive
	 */
	public function testFriendshipIsNotTransitive()
	{
		$a = new Friend();
		$b = new Transitive();
 
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$a->get_property($b);
	}
	
	/**
	 * @brief Test if friendship is not inherited
	 */
	public function testFriendshipIsNotInherited()
	{
		$a = new Inherited();
		$b = new Friend();
		
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$b->get_property($a);
	}
	
	/**
	 * @brief Test if friendship allow access to inherited members
	 */
	public function testAccessIsInherited()
	{
		$a = new A();
		$b = new Friend();
 
		$this->assertEquals(
			$b->get_property($a), 'A: initial'
		);
		$this->assertEquals(
			$b->get_property($a, 'accessible'), 'Base: protected'
		);
		$this->assertEquals(
			$b->call_method($a, 'get_secret'), 'Base: private'
		);
	}
}
?>
