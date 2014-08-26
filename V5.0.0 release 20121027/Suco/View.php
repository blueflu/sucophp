<?php
/**
 * Suco_View 视图类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		View
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/View/Abstract.php';
require_once 'Suco/View/Interface.php';

class Suco_View extends Suco_View_Abstract implements Suco_View_Interface
{
	/**
	 * 视图布局
	 * @var string
	 */
	protected $_layoutPath;

	/**
	 * 当前布局文件
	 * @var string
	 */
	protected $_layoutFile;

	/**
	 * 是否已载入布局
	 * @var bool
	 */
	protected $_layoutLoaded = false;

	/**
	 * 魔术方法
	 * 调用辅助类
	 *
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function __call($name, $args)
	{
		$helper = Suco_Helper::factory($name);
		return call_user_func_array(array($helper, 'callback'), array($args));
	}

	/**
	 * 设置布局文件
	 *
	 * @param string $file
	 * @return object
	 */
	public function setLayout($file)
	{
		$this->_layoutFile = $file;
		return $this;
	}

	/**
	 * 返回布局文件
	 *
	 * @return string
	 */
	public function getLayout()
	{
		return $this->_layoutFile;
	}

	/**
	 * 设置布局路径
	 *
	 * @param string $path
	 * @return object
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = $path;
		return $this;
	}

	/**
	 * 返回布局路径
	 *
	 * @return string
	 */	public function getLayoutPath()
	{
		return $this->_layoutPath;
	}

	/**
	 * 设置辅助类路径
	 *
	 * @param string $path
	 * @return object
	 */
	public function setHelperPath($path)
	{
		Suco_Helper::setHelperPath($path);
		return $this;
	}

	/**
	 * 返回辅助类路径
	 *
	 * @return string
	 */
	public function getHelperPath()
	{
		return Suco_Helper::getHelperPath;
	}

	/**
	 * 渲染并返回视图
	 *
	 * @param string $file
	 * @param array $data
	 * @return string
	 */
	public function output($file, $data = null)
	{
		if ($data) {
			$this->assign($data);
		}

		//渲染视图
		$content = $this->_render($file, $this->_scriptPath);

		//渲染布局
		if ($layout = $this->getLayout()) {
			$this->layout = $this->layout();
			$this->layout->content = $content;
			$content = $this->_render($layout, $this->_layoutPath);
		}
		return $content;
	}

	/**
	 * 渲染并显示视图
	 *
	 * @param string $file
	 * @param array $data
	 * @return string
	 */
	public function render($file, $data = null)
	{
		if ($data) {
			$this->assign($data);
		}

		//渲染视图
		$content = $this->_render($file, $this->_scriptPath);

		//渲染布局
		if ($layout = $this->getLayout()) {
			$this->layout = $this->layout();
			$this->layout->content = $content;
			$content = $this->_render($layout, $this->_layoutPath);
		}
		$this->getResponse()->appendBody($content);

		return $content;
	}

	/**
	 * 渲染并显示视图块
	 * 此方法不加载视图的布局
	 *
	 * @param string $file
	 * @param array $data
	 * @return string
	 */
	public function partial($file, $data = null)
	{
		if ($data) {
			$this->assign($data);
		}

		echo $this->_render($file, $this->_scriptPath);
	}

	/**
	 * 捕捉片断开始
	 * 使用此方法时，系统会忽略片断之前和之后的内容，只显示被捕捉到的部分
	 * 如:<code>
	 *
	 * echo 'before output';
	 * $view = new Suco_View();
	 * $view->fragmentStart()
	 * echo '这里是被捕捉到的片断';
	 * $view->fragmentEnd();
	 * echo 'after output';
	 *
	 * #output
	 * 这里是被捕捉到的片断
	 *
	 * </code>
	 *
	 * @return void
	 */
	public function fragmentStart()
	{
		ob_get_clean();
		ob_start();
	}

	/**
	 * 捕捉片断结束
	 *
	 * @return void
	 */
	public function fragmentEnd()
	{
		echo ob_get_clean(); exit;
	}

	/**
	 * 渲染视图
	 *
	 * @return string
	 */
	protected function _render($file, $path = null)
	{
		ob_start();
		$file = str_replace('/', DIRECTORY_SEPARATOR, $path . $file);
		if (!file_exists($file)) {
			require_once 'Suco/View/Exception.php';
			throw new Suco_View_Exception("找不到视图 [$file]");
		}

		require $file;
		return ob_get_clean();
	}
}