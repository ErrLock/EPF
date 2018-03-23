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

namespace EPF\API\DOM;

use EPF\API;

/**
 * @brief 
 * @details 
 */
class Entity extends \DOMDocument
{
	private $api_entity = null;
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(API\Entity $api_entity)
	{
		parent::__construct();
		$this->formatOutput = true;
		$this->loadXML('<?xml version="1.0" encoding="utf-8"?><entity />');
		$this->api_entity = $api_entity;
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
	public function __clone()
	{
		// Cloning looses the ID attributes
		// Since we won't bother with a DTD (we use XSD)
		// We need to do that manually
		foreach($this->documentElement->childNodes as $node)
		{
			$node->setIdAttribute("name", true);
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
	private function update()
	{
		foreach($this->api_entity->getProperties() as $name => $value)
		{
			$this->setProperty($name, $value);
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
	public function setProperty(string $name, $value)
	{
		$type = API\Entity::getPropertyType($value);
		$node = $this->getElementById($name);
		
		if(is_null($node))
		{
			$node = $this->create_property($type, $name);
		}
		elseif($type != $node->tagName)
		{
			throw new \Error("Type mismatch: ". $type ." != ". $node->tagName);
		}
		
		$this->update_property($node, $value);
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
	private function update_property(\DOMElement $node, $value)
	{
		switch($node->tagName)
		{
			case "link":
				$this->update_link($node, $value->getURI());
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
	private function update_link(\DOMElement $node, string $uri)
	{
		if($node->getAttribute("href") != $uri)
		{
			$node->setAttribute("href", $uri);
			switch($node->getAttribute("name"))
			{
				case '@index':
				case '@collection':
					/*
					 * Call update if we've been changed
					 * This also avoid going into a loop when update calls
					 * setProperty:
					 * We've already been changed, so we won't call update again
					 */
					$this->update();
					break;
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
	private function create_property(string $type, string $name)
	{
		$node = null;
		switch($type)
		{
			case "link":
				$node = $this->createElement("link");
				$rel = "item";
				if($name[0] === "@")
				{
					$rel = substr($name, 1);
				}
				$node->setAttribute("rel", $rel);
				break;
			default:
				$node = $this->createElement($type, "");
				break;
		}
		
		$node->setAttribute("name", $name);
		$node->setIdAttribute("name", true);
		
		$this->documentElement->appendChild($node);
		
		return $node;
	}
}
?>
