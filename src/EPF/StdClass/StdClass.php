<?php
/**
 * @file StdClass.php
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
 * @brief Base class definition.
 * @details
 * This class should be inherited by all our classes.
 * It allows friendship.
 * @author Garinot Pierre <garinot.pierre@errlock.org>
 * @version 0.1
 */

namespace EPF;

/**
 * @brief Base class for all our classes.
 * @details
 * This class should be inherited by all our classes.
 * @par Using friendship:
 * @include friendship.php
 */
abstract class StdClass
{
	/**
	 * @brief Friendship cache
	 * @details
	 * Avoids repetitive use of \ReflectionClass
	 */
	private static $_friend_cache = array();
	
	/**
	 * @brief Call to inaccessible method
	 * @details
	 * Allows calling protected methods by friends
	 */
	public function __call(string $name, array $args)
	{
		$this->_friend_check('method', $name);
		
		return call_user_func_array(array($this, $name), $args);
	}
	
	/**
	 * @brief Getter for inaccessible properties
	 * @details
	 * Allows getting protected properties by friends
	 */
	public function __get(string $name)
	{
		$this->_friend_check('property', $name);
		
		return $this->$name;
	}
	
	/**
	 * @brief Setter for inaccessible properties
	 * @details
	 * Allows setting protected properties by friends
	 */
	public function __set(string $name, $value)
	{
		$this->_friend_check('property', $name);

		$this->$name = $value;
	}
	
	/**
	 * @brief Builds friendship cache
	 */
	private function _friend_build_cache(string $class)
	{
		self::$_friend_cache[$class] = array(
			'members' => array(),
			'friends' => array()
		);
		$ref = new \ReflectionClass($class);
		
		$this->_friend_cache_members($ref);
		$this->_friend_cache_friends($ref);
	}
	
	/**
	 * @brief Cache protected members
	 */
	private function _friend_cache_members(\ReflectionClass $ref)
	{
		$protected = array_merge(
			$ref->getProperties(\ReflectionProperty::IS_PROTECTED),
			$ref->getMethods(\ReflectionMethod::IS_PROTECTED)
		);
		
		foreach($protected as $member)
		{
			self::$_friend_cache[$ref->name]['members'][$member->name] =
				(is_a($member, 'ReflectionProperty') ? 'property' : 'method');
		}
	}
	
	/**
	 * @brief Cache friend classes
	 */
	private function _friend_cache_friends(\ReflectionClass $ref)
	{	
		/*
		 * We must use a property
		 * PHP 7.0 doesn't differentiate declaring class for constants
		 * 7.1 does:
		 * https://secure.php.net/manual/en/reflectionclass.getreflectionconstant.php
		 */
		if(!$ref->hasProperty('_FRIENDS'))
		{
			return;
		}
		
		$r_friends = $ref->getProperty('_FRIENDS');
		if(!$r_friends->getDeclaringClass() == $ref->name)
		{
			return;
		}
		
		$props = $ref->getDefaultProperties();
		if(!isset($props['_FRIENDS']))
		{
			throw new \Error('No default for \$_FRIENDS');
		}
		$friends = $props['_FRIENDS'];
		if(!is_array($friends))
		{
			throw new \Error("\$_FRIENDS is not an array");
		}
		
		foreach($friends as $f_class)
		{
			if(!is_string($f_class))
			{
				throw new \Error("\$_FRIENDS: value is not a string");
			}
			
			self::$_friend_cache[$ref->name]['friends'][$f_class] = true;
		}
	}
	
	/**
	 * @brief Check for friendship access
	 */
	private function _friend_check(string $type, string $name)
	{
		$class = get_class($this);
		
		$exists = $type .'_exists';
		if(!$exists($this, $name))
		{
			throw new \Error("Undefined ". $type .": ". $class ."::". $name);
		}
		
		if(!isset(self::$_friend_cache[$class]))
		{
			$this->_friend_build_cache($class);
		}
		
		if(!isset(self::$_friend_cache[$class]['members'][$name]))
		{
			// If it exists but is not in the cache, it is private
			// (we don't get called if it's public)
			throw new \Error(
				"Cannot access private property ". $class ."::". $name
			);
		}
		elseif(self::$_friend_cache[$class]['members'][$name] !== $type)
		{
			throw new \Error("Undefined ". $type .": ". $class ."::". $name);
		}
		
		$caller = debug_backtrace(null, 3)[2]['class'];
		if(
			empty($caller)
			|| !isset(self::$_friend_cache[$class]['friends'][$caller])
		)
		{
			// Not a friend
			throw new \Error(
				"Cannot access protected property ". $class ."::". $name
			);
		}
	}
}
?>
