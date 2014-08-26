<?php
/*
 * SCN_Pagination #2008/6/12 16:44:59#
 * 用于辅助记录分页显示
 *
 * @version				1.0
 * @package				Helper
 * @author				blueflu (lqhuanle@163.com)
 * @copyright			Copyright (c) 2008, Suconet, Inc.
 * @license				http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example:
 	# 分页条样式说明
	# .current 为当前分页的样式
	# .disable 为禁用的按钮样式.当前页码超出总页数时下一页 最后一页等按键会引用此样式
	
	#样式例子:
  	echo "
	<style>
		* { font-size:12px; font-family:Arial, Helvetica, sans-serif; }
		.pagination .disable { color:#CCCCCC; }
		.pagination a { background:#f0f0f0; text-decoration:none; color:#666666; padding:2px 4px; border:solid 1px #ccc; margin:2px; }
		.pagination a.current { background:#fff; border:none; font-weight:bold; color:#CC0000; }
	</style>";
	
	$page = new SCN_Pagination(1000, 25, $_GET['pn']);
	$page->pageKey = 'pn';					#此设置缺省时为:page
	$page->prevCaption = '上一页';			#此设置缺省时为:< Prev
	$page->nextCaption = '下一页';			#此设置缺省时为:Next >
	$page->firstCaption = '第一页';			#此设置缺省时为:<<
	$page->lastCaption = '最后一页';		#此设置缺省时为:>>
	$page->statusStr = '共%d篇文章, 每页显示%d篇, 当前显示第%d-%d篇';
	
	echo $page->outputPageBar(); //输出整个分页条.
	#也可以只输出一部份分页控制按键如: echo $page->nextPage(); echo $page->prevPage();
 */

class SCN_Pagination
{
	//记录总数
	var $recordCount;
	//每页显示记录数
	var $pageSize;
	//总页数
	var $pageCount;
	
	//URL传递键值
	var $pageKey = 'page';
	//当前页码
	var $currentPage = 1;
	//循环页码长度
	var $pageNumberLength = 5;
	
	//上一页标签
	var $prevCaption = '< Prev';
	//下一页标签
	var $nextCaption = 'Next >';
	
	//第一页标签
	var $firstCaption = '<<';
	//最后一页标签
	var $lastCaption = '>>';
	
	//AJAX分类
	var $ajaxFunc = 0;
	
	//URI 类扩展
	var $uriClass = 0;
	
	//分页状态文字
	var $statusStr = '共<strong>%d</strong>条记录, 当前显示第<strong>%d-%d</strong>条';

	/*
	 * 构造函数 
	 * @params object $uri //传入URI处理类
	 * @params int $recordCount //记录总数
	 * @params int $pageSize //每页显示记录数
	 * @params int $currentPage //当前页码
	 */

	function SCN_Pagination()
	{
	}
	
	/*
	 * 设置记录总数 
	 * @params int $recordCount //记录总数
	 */
	
	function setRecordCount($recordCount)
	{
		$this->recordCount = $recordCount;
	}
	
	/*
	 * 设置每页显示记录
	 * @params int $pageSize //每页显示记录数
	 */
	
	function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
	}
	
	/*
	 * 设置当前页码
	 * @params int $currentPage //当前页码
	 */
	
	function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage ? $currentPage : 1;
	}
	
	/*
	 * 输出分页条 
	 * @return string
	 */
	function outputPageBar()
	{		
		$this->pageCount();
		return $this->pageStatus() . 
				$this->firstPage() . $this->prevPage() . 
				$this->pageNumber() . 
				$this->nextPage() . $this->lastPage();
	}
	
	/*
	 * 统计总页数 
	 */
	function pageCount()
	{
		$this->pageCount = $this->recordCount/$this->pageSize;
		$this->pageCount = $this->pageCount > intval($this->pageCount) ? intval($this->pageCount) + 1 : intval($this->pageCount);
	}
	
	/*
	 * 构造上一页链接 
	 * @return string
	 */
	function nextPage($caption = NULL)
	{
		if ($this->currentPage < $this->pageCount) {
			return '<a href="' . $this->makeUrl($this->currentPage+1) . '">' . ($caption ? $caption : $this->nextCaption) . '</a>';
		} else {
			return '<a class="disable">' . ($caption ? $caption : $this->nextCaption) . '</a>';
		}
	}
	
	/*
	 * 构造下一页链接 
	 * @return string
	 */
	function prevPage($caption = NULL)
	{
		if ($this->currentPage > 1) {
			return '<a href="' . $this->makeUrl($this->currentPage-1) . '">' . ($caption ? $caption : $this->prevCaption) . '</a>';
		} else {
			return '<a class="disable">' . ($caption ? $caption : $this->prevCaption) . '</a>';
		}
	}
	
	/*
	 * 构造首页链接 
	 * @return string
	 */
	function firstPage($caption = NULL)
	{
		if ($this->currentPage > 1) {
			return '<a href="' . $this->makeUrl(1) . '">' . ($caption ? $caption : $this->firstCaption) . '</a>';
		} else {
			return '<a class="disable">' . ($caption ? $caption : $this->firstCaption) . '</a>';
		}
	}
	
	/*
	 * 构造尾页链接 
	 * @return string
	 */	
	function lastPage($caption = NULL)
	{
		if ($this->currentPage < $this->pageCount) {
			return '<a href="' . $this->makeUrl($this->pageCount) . '">' . ($caption ? $caption : $this->lastCaption) . '</a>';
		} else {
			return '<a class="disable">' . ($caption ? $caption : $this->lastCaption) . '</a>';
		}
	}

	/*
	 * 构造页码
	 * @return string
	 */
	function pageNumber()
	{
		for ($i = $this->currentPage - $this->pageNumberLength; $i < $this->currentPage + $this->pageNumberLength; $i++) {
			if ($i < 1 || $i > $this->pageCount) continue;
			$str .= '<a href="' . $this->makeUrl($i). '"' . ($this->currentPage == $i ? ' class="current"' : '') . '>' . $i . '</a>';
		}
		return $str;
	}
	
	/*
	 * 构造分页状态
	 * @return string
	 */
	function pageStatus()
	{
		$tmp = ($this->currentPage * $this->pageSize < $this->recordCount ? $this->currentPage*$this->pageSize : $this->recordCount);
		return '<span class = "pagestatus">' . sprintf($this->statusStr, $this->recordCount, $this->currentPage*$this->pageSize-$this->pageSize+1, $tmp) . '</span>';
	}
	
	/*
	 * 保留原始URL参数,并构造出一个新的带页码的URL
	 * @return string
	 */
	function makeUrl($page)
	{
		$url = $_SERVER['REQUEST_URI'];
		if ($this->uriClass && $this->uriClass->urlMode != 'STANDARD') {
			$url = preg_replace('/-page-[0-9]+/i', '', $url);
			$url .= '/page-' . $page;
			return $this->uriClass->wrap($url, $this->ajaxFunc);
		}
		
		$url = preg_replace('/&page=[0-9]+/i', '', $url);
		$url .= '&page=' . $page;
		
		return $url;
	}
}
?>