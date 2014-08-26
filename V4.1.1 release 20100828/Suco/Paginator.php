<?php

class Suco_Paginator
{
	protected $_totalRecord;
	protected $_totalPage;
	protected $_pageSize;
	protected $_currentPage;
	protected $_maxPage;
	
	public $nextPageCaption = '>';
	public $prevPageCaption = '<';
	public $firstPageCaption = '';
	public $lastPageCaption = '';
	
	public function __construct($totalRecord, $pageSize, $currentPage, $maxPage = 0)
	{
		$this->setTotalRecord($totalRecord);
		$this->setPageSize($pageSize);
		$this->setCurrentPage($currentPage);
		$this->setMaxPage($maxPage);
	}
	
	
	public function __toString()
	{
		return <<<EOF
<form method="get"><span class="pagestatus">Page: <strong> {$this->getCurrentPage()} - {$this->getTotalPage()}</strong> Of <strong> {$this->getTotalRecord()} </strong></span>
{$this->prevPage($this->prevPageCaption)} {$this->firstPage()} {$this->pageNumber(5)} {$this->lastPage()} {$this->nextPage($this->nextPageCaption)} {$this->getInputBox()}</form>
EOF;
	}
	
	public function getInputBox()
	{
		return '<span class="input_box" style="margin-left:50px;">Go to Page <input type="text" name="page" value="'.$this->getCurrentPage().'" style="width:30px; height:13px; padding:2px; text-align:center" /><input type="submit" value="GO" style="height:19px"></span>';
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
		$this->_totalPage = ceil($this->getTotalRecord() / $this->getPageSize());
		if ($this->_maxPage && $this->_totalPage > $this->_maxPage) {
			$this->_totalPage = $this->_maxPage;
		}
	}
	
	public function getTotalPage()
	{
		if (!$this->_totalPage) {
			$this->setTotalPage();
		}
		return $this->_totalPage;	
	}
	
	/*
	 * 构造页码
	 * @return string
	 */
	function pageNumber($length)
	{
		$str = null;
		for ($i = $this->getCurrentPage() - $length; $i < $this->getCurrentPage() + $length; $i++) {
			if ($i < 1 || $i > $this->getTotalPage()) continue;
			$str .= '<a href="' . $this->url($i). '" class="page_number ' . ($this->getCurrentPage() == $i ? 'current' : '') . '">' . $i . '</a>';
		}
		return $str;
	}
		
	public function prevPage($caption = null)
	{
		//如果当前页小于等于1,则禁用上一页按钮
		if ($this->getCurrentPage() <= 1) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = $this->getCurrentPage()-1;
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="prev_page '.$class.'">'.$caption.'</a>';
	}
	
	public function nextPage($caption)
	{
		//如果当前页大于等于总页数,则禁用下一页按钮
		if ($this->getCurrentPage() >= $this->getTotalPage()) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = $this->getCurrentPage()+1;
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="next_page '.$class.'">'.$caption.'</a>';
	}
	
	public function firstPage($caption = '')
	{
		if ((!$caption && $this->_currentPage < 7) || $this->_totalPage < 2) {
			return;	
		}
		//如果当前页小于等于1,则禁用第一页按钮
		if ($this->getCurrentPage() <= 1) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = 1;
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="first_page '.$class.'">'.($caption ? $caption : 1).'</a>' . ($caption ? '' : ' .. ');
	}
	
	public function lastPage($caption = '')
	{
		if ((!$caption && $this->_currentPage > $this->getTotalPage() - 5) || $this->_totalPage < 2) {
			return;	
		}
		//如果当前页大于等于总页数,则禁用最后一页按钮
		if ($this->getCurrentPage() >= $this->getTotalPage()) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = $this->getTotalPage();
			$class = "enable";	
		}
		$url = $this->url($page);
		return ($caption ? '' : ' .. ') . '<a href="'.$url.'" class="last_page '.$class.'">'.($caption ? $caption : $this->_totalPage).'</a>';
	}
	
	public function url($page)
	{
		return Suco_Application::getInstance()->getRouter()
												   ->reverse('&page=' . $page);
	}

}