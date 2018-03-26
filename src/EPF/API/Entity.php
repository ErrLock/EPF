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
class Entity extends EntityBase
{
	private $dom = null;
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
		$this->dom = new DOM\Entity($this);
		parent::__construct($name);
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
	public function getProperty(string $name)
	{
		if(!$this->hasProperty($name))
		{
			switch($name)
			{
				case "@index":
					return $this->getIndex();
					break;
				case "@up":
					return $this->getCollection();
					break;
				default:
					return null;
					break;
			}
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
	final protected function setProperty(string $name, $value)
	{
		if($name[0] == "@")
		{
			throw new \Error("Cannot set special properties");
		}
		
		return $this->set_property($name, $value);
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
		$type = $this->set_property_check($name, $value);
		
		if($type == 'link' && $name[0] != "@")
		{
			$value->set_property("@collection", $this);
		}
		
		$this->properties[$name] = $value;
		$this->dom->setProperty($name, $value);
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
			if($name[0] == "@")
			{
				throw new \Error("Property ". $name ." already set");
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
		
		return $set_type;
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
		
		$dom->setProperty("@index", $this->getIndex());
		
		$up = $this->getCollection();
		if(isset($up))
		{
			$dom->setProperty("@up", $up);
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
	public function hasProperty(string $name)
	{
		return array_key_exists($name, $this->properties);
	}
}
?>
