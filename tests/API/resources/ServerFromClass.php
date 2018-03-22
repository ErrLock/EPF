<?php
require_once("EPF/Autoload.php");

use EPF\API\Entity;
use EPF\API\Server;

class EntityPlayer extends Entity
{
	protected function init()
	{
		parent::init();
		
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
	
	public function getChild(string $name)
	{
		/*
		 * Create child when asked
		 */
		$this->check_player($name);
		
		return parent::getChild($name);
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
		if(!$this->childExists($name) && in_array($name, $this->all_children))
		{
			$root = $this->get_root();
			if(is_null($root))
			{
				throw new \Error("No root");
			}
			$child = $root->createEntity($name, EntityPlayer::class);
			$this->appendChild($child);
		}
	}
}

class ServerFromClass extends Server
{
	protected function init()
	{
		parent::init();
		$players = $this->createEntity("players", EntityPlayerList::class);
		$this->appendChild($players);
	}
}
?>
