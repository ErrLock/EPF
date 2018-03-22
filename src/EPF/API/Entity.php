<?php
/**
 * @file Entity.php
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
 * @brief Short file description.
 * @details A detailed description.
 * @author Garinot Pierre <garinot.pierre@errlock.org>
 * @version 0.1
 */

namespace EPF\API;

/**
 * @brief 
 * @details 
 */
class Entity
{
	private $dom = null;
	private $name = null;
	private $api = null;
	private $parent = null;
	private $properties = array(); /**< Desc */
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(string $name)
	{
		$this->set_name($name);
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
	public function __toString()
	{
		return $this->getDOM()->saveXML();
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
	public function __call(string $method, array $args)
	{
		$caller = debug_backtrace(null, 2)[1]["class"];
		if(!method_exists(self::class, $method))
		{
			throw new \Error(self::class ."::". $method ." doesn't exists");
		}
		
		// This is our friend
		if(is_a($caller, 'EPF\API\Server'))
		{
			throw new \Error("Call to ". self::class ."::". $method .
			" not allowed from ". $caller);
		}
		
		$allowed = false;
		switch($method)
		{
			case "set_api":
			case "set_property":
				$allowed = true;
				break;
		}
		
		if(!$allowed)
		{
			throw new \Error("Call to ". self::class ."::". $method .
			" not allowed from ". $caller);
		}
		
		return call_user_func_array(array($this, $method), $args);
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
	public function getName()
	{
		return $this->name;
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
	public function getParent()
	{
		return $this->parent;
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
	public function getAPI()
	{
		return $this->api;
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
	public function getProperty(string $name)
	{
		if(!$this->hasProperty($name))
		{
			return null;
		}
		
		return $this->properties[$name];
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
	protected function populate()
	{
	
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
	private function set_api(Server $api)
	{
		if(isset($this->api))
		{
			throw new \Error("API already set");
		}
		
		$this->api = $api;
		foreach($this->properties as $prop)
		{
			if(is_a($prop, self::class))
			{
				$prop->set_api($api);
			}
		}
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
	private function set_parent(Entity $parent)
	{
		if(isset($this->parent))
		{
			throw new \Error("Parent already set");
		}
		
		$this->parent = $parent;
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
	private function set_property(string $name, $value)
	{
		$type = $this->get_property_type($value);
		$p_type = $type;
		if($this->hasProperty($name))
		{
			/*
			 * Do not use getProperty,
			 * we might get stuck in a loop if set_property is used in child class
			 */
			$p_type = $this->get_property_type($this->properties[$name]);
			if($type != $p_type)
			{
				throw new \Error("Type mismatch: ". $type ." != ". $p_type);
			}
		}
		
		if($type == "entity")
		{
			$this->entity_init($value);
		}
		
		$this->properties[$name] = $value;
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
	private function entity_init(Entity $entity)
	{
		$p_api = $entity->getAPI();
		$api = $this->getAPI();
		if(is_null($p_api))
		{
			if(!is_null($api))
			{
				$entity->set_api($api);
			}
		}
		elseif($p_api != $api)
		{
			throw new \Error("Entities are not from the same API");
		}
		
		$entity->set_parent($this);
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
	public function getDOM()
	{
		if(!isset($this->dom))
		{
			$this->dom_init();
		}
		
		// Clone it, only us should modify it
		$dom = clone $this->dom;
		return  $dom;
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
	private function set_name(string $name)
	{
		$this->name = $name;
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
	public function getURI()
	{
		$parent = $this->getParent();
		$name = $this->getName();
		
		if(is_null($parent))
		{
			return $name;
		}
		
		$uri = $parent->getURI();
		if(strrpos($uri, '/') !== (strlen($uri) -1))
		{
			$uri .= "/";
		}
		$uri .= $name;
		
		return  $uri;
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
	public function hasProperty(string $name)
	{
		return array_key_exists($name, $this->properties);
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
	private function dom_init()
	{
		$this->dom = new \DomDocument();
		$this->dom->loadXML(
			'<?xml version="1.0" encoding="utf-8"?><entity />',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		
		$api = $this->getAPI();
		if(!is_null($api))
		{
			$this->dom_add_property("index", $api);
		}
		$this->dom_add_property("self", $this);
		
		$parent = $this->getParent();
		if(!is_null($parent))
		{
			$this->dom_add_property("collection", $parent);
		}
		
		// populate
		$this->populate();
		
		foreach($this->properties as $name => $value)
		{
			$this->dom_add_property($name, $value);
		}
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
	private function dom_create_link(string $name, Entity $entity)
	{
		$rel = "item";
		if($entity == $this->getAPI() && $name == "index")
		{
			$rel = "index";
		}
		elseif($entity == $this)
		{
			$rel = "self";
		}
		elseif($entity == $this->getParent())
		{
			$rel = "collection";
		}
		
		$node = $this->dom->createElement("link");
		$node->setAttribute("rel", $rel);
		$node->setAttribute("href", $entity->getURI());
		
		return $node;
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
	private function dom_add_property(string $name, $value)
	{
		$node = null;
		$type = $this->get_property_type($value);
		switch($type)
		{
			case "entity":
				$node = $this->dom_create_link($name, $value);
				break;
			case "string":
				$node = $this->dom->createElement($type, $value);
				break;
		}
		$node->setAttribute("name", $name);
		$node->setIdAttribute("name", true);
		
		$this->dom->documentElement->appendChild($node);
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
	private function get_property_type($value)
	{
		$type = gettype($value);
		switch($type)
		{
			case "object":
				if(!is_a($value, self::class))
				{
					throw new \Error("Invalid type: ". get_class($value));
				}
				$type = "entity";
				break;
			case "string":
				break;
			default:
				throw new \Error("Invalid type: ". $type);
				break;
		}
		
		return $type;
	}
}
?>
