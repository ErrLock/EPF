<?php
/**
 * @file EntityRef.php
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

use EPF\StdClass\Friend;

/**
 * @brief 
 * @details 
 */
class EntityRef extends EntityBase
{
	use Friend;
	
	private $target = null;
	private $up = null;
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(EntityBase $target)
	{
		self::_friend_init();
		$this->target = $target;
		parent::__construct($target->getName());
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
	private static function _friend_config()
	{
		self::_friend(Entity::class);
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
		$result = $this->target->getDOM();
		$result->setProperty("@up", $this->getProperty("@up"));
		return $result;
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
		$result = $this->target->getProperties();
		$result["@up"] = $this->up;
		return $result;
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
		if($name == "@up")
		{
			return $this->up;
		}
		
		return $this->target->getProperty($name);
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
		if($name == "@up" && isset($this->up))
		{
			return $this->up;
		}
		
		return $this->target->hasProperty($name);
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
	protected function setProperty(string $name, $value)
	{
		throw new \Error("Entity references doesn't have properties");
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
	final protected function set_property(string $name, $value)
	{
		if($name != "@collection")
		{
			throw new \error("Trying to set ". $name ." on ". get_class($this));
		}
		if(isset($this->up))
		{
			throw new \Error("@up already set");
		}
		if(!is_a($value, parent::class))
		{
			throw new \Error("Invalid type");
		}
		if($value->getIndex() !== $this->target->getIndex())
		{
			throw new \Error("Different API");
		}
		
		$this->up = $value;
	}
}
?>
