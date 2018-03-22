<?php
/**
 * @file Server.php
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
class Server extends Entity
{
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct()
	{
		parent::__construct("index");
		$this->set_root($this);
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
	public function GET(string $path = '/', string $media = 'application/xml')
	{
		if($media != 'application/xml')
		{
			throw new \Error("Unknow media type: ". $media);
		}
	
		if(strpos($path, '/') !== 0)
		{
			throw new Error("Path must be absolute");
		}
		
		$path = substr($path, 1);
		$result = $this;
		if(!empty($path))
		{
			$path = explode('/', $path);
			foreach($path as $name)
			{
				$result = $result->getChild($name);
			}
		}
		
		$dom = $result->get_dom();
		
		/*
		 * We can modify the dom here
		 * 
		 * > Note: this is a clone of the Entity::$dom
		 */
		
		return $dom;
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
	public function send(string $path, string $media)
	{
		$entity = $this->GET($path, $media);
		
		header('Content-Type: '. $media);
		echo $entity->saveXML();
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
	public function createEntity(string $name, string $class = null)
	{
		if(is_null($class))
		{
			$class = "EPF\API\Entity";
		}
		elseif(!is_subclass_of($class, "EPF\API\Entity"))
		{
			throw new \Error($class ." is not an Entity");
		}
		
		$entity = new $class($name);
		$entity->set_root($this);
		
		return $entity;
	}
}
?>
