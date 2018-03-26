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
		echo __METHOD__ ." for ". self::class ."\n";
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
		if(!isset(self::$_friend_cache[static::class]))
		{
			self::_friend_build_cache();
		}
	}
	
	private static function _friend_build_cache(
		array $list = null,
		string $type = null
	)
	{
		/*
		 * Build a cache of our protected members
		 * so that we don't have to use ReflectionClass every time we need to
		 * check friendship access
		 */
		if(is_null($list))
		{
			$ref = new \ReflectionClass(static::class);
			self::_friend_build_cache(
				$ref->getProperties(
					\ReflectionProperty::IS_PROTECTED |
					\ReflectionProperty::IS_PRIVATE
				),
				'property'
			);
			self::_friend_build_cache(
				$ref->getMethods(
					\ReflectionMethod::IS_PROTECTED |
					\ReflectionMethod::IS_PRIVATE
				),
				'method'
			);
			
			return;
		}
		
		foreach($list as $value)
		{
			$class = $value->class;
			if(!isset(self::$_friend_cache[$class]))
			{
				self::$_friend_cache[$class] = array(
					'property' => array(),
					'method' => array()
				);
			}
			$cache =& self::$_friend_cache[$class][$type];
			
			$name = $value->name;
			if(strpos($name, '_friend') === 0)
			{
				continue;
			}
			$access = ($value->isPrivate() ? 'private' : 'protected');
			if(!isset($cache[$name]))
			{
				$cache[$name] = array(
					'access' => $access,
					'auth' => is_a(self::class, $class, true)
				);
			}
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
	
	private static function _friend_check_access(string $type, string $name)
	{
		echo __METHOD__ ."(". $name .")\n";
		$class = static::class;
		
		$exists = $type .'_exists';
		if(!$exists($class, $name))
		{
			throw new \Error("Undefined ". $type .": ". $class ."::". $name);
		}
		
		do
		{
			if(!isset(self::$_friend_cache[$class]))
			{
				throw new \Error($class ." not in Friends cache");
			}
			
			$cache = self::$_friend_cache[$class];
			if(!isset($cache[$type][$name]))
			{
				$class = get_parent_class($class);
				continue;
			}
			
			$value = $cache[$type][$name];
			if($value['access'] == 'private')
			{
				throw new \Error(
					"Cannot access private ". $type ." ".
					$class ."::". $name
				);
			}
			if(!$value['auth'])
			{
				throw new \Error(
					"Cannot access protected ". $type ." ".
					$class ."::". $name
				);
			}
			
			$caller = debug_backtrace(null, 3)[2]['class'];
			if(empty($caller) || !isset(self::$_friend_friendships[$caller]))
			{
				// Not a friend
				throw new \Error(
					"Cannot access protected ". $type ." ".
					$class ."::". $name
				);
			}
			
			return true;
		}
		while($class);
		
		throw new \Error(
			$type ." ". static::class ."::". $name ." not found"
		);
		return false;
	}
	
	public function __get(string $name)
	{
		echo __METHOD__ ."(". $name .")\n";
		self::_friend_check_access('property', $name);
		return $this->$name;
	}
	
	public function __call(string $name, array $args)
	{
		echo __METHOD__ ."(". $name .")\n";
		self::_friend_check_access('method', $name);
		return call_user_func_array(array($this, $name), $args);
	}
}
?>
