<?php
require_once("EPF/Autoload.php");

use EPF\API\Entity;
use EPF\API\EntityRef;
use EPF\API\Resource;
use EPF\API\Server;

class FriendsList extends Entity
{
	private $player = null;
	private $all_children = array(
		"player1" => array( "player2" ),
		"player2" => array()
	);
	
	public function __construct(string $player, string $name = "friends")
	{
		$this->player = $player;
		parent::__construct($name);
	}
	
	public function getProperty(string $name)
	{
		/*
		 * Create child when asked
		 */
		$this->check_player($name);
		
		return parent::getProperty($name);
	}
	
	protected function populate()
	{
		/*
		 * We fully populate the dom only if asked for it
		 */
		parent::populate();
		foreach($this->all_children[$this->player] as $name)
		{
			$this->check_player($name);
		}
	}
	
	private function check_player(string $name)
	{
		if(
			!$this->hasProperty($name)
			&& in_array($name, $this->all_children[$this->player])
		)
		{
			$target = $this->getIndex()->getEntity("/players/". $name);
			$this->setProperty($name, new EntityRef($target));
		}
	}
}

class EntityPlayer extends Entity
{
	public function __construct(string $name)
	{
		parent::__construct($name);
		
		$a_url = __DIR__ ."/avatar.jpg";
		switch($this->getName())
		{
			case "player1":
				$this->setProperty("firstName", "Kevin");
				$this->setProperty("lastName", "Sookocheff");
				$this->setProperty("pseudonym", "soofaloofa");
				$this->setProperty("avatar", new Resource($a_url));
				break;
			case "player2":
				$this->setProperty("firstName", "Albert");
				$this->setProperty("lastName", "Hofmann");
				$this->setProperty("pseudonym", "bicycleman");
				$this->setProperty("avatar", new Resource($a_url));
				break;
		}
		$this->setProperty("friends", new FriendsList($name));
	}
}

class EntityPlayerList extends Entity
{
	private $all_children = array(
		"player1",
		"player2"
	);
	
	public function __construct(string $name = "players")
	{
		parent::__construct($name);
	}
	
	public function getProperty(string $name)
	{
		/*
		 * Create child when asked
		 */
		$this->check_player($name);
		
		return parent::getProperty($name);
	}
	
	protected function populate()
	{
		/*
		 * We fully populate the dom only if asked for it
		 */
		parent::populate();
		foreach($this->all_children as $name)
		{
			$this->check_player($name);
		}
	}
	
	private function check_player(string $name)
	{
		if(
			!$this->hasProperty($name)
			&& in_array($name, $this->all_children)
		)
		{
			$this->setProperty($name, new EntityPlayer($name));
		}
	}
}

class ServerFromClass extends Server
{
	public function __construct()
	{
		parent::__construct();
		
		$this->setProperty("players", new EntityPlayerList());
	}
}
?>
