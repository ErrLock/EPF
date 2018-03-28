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
	private $uri = "/";
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(string $uri)
	{
		$this->setURI($uri);
		
		parent::__construct("index");
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
	private function setURI(string $uri)
	{
		$p_uri = parse_url($uri);
		if(
			$p_uri == false
			|| !isset($p_uri['scheme'])
			|| !isset($p_uri['host'])
			|| ($p_uri['scheme'] != "http" && $p_uri['scheme'] != "https")
		)
		{
			throw new \Error("Malformed URI");
		}
		if($p_uri['scheme'] == "https" && !isset($_SERVER['HTTPS']))
		{
			throw new \Error("API should be accessed using https");
		}
		
		$path = '/';
		if(!isset($p_uri['path']))
		{
			$uri .= '/';
		}
		else
		{
			$path = $p_uri['path'];
		}
		
		if(
			$p_uri['host'] != $_SERVER['SERVER_NAME']
			|| (
				isset($p_uri['port'])
				&& $p_uri['port'] != $_SERVER['SERVER_PORT']
			)
			|| dirname($_SERVER['SCRIPT_NAME']) != $path
		)
		{
			throw new \Error("API URI mismatch");
		}
		
		$this->uri = $uri;
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
	private function parse_media_type(string $media)
	{
		$result = array(
			'type' => 'application',
			'subtype' => array(
				'tree' => 'standard',
				'name' => 'xml',
				'syntax' => 'xml'
			)
		);
		
		$media = explode('/', $media);
		$result['type'] = $media[0];
		
		$subtype = explode('+', $media[1], 2);
		if(count($subtype) == 1)
		{
			$result['subtype']['name'] = $result['subtype']['syntax']
				= $subtype[0];
			return $result;
		}
		$result['subtype']['syntax'] = $subtype[1];
		
		$tree = explode('.', $subtype[0], 2);
		if(count($tree) == 1)
		{
			$result['subtype']['name'] = $tree[0];
			return $result;
		}
		$result['subtype']['tree'] = $tree[0];
		$result['subtype']['name'] = $tree[1];
		
		return $result;
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
	private function get_xsl(string $media)
	{
		switch($media)
		{
			case 'application/xml':
				$media = 'application/vnd.errlock.api+entity+xml';
				break;
		}
		
		if($media == 'application/vnd.errlock.api+entity+xml')
		{
			return null;
		}
		
		$p_media = $this->parse_media_type($media);
		var_dump($p_media);
		throw new \Error("Unknow media type: ". $media);
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
	public function GET(string $path = '/')
	{
		if(strpos($path, '/') !== 0)
		{
			throw new Error("Path must be absolute");
		}
		if(substr($path , -1) == '/')
		{
			$path = substr($path, 0, -1);
		}
		
		$path = substr($path, 1);
		$result = $this;
		if(!empty($path))
		{
			$path = explode('/', $path);
			foreach($path as $name)
			{
				$result = $result->getProperty($name);
				if(!is_a($result, EntityBase::class))
				{
					throw new \Error($name ." is not an entity");
				}
			}
		}
		
		return $result;
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
	public function send(Entity $data, string $media = null)
	{
		if(is_null($media))
		{
			$media = 'application/xml';
		}
		
		$dom = $this->transform($data, $media);
		
		header(
			'Content-Type: '. $media .
			'; charset='. $dom->encoding
		);
		echo $dom;
		exit;
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
	private function get_request_path()
	{
		$path = $_SERVER['REQUEST_URI'];
		if(isset($_SERVER['QUERY_STRING']))
		{
			$len = strlen($_SERVER['QUERY_STRING']) + 1;
			$path = substr($path, 0, -$len);
		}
		
		$pos = strpos($path, $_SERVER['SCRIPT_NAME']);
		if($pos !== false)
		{
			$len = strlen($_SERVER['SCRIPT_NAME']);
			$path = substr($path, $len);
		}
		
		if(empty($path))
		{
			$path = '/';
		}
		
		return $path;
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
	public function run(
		string $method = null,
		string $path = null,
		string $media = null
	)
	{
		if(is_null($method))
		{
			$method = $_SERVER['REQUEST_METHOD'];
		}
		if(is_null($path))
		{
			$path = $this->get_request_path();
		}
		
		$result = null;
		switch($method)
		{
			case 'GET':
				$result = $this->GET($path);
				break;
			default:
				throw new \Error("Invalid method: ". $method);
				break;
		}
		
		$this->send($result, $media);
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
	public function transform(Entity $data, string $media)
	{
		$dom = $data->getDom();
		$xsl = $this->get_xsl($media);
		
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
	public function getURI()
	{
		return $this->uri;
	}
}
?>
