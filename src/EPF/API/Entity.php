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
	private $root = null;
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
	public function __construct()
	{
		$this->dom = new \DomDocument();
		$this->dom->loadXML(
			'<?xml version="1.0" encoding="utf-8"?>
<entity>
</entity>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
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
	protected function set_name(string $name)
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
	protected function set_root(Server $root)
	{
		$this->root = $root;
		
		/*
		 * As soon as we have a root, we can start building our xml
		 */
		$root = $this->get_root();
		if($root === $this)
		{
			$this->add_link("self index", $this->get_name(), $this->get_uri());
		}
		else
		{
			$this->add_link("index", $root->get_name(), $root->get_uri());
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
	private function get_uri()
	{
		$root = $this->get_root();
		if($root === $this)
		{
			return "/";
		}
		
		$uri = $this->get_parent()->get_uri();
		if(strrpos($uri, '/') !== (strlen($uri) -1))
		{
			$uri .= "/";
		}
		$uri .= $this->get_name();
		
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
	private function add_link(string $rel, string $name, string $uri)
	{
		$node = $this->dom->createElement("link");
		$node->setAttribute("rel", $rel);
		$node->setAttribute("name", $name);
		$node->setAttribute("href", $uri);
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
	public function get_name()
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
	public function get_parent()
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
	public function get_root()
	{
		return $this->root;
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
	public function getChild(string $name)
	{
		if(!array_key_exists($name, $this->children))
		{
			throw new \Error("Invalid path");
		}
		
		return $this->children[$name];
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
	public function appendChild(Entity $child)
	{
		if($child->get_root() !== $this->get_root())
		{
			throw new \Error("Entities are not from the same API");
		}
		
		$name = $child->get_name();
		if(array_key_exists($name, $this->children))
		{
			throw new \Error("Child already exists: ". $name);
		}
		
		$child->set_parent($this);
		$this->children[$name] = $child;
		$this->add_link("item", $child->get_name(), $child->get_uri());
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
		
		// We have a parent, means we can get a link
		$this->add_link("self", $this->get_name(), $this->get_uri());
		$this->add_link("collection", $parent->get_name(), $parent->get_uri());
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
		return $this->dom->saveXML();
	}
}
?>
