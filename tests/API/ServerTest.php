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
	}
	
	/**
	 * @brief Test if Autoload registers itself
	 */
	public function testFromCode()
	{
		$this->api = new Server();
		
		$players = $this->api->createEntity("players");
		$this->api->appendChild($players);
		
		$player_1 = $this->api->createEntity("player1");
		$players->appendChild($player_1);

		$player_1_friends = $this->api->createEntity("friends");
		$player_1->appendChild($player_1_friends);

		$player_2 = $this->api->createEntity("player2");
		$players->appendChild($player_2);

		$player_2_friends = $this->api->createEntity("friends");
		$player_2->appendChild($player_2_friends);
		
		$this->api_check();
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testFromClass()
	{
		require_once(__DIR__ .'/resources/ServerFromClass.php');
		$this->api = new \ServerFromClass();
		
		$this->api_check();
	}
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	private function api_check()
	{
		foreach($this->test_paths as $path)
		{
			$x_name = substr($path, 1);
			if(empty($x_name))
			{
				$x_name = "root";
			}
			else
			{
				$x_name = str_replace('/', '_', $x_name);
			}
			$actual = $this->api->GET($path);
			$this->expected->load(__DIR__ .'/resources/xml/'. $x_name .'.xml');
			$this->assertEquals($this->expected, $actual);
		}
	}
}
?>
