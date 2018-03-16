<?php
/**
 * @file AutoloadTest.php
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
 * @brief Test unit for EPF\Autoload.
 * @author Garinot Pierre <garinot.pierre@errlock.org>
 * @version 0.1
 */

declare(strict_types=1);

namespace EPF;

require_once("EPF/Autoload.php");

use PHPUnit\Framework\TestCase;

/**
 * @brief Autoload test
 */
final class AutoloadTest extends TestCase
{
	/**
	 * @brief Test if Autoload registers itself
	 */
	public function testRegistersItself()
	{
		$path = realpath(__DIR__ .'/../src/EPF');
		
		$al = $this->find_loader(__NAMESPACE__, $path);
		
		$this->assertInstanceOf(Autoload::class, $al);
	}
	
	/**
	 * @brief Test if Autoload registers
	 */
	public function testRegisters()
	{
		$al = Autoload::register('testNS', __DIR__ .'/autoload');
		
		$this->assertInstanceOf(Autoload::class, $al);
		
		$al = $this->find_loader('testNS', realpath(__DIR__ .'/autoload'));
		
		$this->assertInstanceOf(Autoload::class, $al);
	}
	
	/**
	 * @brief Test if Autoload loads
	 * 
	 * @depends testRegisters
	 */
	public function testLoads()
	{
		$this->assertTrue(class_exists('testNS\testClass'));
	}
	
	/**
	 * @brief Find a registered Autoload
	 */
	private function find_loader($ns, $path)
	{
		$result = null;
		
		foreach(spl_autoload_functions() as $al)
		{
			if(
				is_array($al)
				&& is_object($al[0])
				&& is_a($al[0], Autoload::class)
			)
			{
				$al = $al[0];
				if(
					($al->get_namespace() == $ns)
					&& ($al->get_path() == $path)
					&& ($al->get_suffixes() == Autoload::DEFAULT_SUFFIXES)
				)
				{
					$result = $al;
					break;
				}
			}
		}
		
		return $result;
	}
}
?>
