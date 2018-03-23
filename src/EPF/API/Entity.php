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
		
		$this->dom = new DOM\Entity($this);
		$this->set_property("@self", $this);
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
				$allowed = ($args[0][0] != "@" || $caller == 'EPF\API\Server');
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
	public function getProperties()
	{
		return $this->properties;
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
	private function set_property(string $name, $value)
	{
		$this->set_property_check($name, $value);
		
		if(is_a($value, self::class) && $name[0] != "@")
		{
			$value->set_property("@collection", $this);
		}
		
		$this->properties[$name] = $value;
		$this->dom->setProperty($name, $value);
		
		switch($name)
		{
			case "@index":
				foreach($this->properties as $p_name => $prop)
				{
					if($p_name[0] != "@" && is_a($prop, self::class))
					{
						$prop->set_property($name, $value);
					}
				}
				break;
			case "@collection":
				$api = $value->getProperty("@index");
				if(!is_null($api))
				{
					$this->set_property("@index", $api);
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
	public static function getPropertyType($value)
	{
		$type = gettype($value);
		$valid = false;
		switch($type)
		{
			case "object":
				if(is_a($value, 'EPF\API\Entity'))
				{
					$valid = true;
					$type = "link";
				}
				break;
			case "double":
				$type = "float";
			case "boolean":
			case "integer":
			case "string":
				$valid = true;
				break;
		}
		
		if(!$valid)
		{
			throw new \Error("Invalid type: ". $type);
		}
		
		return $type;
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
		$set_type = self::getPropertyType($value);
		if($this->hasProperty($name))
		{
			switch($name)
			{
				case "@index":
				case "@collection":
					throw new \Error("Property ". $name ." already set");
					break;
			}
			/*
			 * Do not use getProperty,
			 * we might get stuck in a loop if set_property is used in child
			 * class
			 */
			$get_type = self::getPropertyType($this->properties[$name]);
			if($set_type != $get_type)
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
		$parent = $this->getProperty("@collection");
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
}
?>
