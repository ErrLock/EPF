<?php
/**
 * @file Document.php
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

namespace EPF\XML\DOM;

/**
 * @brief 
 * @details 
 */
class Document extends \DOMDocument
{
	private $media_type = 'application/xml';
	
	/**
	 * @brief 
	 * 
	 * @param[in] type name Desc
	 * 
	 * @exception type Desc
	 * 
	 * @retval type Desc
	 */
	public function __construct(string $media_type = null)
	{
		parent::__construct('1.0', 'utf-8');
		
		$this->formatOutput = true;
		$this->registerNodeClass('DOMElement', Element::class);
		
		if(isset($media_type))
		{
			$this->setMediaType($media_type);
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
	public function validate()
	{
		if(isset($this->doctype))
		{
			return parent::validate();
		}

		$xsi = $this->getXSI();
		if(!isset($xsi))
		{
			trigger_error("No DocumentType or XMLSchema found", E_USER_WARNING);
			return false;
		}
		
		return $this->schemaValidate($xsi);
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
	public function schemaValidate(
		$filename,
		int $flags = LIBXML_SCHEMA_CREATE
	)
	{
		
		return parent::schemaValidate($filename, $flags);
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
	private function getXSI()
	{
		$root = $this->documentElement;
		$xsi_prefix =
			$root->lookupPrefix("http://www.w3.org/2001/XMLSchema-instance");
		
		if(!isset($xsi_prefix))
		{
			return null;
		}
		
		$ns = $root->namespaceURI;
		if(!isset($ns))
		{
			$xsi =
				$root->getAttribute($xsi_prefix .":noNamespaceSchemaLocation");
			return $xsi;
		}

		$xsi_map = $root->getAttribute($xsi_prefix .":schemaLocation");
		if(!isset($xsi_map))
		{
			return null;
		}
		
		$xsi_map = explode(' ', $xsi_map);		
		for($i = 0; $i < count($xsi_map); $i +=2)
		{
			if($xsi_map[$i] == $ns)
			{
				$xsi = $xsi_map[$i + 1];
				return $xsi;
			}
		}
		
		return null;
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
	public function getMediaType()
	{
		return $this->media_type;
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
	protected function setMediaType(string $type)
	{
		$this->media_type = $type;
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
		return $this->saveXML();
	}
}
?>
