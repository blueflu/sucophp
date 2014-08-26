<?php
/**
 * Suco_Helper_Head HTML页头
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Head implements Suco_Helper_Interface
{
	protected $_title = array();
	protected $_metas = array();
	protected $_links = array();
	protected $_capture = array();

	public static function callback($args)
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	public function setTitle($title, $locked = 0)
	{
		if ($locked) {
			$this->_title = array(strip_tags($title));
		} else {
			$this->_title[] = strip_tags($title);
		}
		return $this;
	}

	public function getTitle()
	{
		$this->_title = $this->_title;
		$title = @implode(' ', $this->_title);
		return $title;
	}

	public function addLink($files, $type = 'text/css') # text/javascript
	{
		if (!is_array($files)) {
			$files = array($files);
		}
		$files = array_reverse($files);

		foreach ($files as $file) {
			if (is_object($file)) { $file = (string)$file; }
			switch ($type) {
				case 'text/css':
					$this->_links['styles'][$file] = sprintf("<link href=\"%s\" rel=\"stylesheet\" type=\"text/css\" />\r\n", $file);
					break;
				default:
					$this->_links['scripts'][$file] = sprintf("<script type=\"%s\" src=\"%s\"></script>\r\n", $type, $file);
					break;
			}
		}

		return $this;
	}

	public function removeLink($file)
	{
		unset($this->_links[$file]);
		return $this;
	}

	public function getLinks()
	{
		return $this->_links;
	}

	public function addMeta($name = null, $content = null, $type = null)
	{
		$this->_metas[$name] = "<meta" . ($type ? " http-equiv=\"".$type."\"" : null)
									   . ($name ? " name=\"".$name."\"" : null)
									   . ($content ? " content=\"".$content."\"" : null) . " />\r\n";
		return $this;
	}

	public function removeMeta($name)
	{
		unset($this->_metas[$name]);
		return $this;
	}

	public function getMetas()
	{
		return $this->_metas;
	}

	public function captureStart()
	{
		ob_start();
		return $this;
	}

	public function captureEnd()
	{
		$this->_capture[] = ob_get_clean();
		return $this;
	}

	public function __toString()
	{
		$string = "<title>{$this->getTitle()}</title>\r\n";
		$string .= implode($this->getMetas());

		$links = $this->getLinks();
		$string .= isset($links['styles']) ? implode(array_reverse($links['styles'])) : null;
		$string .= isset($links['scripts']) ? implode(array_reverse($links['scripts'])) : null;

		if ($this->_capture) {
			foreach ($this->_capture as $content) {
				$string .= $content;
			}
		}

		return $string;
	}
}