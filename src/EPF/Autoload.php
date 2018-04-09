<?php
/**
 * @file Autoload.php
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
 * @brief Autoloader.
 * @details Allows registering an autoload for a namespace.
 * @author Garinot Pierre <garinot.pierre@errlock.org>
 * @version 0.1
 */

namespace EPF;

require_once(__DIR__ .'/StdClass/StdClass.php');

/**
 * @brief The Autoload class
 * @details Used to autoload classes
 * @remark
 * This class registers itself with the following parameters:
 * - namespace: ErrLock
 * - path: The directory where this file resides
 */
class Autoload extends StdClass
{
	/**
	 * Default suffixes for file lookups
	 */
	const DEFAULT_SUFFIXES = array(
		'.php',
	);
	
	private $namespace = null;	/**< Top namespace to use */
	private $path = null;		/**< Base path for this namespace */
	private $suffixes = array();	/**< Suffixes for file lookups */
	
	/**
	 * @brief Register ourself with php
	 * 
	 * @param[in] string namespace Namespace
	 * @param[in] string path Base path
	 * @param[in] array suffixes Suffixes
	 * 
	 * @retval Autoload The registered instance
	 */
	public static function register(
		string $namespace,
		string $path,
		array $suffixes = null
	)
	{
		if(is_null($suffixes))
		{
			$suffixes = self::DEFAULT_SUFFIXES;
		}
		
		$loader = self::find_loader($namespace, $path, $suffixes);
		if($loader !== null)
		{
			return $loader;
		}
		
		$me = self::class;
		return new $me($namespace, $path, $suffixes);
	}
	
	/**
	 * @brief Constructor
	 * 
	 * @param[in] string namespace Namespace
	 * @param[in] string path Base path
	 * @param[in] array suffixes Suffixes
	 */
	private function __construct(
		string $namespace,
		string $path,
		array $suffixes
	)
	{
		$this->namespace = $namespace;
		$this->path = realpath($path);
		$this->suffixes = $suffixes;
		
		$this->init_includes();
		spl_autoload_register(array($this, 'load'), true, true);
	}
	
	/**
	 * @brief Load a class
	 * 
	 * @param[in] string class The class to load (namespaced)
	 */
	public function load(string $class)
	{
		$path = $this->find_file($class);
		if(!empty($path))
		{
			require_once($path);
		}
	}
	
	/**
	 * @brief Get the namespace
	 * 
	 * @retval string namespace
	 */
	public function get_namespace()
	{
		return $this->namespace;
	}
	
	/**
	 * @brief Get the base path
	 * 
	 * @retval string path
	 */
	public function get_path()
	{
		return $this->path;
	}
	
	/**
	 * @brief Get the suffixes
	 * 
	 * @retval array suffixes
	 */
	public function get_suffixes()
	{
		return $this->suffixes;
	}
	
	/**
	 * @brief Check if we're already registered
	 * 
	 * @param[in] string namespace Namespace
	 * @param[in] string path Base path
	 * @param[in] array suffixes Suffixes
	 * 
	 * @retval Autoload Instance found
	 * @retval null Not found
	 */
	private static function find_loader(
		string $namespace,
		string $path,
		array $suffixes
	)
	{
		$spl_al = spl_autoload_functions();
		if(empty($spl_al))
		{
			return null;
		}
		
		foreach($spl_al as $al)
		{
			if(
				is_array($al)
				&& is_object($al[0])
				&& is_a($al[0], self::class)
			)
			{
				$al = $al[0];
				if(
					($al->get_namespace() == $namespace)
					&& ($al->get_path() == $path)
					&& ($al->get_suffixes() == $suffixes)
				)
				{
					return $al;
				}
			}
		}
		
		return null;
	}
	
	/**
	 * @brief Update include path
	 * @details
	 * Adds our base path to include_path so that we can also use include
	 * functions easily.
	 */
	private function init_includes()
	{
		$ipath = get_include_path();
		if(!in_array($this->path, explode(PATH_SEPARATOR, $ipath)))
		{
			set_include_path($this->path . PATH_SEPARATOR . $ipath);
		}
	}
	
	/**
	 * @brief Find a file corresponding to a class
	 * 
	 * @param[in] string class Class to find
	 * 
	 * @retval string Path found
	 * @retval false Path not found
	 */
	private function find_file(string $class)
	{
		if(strpos($class, $this->namespace) !== 0)
		{
			return false;
		}
		
		$class = substr($class, strlen($this->namespace));
		$prefix = $this->path . str_replace('\\', DIRECTORY_SEPARATOR, $class);
		
		foreach($this->suffixes as $suffix)
		{
			$path = $prefix . $suffix;
			if(is_file($path))
			{
				return $path;
			}
		}
		
		// Not found, try the class name
		$class = substr($class, strrpos($class, '\\') + 1);
		$path = $prefix ."/". $class .".php";
		if(is_file($path))
		{
			return $path;
		}
		
		return false;
	}
}

Autoload::register(__NAMESPACE__, __DIR__);
?>
