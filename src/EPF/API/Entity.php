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
		$this->name = $name;
		
		$this->dom = new \DomDocument();
		$this->dom->loadXML(
			'<?xml version="1.0" encoding="utf-8"?>'.
			'<entity />',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		
		// Those should always be first
		$this->set_property("@index", $this);
		$this->set_property("@self", $this);
		$this->set_property("@collection", null);
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
		$dom = $this->getDOM();
		$dom->formatOutput = true;
		return $dom->saveXML();
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
		
		// These can call us, on certain conditions
		if(!is_a($caller, 'EPF\API\Entity', true))
		{
			throw new \Error("Call to ". self::class ."::". $method .
			" not allowed from ". $caller);
		}
		
		$allowed = false;
		switch($method)
		{
			case "set_property":
				// only ourself can set @ properties
				$allowed = ($args[0][0] != "@");
				break;
			case "set_api":
				$allowed = ($caller == 'EPF\API\Server');
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
		
		foreach($this->properties as $name => $prop)
		{
			if($name[0] != "@" && $this->get_property_type($prop) == 'entity')
			{
				$prop->set_api($api);
			}
		}
		
		$this->dom_update();
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
		
		$api = $parent->getAPI();
		if(!is_null($api))
		{
			$this->set_api($api);
		}
		else
		{
			$this->dom_update();
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
	private function set_property(string $name, $value)
	{
		$this->set_property_check($name, $value);
		
		if(
			$this->get_property_type($value) == "entity"
			&& $value !== $this
			&& $name[0] != "@"
		)
		{
			$value->set_parent($this);
		}
		
		$this->properties[$name] = $value;
		$this->dom_set_property($name, $value);
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
	private function set_property_check(string $name, $value)
	{
		$set_type = $this->get_property_type($value);
		// Only collection can be set to NULL, and only the first time
		if(
			$set_type == 'NULL'
			&& ($name != "@collection" || $this->hasProperty($name))
		)
		{
			throw new \Error("NULL value");
		}
		
		if($this->hasProperty($name))
		{
			/*
			 * Do not use getProperty,
			 * we might get stuck in a loop if set_property is used in child
			 * class
			 */
			$get_type = $this->get_property_type($this->properties[$name]);
			if($get_type != 'NULL' && $set_type != $get_type)
			{
				throw new \Error(
					"Type mismatch: ". $set_type ." != ". $get_type
				);
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
	public function getDOM()
	{
		// Get all our properties
		$this->populate();
		
		// Clone it, only us should modify it
		$dom = clone $this->dom;
		// This looses the ID attributes
		// Since we won't bother with a DTD (we use XSD)
		// We need to do that manually
		foreach($dom->documentElement->childNodes as $node)
		{
			$node->setIdAttribute("name", true);
		}
		
		// no parent == no collection
		if(is_null($this->getParent()))
		{
			$dom->documentElement->removeChild(
				$dom->getElementById("@collection")
			);
		}
		
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
	private function dom_set_property(string $name, $value)
	{
		$node = $this->dom->getElementById($name);
		if(is_null($node))
		{
			return $this->dom_create_property($name, $value);
		}
		
		switch($this->get_property_type($value))
		{
			case "entity":
				$node->setAttribute("href", $value->getURI());
				break;
			default:
				$node->nodeValue = $value;
				break;
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
	private function dom_update()
	{
		foreach($this->dom->getElementsByTagName("link") as $node)
		{
			$target = null;
			$name = $node->getAttribute("name");
			switch($name)
			{
				case "@index":
					$target = $this->getAPI();
					break;
				case "@self":
					$target = $this;
					break;
				case "@collection":
					$target = $this->getParent();
					break;
				default:
					$target = $this->getProperty($name);
					break;
			}
			
			if(!is_null($target))
			{
				$node->setAttribute("href", $target->getURI());
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
	private function dom_create_property(string $name, $value)
	{
		$node = null;
		switch($this->get_property_type($value))
		{
			case "NULL":
			case "entity":
				$node = $this->dom->createElement("link");
				$rel = "item";
				if($name[0] === "@")
				{
					$rel = substr($name, 1);
				}
				$node->setAttribute("rel", $rel);
				if(!is_null($value))
				{
					$node->setAttribute("href", $value->getURI());
				}
				break;
			case "string":
				$node = $this->dom->createElement("string", $value);
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
			case 'NULL':
				break;
			default:
				throw new \Error("Invalid type: ". $type);
				break;
		}
		
		return $type;
	}
}
?>
