<?php
/**
 * Suco_Helper_Paginator 分页条
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Paginator implements Suco_Helper_Interface
{
	protected $_totalRecord;
	protected $_totalPage;
	protected $_pageSize;
	protected $_currentPage;
	protected $_maxPage;
	protected $_ajaxFunc;

	public $nextPageCaption = 'Next »';
	public $prevPageCaption = '«';
	public $firstPageCaption = '';
	public $lastPageCaption = '';
	public $pageNumberLength = 5;

	public static function callback($args)
	{
		static $instance = array();
		$mark = serialize($args);
		if (!isset($instance[$mark])) {
			$instance[$mark] = new self($args[0], $args[1], $args[2], $args[3]);
		}
		return $instance[$mark];
	}

	public function __construct($totalRecord, $pageSize, $currentPage, $maxPage = 0, $ajaxFunc = null)
	{
		if ($totalRecord instanceof Suco_Db_Table_Rowset) {
			$select = $totalRecord->getSelect();
			$totalRecord = $totalRecord->getTotal();
			$pageSize = $select->getPart(Suco_Db_Select::LIMIT_OFFSET);
			$currentPage = @ceil($select->getPart(Suco_Db_Select::LIMIT_COUNT) / $pageSize) + 1;
		}

		$this->setAjaxFunc($ajaxFunc);
		$this->setTotalRecord($totalRecord);
		$this->setPageSize($pageSize);
		$this->setCurrentPage($currentPage);
		$this->setMaxPage($maxPage);
	}

	public function __toString()
	{
		return $this->getFullbar();
	}

	public function getAjaxBar($func)
	{
		$this->setAjaxFunc($func);
		return $this;
	}

	public function getMiniBar()
	{
		return <<<EOF
<span class="pagestatus"> {$this->getCurrentPage()} / {$this->getTotalPage()}</span>
{$this->prevPage()} {$this->nextPage()}
EOF;
	}

	public function getFullBar()
	{
		return <<<EOF
<form method="get"><span class="pagestatus">Page: <strong> {$this->getCurrentPage()}</strong> Of <strong>{$this->getTotalPage()}</strong></span>
{$this->prevPage($this->prevPageCaption)} {$this->firstPage()} {$this->pageNumber()} {$this->lastPage()} {$this->nextPage($this->nextPageCaption)} {$this->getInputBox()}</form>
EOF;
	}

	public function getInputBox()
	{
		return '<span class="gotopage">Go to Page <input type="text" name="page" value="'.$this->getCurrentPage().'" size="3" /><button type="submit">GO</button></span>';
	}

	public function setAjaxFunc($ajaxFunc)
	{
		$this->_ajaxFunc = $ajaxFunc;
	}

	public function getAjaxFunc()
	{
		return $this->_ajaxFunc;
	}

	public function setMaxPage($maxPage)
	{
		$this->_maxPage = $maxPage;
	}

	public function getMaxPage()
	{
		return $this->_maxPage;
	}

	public function setTotalRecord($totalRecord)
	{
		$this->_totalRecord = $totalRecord;
	}

	public function getTotalRecord()
	{
		return $this->_totalRecord;
	}

	public function setPageSize($pageSize)
	{
		$this->_pageSize = $pageSize;
	}

	public function getPageSize()
	{
		return $this->_pageSize;
	}

	public function setCurrentPage($currentPage)
	{
		$this->_currentPage = $currentPage ? $currentPage : 1;
	}

	public function getCurrentPage()
	{
		return $this->_currentPage;
	}

	public function setTotalPage()
	{
		$this->_totalPage = @ceil($this->getTotalRecord() / $this->getPageSize());
		if ($this->_maxPage && $this->_totalPage > $this->_maxPage) {
			$this->_totalPage = $this->_maxPage;
		}
	}

	public function getTotalPage()
	{
		if (!$this->_totalPage) {
			$this->setTotalPage();
		}
		return $this->_totalPage ? $this->_totalPage : 1;
	}

	/**
	 * 构造页码
	 * @return string
	 */
	function pageNumber($length = null)
	{
		if ($length) {
			$this->pageNumberLength = $length;
		} else {
			$length = $this->pageNumberLength;
		}

		$str = null;
		for ($i = $this->getCurrentPage() - $length; $i < $this->getCurrentPage() + $length + 1; $i++) {
			if ($i < 1 || $i > $this->getTotalPage()) continue;
			$str .= '<a '.($this->getCurrentPage() == $i ? ' class="current"' : 'href="'.$this->go($i).'"').'>'.$i.'</a> ';
		}

		return $str;
	}

	public function prevPage($caption = null)
	{
		return '<a href="'.$this->go('prev').'" '.($this->getCurrentPage() > 1 ? '' : 'class="disable"').'>'.($caption ? $caption : $this->prevPageCaption).'</a>';
	}

	public function nextPage($caption = null)
	{
		return '<a href="'.$this->go('next').'" '.($this->getCurrentPage() < $this->getTotalPage() ? '' : 'class="disable"').'>'.($caption ? $caption : $this->nextPageCaption).'</a>';
	}

	public function firstPage($caption = '')
	{
		if ((!$caption && $this->getCurrentPage() - $this->pageNumberLength - 1 < 1)) {
			return;
		}
		return '<a href="'.$this->go('first').'">'.($caption ? $caption : 1).'</a>' . ($caption ? '' : '..');
	}

	public function lastPage($caption = '')
	{
		if ((!$caption && $this->_currentPage > $this->getTotalPage() - $this->pageNumberLength - 1)) {
			return;
		}
		return ($caption ? '' : '..') . '<a href="'.$this->go('last').'">'.($caption ? $caption : $this->_totalPage).'</a>';
	}

	public function go($page)
	{
		switch ($page) {
			case 'first': $page = 1; break;
			case 'last': $page = $this->getTotalPage(); break;
			case 'next': $page = $this->getCurrentPage() >= $this->getTotalPage() ? $this->getCurrentPage() : $this->getCurrentPage()+1;
				break;
			case 'prev':
				$page = $this->getCurrentPage() <= 1 ? $this->getCurrentPage() : $this->getCurrentPage()-1;
				break;
		}

		if ($this->getAjaxFunc()) {
			return 'javascript:'.$this->getAjaxFunc().'('.$page.')"';
		} else {
			return Suco_Application::instance()->getRouter()
				->reverse('&page=' . ($page > 1 ? $page : ''));
		}
	}
}