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
use EPF\XML\DOM\Document;

/**
 * @brief 
 * @details 
 */
class Entity extends Document
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
		parent::__construct();
		$this->loadXML(
'<?xml version="1.0" encoding="utf-8"?>'.
'<entity '.
	'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
	'xsi:schemaLocation="https://schemas.errlock.org/api/xml/entity '.
		'https://schemas.errlock.org/api/xml/entity.xsd" '.
	'xmlns="https://schemas.errlock.org/api/xml/entity" '.
'/>'
		);
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
	public function setProperty(string $name, $value)
	{
		$type = API\EntityBase::getPropertyType($value);
		$node = $this->getElementById($name);
		
		if(is_null($node))
		{
			$node = $this->create_property($type, $name);
		}
		elseif($type != $node->tagName)
		{
			throw new \Error("Type mismatch: ". $type ." != ". $node->tagName);
		}
		
		switch($type)
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
	private function create_property(string $type, string $name)
	{
		$node = $this->createElement($type);
		$node = $this->documentElement->appendChild($node);
		$node->setAttribute("name", $name);
		$node->setIdAttribute("name", true);
		
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
	public function validate()
	{
		// We always validate against our internal schema
		return $this->schemaValidate(__DIR__ .'/../XML/entity.xsd');
	}
}
?>
