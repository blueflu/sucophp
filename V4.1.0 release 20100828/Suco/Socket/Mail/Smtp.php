<?php

require_once 'Suco/Socket/Abstract.php';

class Suco_Socket_Mail_Smtp extends Suco_Socket_Abstract
{
	protected $_host;
	protected $_port;
	protected $_user;
	protected $_pass;
	protected $_protocol;
	protected $_auth = false;
	
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
	
	public function send($to, $from, $subject, $body, $mailType = 'TXT', $charset = 'utf-8', $cc = null, $bcc = null, $posttime = null, $attch = null)
	{
		//邮件头
		$header = "MIME-Version:1.0\r\n";
		//$header .= "Connection: Close\r\n";
        $header .= "To: {$to}\r\n";
        if (!empty($cc)) {
            $header .= "Cc: {$cc}\r\n";
        }
        $header .= "From: <{$from}>\r\n";
        $header .= "Subject: =?{$charset}?B?".base64_encode($subject)."?=\r\n";
        $header .= $attch;
        $header .= "Date: ".($posttime ? $posttime : date('r'))."\r\n";
        $header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
        if($mailType=="HTML"){
            $header .= "Content-Type:text/html;charset=\"{$charset}\"\r\n";
        }
        list($msec, $sec) = explode(" ", microtime());
        $header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".{$from}>\r\n";
        
        //发送邮件列表
        $mailList = explode(",", $to);
        if (!empty($cc)) {
            $mailList = array_merge($mailList, explode(",", $cc));
        }
        if (!empty($bcc)) {
            $mailList = array_merge($mailList, explode(",", $bcc));
        }
        foreach ($mailList as $mail) {
			$this->_send($mail, $from, $header, $body);
        }
	}
	
    protected function _send($to, $from, $header, $body = "")
    {
		echo $this->execute("HELO {$this->_host}");
		if ($this->_auth) {
			echo $this->execute("AUTH LOGIN " . base64_encode($this->_user));
			echo $this->execute(base64_encode($this->_pass));
		}
		echo $this->execute("Mail FROM:<{$this->_user}>");
		echo $this->execute("RCPT TO:<{$to}>");
		echo $this->execute("DATA");
		echo $this->execute("{$header}\r\n{$body}");
		echo $this->execute(".");
		echo $this->execute("NOOP");
		echo $this->execute("QUIT");
    }
    
    public function __destroy()
    {
    	$this->close();
    }
}