<?php
/**
 * @file EntityBase.php
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
abstract class EntityBase
{
	private $name = null;
	private $collection = null;
	
	abstract public function getDOM();
	abstract public function getProperties();
	abstract public function getProperty(string $name);
	abstract public function hasProperty(string $name);
	abstract protected function setProperty(string $name, $value);
	
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
	public function getCollection()
	{
		return $this->collection;
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
	public function getIndex()
	{
		// @index is the topmost collection
		$index = $this;
		while(($col = $index->getCollection()) != null)
		{
			$index = $col;
		}
		
		if(!is_a($index, Server::class))
		{
			throw new \Error("Index is not a ". Server::class);
		}
		
		return $index;
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
	public function getURI()
	{
		$parent = $this->getCollection();
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
	public static function getPropertyType($value)
	{
		$type = gettype($value);
		$valid = false;
		switch($type)
		{
			case "object":
				$type = get_class($value);
				if(is_a($value, self::class))
				{
					$valid = true;
					$type = "entity";
				}
				elseif(is_a($value, Resource::class))
				{
					$valid = true;
					$type = "resource";
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
	protected function setCollection(EntityBase $value)
	{
		if(isset($this->collection))
		{
			throw new \Error("Collection already set");
		}
		
		$this->collection = $value;
	}
}
?>
