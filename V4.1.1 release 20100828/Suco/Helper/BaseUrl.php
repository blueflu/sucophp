<?php

class Suco_Helper_BaseUrl
{
	public function baseUrl($url = null)
	{
		$site = !isset($arr['scheme']) ? Suco_Application::getInstance()->getRequest()->getHost() : '';
		$baseUrl = Suco_Application::getInstance()->getRequest()->getBasePath();
		if (substr($url, 0, 1) != '/') {
			$baseUrl = '/' . $baseUrl;
		}

		if ($url) {
			$src = parse_url($url);
			if (isset($src['scheme'])) {
				return $url;
			} else {
				return $site . $baseUrl . $url;
			}
		} else {
			return $site . $baseUrl;
		}
	}
}