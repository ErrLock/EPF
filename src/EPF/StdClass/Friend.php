<?php
/**
 * @file Friend.php
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

namespace EPF\StdClass;

/**
 * @brief 
 * @details 
 */
trait Friend
{
	private static $_friend_friendships = null;
	private static $_friend_cache = null;
	
	public function __construct()
	{
		echo __METHOD__ ."\n";
		self::_friend_init();
	}
	
	private static function _friend_init()
	{
		echo __METHOD__ ."\n";
		// Good usage
		$caller = debug_backtrace(null, 2)[1];
		if(
			$caller['class'] != self::class
			|| $caller['function'] != '__construct'
		)
		{
			throw new \Error(
				__METHOD__ ." should only be called from ".
				self::class ."::__construct"
			);
		}
		
		// Initialize our friends
		if(is_null(self::$_friend_friendships))
		{
			self::$_friend_friendships = array();
			self::_friend_config();
		}
		
		// Initialize our (maybe derived) class
		self::_friend_build_cache();
	}
	
	private static function _friend_build_cache(string $class = null)
	{
		if(is_null($class))
		{
			$class = static::class;
		}
		
		if(isset(self::$_friend_cache[$class]))
		{
			return;
		}
		
		/*
		 * Build a cache of our protected members
		 * so that we don't have to use ReflectionClass every time we need to
		 * check friendship access
		 */
		self::$_friend_cache[$class] = array(
			'methods' => array(),
			'properties' => array()
		);
		$cache =& self::$_friend_cache[$class];
		$auth = ($class == self::class);
				
		$ref = new \ReflectionClass(static::class);
		foreach($ref->getMethods(\ReflectionMethod::IS_PROTECTED) as $value)
		{
			$v_class = $value->class;
			if($v_class != $class)
			{
				self::_friend_build_cache($v_class);
				continue;
			}
			
			$v_name = $value->name;
			$cache['methods'][$v_name] = $auth;
		}
		foreach($ref->getProperties(\ReflectionProperty::IS_PROTECTED) as $value)
		{
			$v_class = $value->class;
			if($v_class != $class)
			{
				self::_friend_build_cache($v_class);
				continue;
			}
			
			$v_name = $value->name;
			$cache['properties'][$v_name] = $auth;
		}
	}
	
	private static function _friend_config()
	{
		echo __METHOD__ ."\n";
	}
	
	private static function _friend(string $class)
	{
		echo __METHOD__ ."(". $class .")\n";
		/*
		 * Good usage
		 * Debug backtrace is not that costly here
		 * since we should only be called once
		 */
		$caller = debug_backtrace(null, 2)[1];
		if(
			$caller['class'] != self::class
			|| $caller['function'] != '_friend_config'
		)
		{
			throw new \Error(
				__METHOD__ ." should only be called from ".
				self::class ."::_friend_config"
			);
		}
		
		if(isset(self::$_friend_friendships[$class]))
		{
			trigger_error(
				$class ." is already a friend of ". self::class,
				E_USER_WARNING
			);
			return;
		}
		
		self::$_friend_friendships[$class] = true;
	}
	
	public function __get(string $name)
	{
		echo "GET ". $name ."\n";
		$class = static::class;
		
		if(!property_exists($class, $name))
		{
			return null;
		}
		
		$cache = self::$_friend_cache[$class];
		if(!isset($cache['properties'][$name]))
		{
			// First check the property is protected, avoids a costly call to
			// debug_backtrace
			throw new \Error(
				"Cannot access private property ". $class ."::\$". $name
			);
		}
		elseif($class != self::class)
		{
			// protected, but not ours
			throw new \Error(
				"Cannot access protected property ". $class ."::\$". $name
			);
		}
		
		$caller = debug_backtrace(null, 2)[1]['class'];
		if(empty($caller) || !isset(self::$_friend_friendships[$caller]))
		{
			// Not a friend
			throw new \Error(
				"Cannot access protected property ". self::class ."::\$". $name
			);
		}
		
		return $this->$name;
	}
}
?>
