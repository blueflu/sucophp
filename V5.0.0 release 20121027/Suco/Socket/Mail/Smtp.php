<?php
/**
 * Suco_Socket_Mail_Smtp SMTP 操作封装
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Socket
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/Socket/Abstract.php';

class Suco_Socket_Mail_Smtp extends Suco_Socket_Abstract
{
	/**
	 * SMTP主机地址
	 * @var string
	 */
	protected $_host;

	/**
	 * SMTP主机端口
	 * @var int
	 */
	protected $_port;

	/**
	 * SMTP用户名
	 * @var string
	 */
	protected $_user;

	/**
	 * SMTP密码
	 * @var string
	 */
	protected $_pass;

	/**
	 * 套接协议
	 * @var string
	 */
	protected $_protocol;

	/**
	 * 是否需要身份验证
	 */
	protected $_auth = false;

	/**
	 * 构造函数
	 * 连接SMTP服务器
	 *
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $pass
	 * @param string $protocol
	 * @param bool $auth
	 * @param int $timeout
	 * @return void
	 */
	public function __construct($host, $port, $user, $pass, $protocol = 'tcp', $auth = true, $timeout = 3)
	{
		$this->_host = $host;
		$this->_port = $port;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_auth = $auth;
		$this->_protocol = $protocol;

    	$this->open($this->_host, $this->_port, $timeout, $protocol);
	}

	/**
	 * 发送邮件
	 *
	 * @param string $to 收件人
	 * @param string $from 发件人
	 * @param string $subject 邮件主题
	 * @param string $mailType 邮件类型 TXT|HTML
	 * @param string $charset 字符集
	 * @param string $cc 超送
	 * @param string $bcc 密送
	 * @param string $posttime 发送时间
	 * @param mixed $attch 附件
	 * @return void
	 */
	public function send($to, $from, $subject, $body, $mailType = 'TXT', $charset = 'utf-8', $cc = null, $bcc = null, $posttime = null, $attch = null)
	{
		//邮件头
		$header = "MIME-Version:1.0\r\n";
		//$header .= "Connection: Close\r\n";
        $header .= "To: {$to}\r\n";
        if (!empty($cc)) {
            $header .= "Cc: {$cc}\r\n";
        }
        if (!empty($bcc)) {
            $header .= "Bcc: {$bcc}\r\n";
        }
        $header .= "From: <{$from}>\r\n";
        $header .= "Subject: =?{$charset}?B?".base64_encode($subject)."?=\r\n";
        $header .= $attch;
        $header .= "Date: ".($posttime ? $posttime : date('r'))."\r\n";
        $header .= "X-Mailer:By SucoPHP (PHP/".phpversion().")\r\n";
        if($mailType=="HTML"){
            $header .= "Content-Type:text/html;charset=\"{$charset}\"\r\n";
        }
        list($msec, $sec) = explode(" ", microtime());
        $header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".{$from}>\r\n";

        //发送邮件列表
        $mailList = explode(",", $to);
        foreach ($mailList as $mail) {
			$this->_send($mail, $from, $header, $body);
        }
	}

	/**
	 * 发送邮件
	 *
	 * @param string $to 收件人
	 * @param string $from 发件人
	 * @param string $header 邮件头
	 * @param string $body 邮件内容
	 * @return void
	 */
    protected function _send($to, $from, $header, $body = "")
    {
		$this->execute("HELO {$this->_host}");
		if ($this->_auth) {
			$this->execute("AUTH LOGIN " . base64_encode($this->_user));
			$this->execute(base64_encode($this->_pass));
		}
		$this->execute("Mail FROM:<{$this->_user}>");
		$this->execute("RCPT TO:<{$to}>");
		$this->execute("DATA");
		$this->execute("{$header}\r\n{$body}");
		$this->execute(".");
		$this->execute("NOOP");
		$this->execute("QUIT");
    }

	/**
	 * 构造函数
	 * 关闭 Socket 连接
	 *
	 * @return void
	 */
    public function __destroy()
    {
    	$this->close();
    }
}