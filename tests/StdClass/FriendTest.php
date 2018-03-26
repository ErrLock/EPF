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

namespace EPF\StdClass;

require_once("EPF/Autoload.php");

use PHPUnit\Framework\TestCase;

class A
{
	use Friend;
	
	protected $property = true;
	
	private static function _friend_config()
	{
		self::_friend(FriendOfA::class);
	}
	
	protected function touch()
	{
		return true;
	}
}

class FriendOfA
{
	public function touch_property(A $instance)
	{
		return $instance->property;
	}
	
	public function touch_method(A $instance)
	{
		return $instance->touch();
	}
}

class SymmetricA
{
	protected $property = true;
 
    public function touch(SymmetricB $instance)
    {
        return $instance->property;
    }
}

class SymmetricB
{
	use Friend;
	
	protected $property = true;
	
	private static function _friend_config()
	{
		self::_friend(SymmetricA::class);
	}
	
	public function touch(SymmetricA $instance)
    {
        echo $instance->property;
    }
}

class TransitiveA
{       
    public function touch(TransitiveC $instance)
    {
        echo $instance->property;
    }
}
 
class TransitiveB
{   
	use Friend;
	
	private static function _friend_config()
	{
		self::_friend(TransitiveA::class);
	}
}
 
class TransitiveC
{
	use Friend;
	
	private static function _friend_config()
	{
		self::_friend(TransitiveB::class);
	}
 
    protected $property = 'foo';
}

class InheritedBase
{
	use Friend;
	
	private static function _friend_config()
	{
		self::_friend(InheritedFriendly::class);
	}
}

class InheritedDerived extends InheritedBase
{
    protected $property = 'foo';
}

class InheritedFriendly
{
    public function touch(InheritedDerived $instance)
    {
        echo $instance->property;
    }
}

class AccessBase
{
    private $secret = true;
    protected $accessible = true;
 
    protected function touch()
    {
        return $this->secret;
    }
}

class AccessDerived extends AccessBase
{
	use Friend;
	
	private static function _friend_config()
	{
		self::_friend(AccessFriendly::class);
	}
 
    protected $someProperty = true;
}

class AccessFriendly
{
    public function touch_property(AccessDerived $instance)
    {
        return $instance->someProperty;
	}
	
    public function touch_accessible(AccessDerived $instance)
    {
        return $instance->accessible;
    }
    
    public function touch_secret(AccessDerived $instance)
    {
        return $instance->secret;
    }
    
    public function touch_secret_method(AccessDerived $instance)
    {
        return $instance->touch();
    }
}

/**
 * @brief Autoload test
 */
final class FriendTest extends TestCase
{
	/**
	 * @brief Test class generated api
	 */
	public function testCanBeFriend()
	{
		$a = new A();
		$b = new FriendOfA();
		$this->assertTrue($b->touch_property($a));
		$this->assertTrue($b->touch_method($a));
	}
	
	public function testFriendshipIsNotSymmetric()
	{
		$a = new SymmetricA();
		$b = new SymmetricB();
		
		$this->assertTrue($a->touch($b));
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$b->touch($a);
	}
	
	public function testFriendshipIsNotTransitive()
	{
		$a = new TransitiveA();
		$c = new TransitiveC();
 
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$a->touch($c);
	}
	
	public function testFriendshipIsNotInherited()
	{
		$derived = new InheritedDerived();
		$friendly = new InheritedFriendly();
		
		$this->expectException('Error');
		$this->expectExceptionMessage("Cannot access protected property");
		$friendly->touch($derived);
	}
	
	public function testAccessIsInherited()
	{
		$derived = new AccessDerived();
		$friendly = new AccessFriendly();
 
		$friendly->touch_property($derived);
		$friendly->touch_accessible($derived);
		$friendly->touch_secret_method($derived);
		$this->expectException('Error');
		$this->expectExceptionMessage("Undefined property");
		$friendly->touch_secret($derived);
	}
}
?>
