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

namespace EPF\API;

require_once("EPF/Autoload.php");
require_once(__DIR__ .'/resources/ServerFromClass.php');

use PHPUnit\Framework\TestCase;

/**
 * @brief Autoload test
 */
final class ServerTest extends TestCase
{
	private $api = null;
	private $test_paths = array(
		"/",
		"/players",
		"/players/player1",
		"/players/player1/friends",
	);
	private $expected = null;
	private $res = __DIR__ .'/resources/xml';
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function setup()
	{
		$this->expected = new \DomDocument();
		$this->expected->preserveWhiteSpace = false;
		$this->api = new \ServerFromClass();
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testRoot()
	{
		$actual = $this->api->GET("/");
		$this->expected->load($this->res .'/root.xml');
		$this->assertEquals($this->expected, $actual);
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testPlayers()
	{
		$actual = $this->api->GET("/players");
		$this->expected->load($this->res .'/players.xml');
		$this->assertEquals($this->expected, $actual);
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testPlayer()
	{
		$actual = $this->api->GET("/players/player1");
		$this->expected->load($this->res .'/players_player1.xml');
		$this->assertEquals($this->expected, $actual);
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testFriends()
	{
		$actual = $this->api->GET("/players/player1/friends");
		$this->expected->load($this->res .'/players_player1_friends.xml');
		$this->assertEquals($this->expected, $actual);
	}
}
?>
