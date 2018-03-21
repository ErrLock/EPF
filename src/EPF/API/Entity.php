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
	private $id = null;
	private $parent = null;
	private $children = array(); /**< Desc */
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
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
	public function get_id()
	{
		return $this->id;
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
	public function GET(string $path)
	{
		$path = explode('/', $path);
		$id = array_shift($path);
				
		if(empty($id))
		{
			return $this;
		}
		
		if(!array_key_exists($id, $this->children))
		{
			throw new \Error("Invalid path");
		}
		
		$value = $this->children[$id];
		if(!is_a($value, 'EPF\API\Entity'))
		{
			throw new \Error("Invalid path");
		}
		
		return $value->GET($path);
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
	public function append(Entity $child)
	{
		$id = $child->get_id();
		if(array_key_exists($id, $this->children))
		{
			throw new \Error("Child already exists: ". $id);
		}
		
		$child->set_parent($this);
		$this->children[$id] = $child;
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
	public function __toString()
	{
		if(!isset($this->dom))
		{
			$this->make_dom();
		}
		
		return $this->dom->saveXML();
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
	private function make_dom()
	{
		$this->dom = new \DomDocument();
		$this->dom->loadXML(
			'<?xml version="1.0" encoding="utf-8"?>
<entity>
</entity>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		
		foreach($this->children as $id => $child)
		{
			$type = gettype($child);
			if($type == 'object')
			{
				$type = get_class($child);
				if(is_a($child, 'EPF\API\Entity'))
				{
					$type = "entity";
				}
			}
			
			$node = null;
			switch($type)
			{
				case 'entity':
					$node = $this->dom->createElement('link');
					$node->setAttribute("rel", "item");
					break;
				default:
					throw new \Error("Invalid type: ". $type);
					break;
			}
			
			$node->setAttribute("name", $id);
			$node->setIdAttribute("name", true);
			
			$this->dom->documentElement->appendChild($node);
		}
	}
}
?>
