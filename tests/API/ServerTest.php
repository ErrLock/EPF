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

class EntityPlayer extends Entity
{
	protected function set_parent(Entity $parent)
	{
		parent::set_parent($parent);
		
		$root = $this->get_root();
		$friends = $root->createEntity("friends");
		$this->appendChild($friends);
	}
}

class EntityPlayerList extends Entity
{
	private $all_children = array(
		"player1",
		"player2"
	);
	
	public function childExists(string $name)
	{
		/*
		 * Create child when asked
		 */
		$result = parent::childExists($name);
		if(!$result && in_array($name, $this->all_children))
		{
			$root = $this->get_root();
			$child = $root->createEntity($name, EntityPlayer::class);
			$this->appendChild($child);
			$result = parent::childExists($name);
		}
		
		return $result;
	}
	
	protected function get_dom()
	{
		/*
		 * We fully populate the dom only if asked for it
		 */
		foreach($this->all_children as $child_name)
		{
			$this->childExists($child_name);
		}
		
		return parent::get_dom();
	}
}

class ServerFromClass extends Server
{
	public function __construct()
	{
		parent::__construct();
		$players = $this->createEntity("players", EntityPlayerList::class);
		$this->appendChild($players);
	}
}

/**
 * @brief Autoload test
 */
final class ServerTest extends TestCase
{
	/**
	 * @brief Test if Autoload registers itself
	 */
	public function testFromCode()
	{
		$api = new Server();
		
		$players = $api->createEntity("players");
		$api->appendChild($players);
		
		$player_1 = $api->createEntity("player1");
		$players->appendChild($player_1);

		$player_1_friends = $api->createEntity("friends");
		$player_1->appendChild($player_1_friends);

		$player_2 = $api->createEntity("player2");
		$players->appendChild($player_2);

		$player_2_friends = $api->createEntity("friends");
		$player_2->appendChild($player_2_friends);
		
		$this->api_check($api);
	}
	
	/**
	 * @brief Test class generated api
	 */
	public function testFromClass()
	{
		$api = new ServerFromClass();
		
		$this->api_check($api);
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
	private function api_check(Server $api)
	{
		$expected = new \DomDocument();
		$expected->preserveWhiteSpace = false;
		
		$test_paths = array(
			"/",
			"/players",
			"/players/player1",
		);
		
		foreach($test_paths as $path)
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
			$actual = $api->GET($path);
			$expected->load(__DIR__ .'/resources/xml/'. $x_name .'.xml');
			$this->assertEquals($expected, $actual);
		
		}
	}
}
?>
