<?php

class Suco_View_Helper_Paginator
{
	protected $_recordCount;
	protected $_pageCount;
	protected $_pageSize;
	protected $_currentPage;
	
	public function __construct($recordCount, $pageSize, $currentPage)
	{
		$this->setRecordCount($recordCount);
		$this->setPageSize($pageSize);
		$this->setCurrentPage($currentPage);
	}
	
	
	public function __toString()
	{
		return <<<EOF
<span class="pagestatus">共 <strong> {$this->getRecordCount()} </strong> 条记录, 当前显示第 <strong> {$this->getCurrentPage()} / {$this->getPageCount()}</strong> 页</span>
{$this->firstPage('<<')} {$this->prevPage('< PREV')} {$this->nextPage('NEXT >')} {$this->lastPage('>>')}
EOF;
	}
	
	public function setRecordCount($recordCount)
	{
		$this->_recordCount = $recordCount;
	}
	
	public function getRecordCount()
	{
		return $this->_recordCount;	
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
		$this->_currentPage = $currentPage;
	}
	
	public function getCurrentPage()
	{
		return $this->_currentPage;	
	}

	public function setPageCount()
	{
		$this->_pageCount = $this->getRecordCount() / $this->getPageSize();
		if ($this->_pageCount > intval($this->_pageCount)) { //如果不能被整除,则取整加1
			$this->_pageCount = intval($this->_pageCount) + 1;
		}
	}
	
	public function getPageCount()
	{
		if (!$this->_pageCount) {
			$this->setPageCount();
		}
		return $this->_pageCount;	
	}
	
	public function prevPage($caption)
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
		return '<a href="'.$url.'" class="'.$class.'">'.$caption.'</a>';
	}
	
	public function nextPage($caption)
	{
		//如果当前页大于等于总页数,则禁用下一页按钮
		if ($this->getCurrentPage() >= $this->getPageCount()) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = $this->getCurrentPage()+1;
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="'.$class.'">'.$caption.'</a>';
	}
	
	public function firstPage($caption)
	{
		//如果当前页小于等于1,则禁用第一页按钮
		if ($this->getCurrentPage() <= 1) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = 1;
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="'.$class.'">'.$caption.'</a>';
	}
	
	public function lastPage($caption)
	{
		//如果当前页大于等于总页数,则禁用最后一页按钮
		if ($this->getCurrentPage() >= $this->getPageCount()) {
			$page = $this->getCurrentPage();
			$class = "disable";
		} else {
			$page = $this->getPageCount();
			$class = "enable";	
		}
		$url = $this->url($page);
		return '<a href="'.$url.'" class="'.$class.'">'.$caption.'</a>';
	}
	
	public function url($page)
	{
		$querys = Suco_Controller_Front::getInstance()->getRequest()->getQuerys();
		$querys['page'] = $page;

		return Suco_Controller_Front::getInstance()->getRouter()->reverse($querys);
	}

}