<?php
require_once("EPF/Autoload.php");

use EPF\API\Entity;
use EPF\API\Server;

class EntityPlayer extends Entity
{
	public function __construct(string $name)
	{
		parent::__construct($name);
		
		$displayName = null;
		switch($this->getName())
		{
			case "player1":
				$displayName = "Player 1";
				break;
			case "player2":
				$displayName = "Player 2";
				break;
		}
		$this->set_property("displayName", $displayName);
		$this->set_property("friends", new Entity("friends"));
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
			$this->set_property($name, new EntityPlayer($name));
		}
	}
}

class ServerFromClass extends Server
{
	public function __construct()
	{
		parent::__construct();
		
		$this->set_property("players", new EntityPlayerList());
	}
}
?>
