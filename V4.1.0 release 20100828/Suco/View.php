<?php 

require_once 'Suco/View/Abstract.php';

class Suco_View extends Suco_View_Abstract
{
	protected $_helperNamespace = 'Helper_';
	protected $_helperPath;
	
	protected $_layoutPath;
	protected $_layoutFile;
	protected $_layoutLoaded = false;

	public function __call($name, $args)
	{
        $helper = $this->getHelper($name);
		return call_user_func_array(array($helper, $name), $args);
	}
	
	public function setLayout($file)
	{
		$this->_layoutFile = $file;
		return $this;
	}
	
	public function getLayout()
	{
		return $this->_layoutFile;
	}
	
	public function setLayoutPath($path)
	{
		$this->_layoutPath = $path;
		return $this;
	}
	
	public function getLayoutPath()
	{
		return $this->_layoutPath;
	}
	
	public function setHelperPath($path)
	{
		$this->_helperPath = $path;
		return $this;
	}
	
	public function getHelperPath()
	{
		return $this->_helperPath;
	}
	
	public function getHelper($name)
	{
		$classname = 'Suco_Helper_' . ucfirst($name);
		if ($this->_helperPath) {
			$file = $this->_helperPath . ucfirst($name) . '.php';
			if (file_exists($file)) {
				require_once $file;
				if ($this->_helperNamespace) {
					$classname = $this->_helperNamespace . ucfirst($name);
				}
				return $classname;
			}
		}
		
		try {
			require_once 'Suco/Loader/Class.php';
			Suco_Loader_Class::loadClass($classname);
			return $classname;
		} catch (Suco_Loader_Exception $e) {
			throw new Suco_Loader_Exception("加载辅助类{$classname}失败!");
		}
	}
	
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
	
	public function partial($file, $data = null)
	{
		if ($data) {
			$this->assign($data);
		}
		
		$content = $this->_render($file, $this->_scriptPath);
		echo $content;
		
		return $content;
	}
	
	protected function _render($file, $path = null)
	{
		ob_start();
		$file = $path . $file;
		if (!file_exists($file)) {
			require_once 'Suco/View/Exception.php';
			throw new Suco_View_Exception("找不到视图 [$file]");
		}
		
		require $file;
		return ob_get_clean();
	}
}