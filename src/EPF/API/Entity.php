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
	public function __construct(string $name)
	{
		$this->set_name($name);
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
		if(isset($this->root))
		{
			throw new \Error("Root already set");
		}
		
		$this->root = $root;
		$this->init();
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
	protected function init()
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
	private function get_uri()
	{
		$root = $this->get_root();
		if($root === $this)
		{
			return "/";
		}
		
		$parent = $this->get_parent();
		$name = $this->get_name();
		
		if(is_null($parent))
		{
			return $name;
		}
		
		$uri = $this->get_parent()->get_uri();
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
	protected function get_dom()
	{
		if(!isset($this->dom))
		{
			$this->init_dom();
		}
		
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
	private function init_dom()
	{
		$this->dom = new \DomDocument();
		$this->dom->loadXML(
			'<?xml version="1.0" encoding="utf-8"?><entity />',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		
		$root = $this->get_root();
		if($root === $this)
		{
			$this->add_link("self index", $this->get_name(), $this->get_uri());
		}
		else
		{
			if(!is_null($root))
			{
				$this->add_link("index", $root->get_name(), $root->get_uri());
			}
			$this->add_link("self", $this->get_name(), $this->get_uri());
		}
		
		$parent = $this->get_parent();
		if(!is_null($parent))
		{
			$this->add_link("collection", $parent->get_name(), $parent->get_uri());
		}
		
		// populate
		$this->populate();
		
		foreach($this->children as $child)
		{
			$this->add_link("item", $child->get_name(), $child->get_uri());
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
		if(!$this->childExists($name))
		{
			throw new \Error("Invalid path: ". $name);
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
	public function childExists(string $name)
	{
		return array_key_exists($name, $this->children);
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
		$root = $this->get_root();
		if(is_null($root))
		{
			throw new \Error("No root");
		}
		
		if($child->get_root() !== $root)
		{
			throw new \Error("Entities are not from the same API");
		}
		
		$name = $child->get_name();
		if($this->childExists($name))
		{
			throw new \Error("Child already exists: ". $name);
		}
		
		$child->set_parent($this);
		$this->children[$name] = $child;
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
	protected function set_parent(Entity $parent)
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
		return $this->get_dom()->saveXML();
	}
}
?>
