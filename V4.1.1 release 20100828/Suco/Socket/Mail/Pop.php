<?php

require_once 'Suco/Socket/Abstract.php';

class Suco_Socket_Mail_Pop extends Suco_Socket_Abstract
{
	protected $_host;
	protected $_port;
	protected $_user;
	protected $_pass;
	protected $_charset;
	
	public function __construct($host, $port, $user, $pass, $charset = 'utf-8', $timeout = 30)
	{
		$this->_host = $host;
		$this->_port = $port;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_charset = $charset;
		
    	$this->open($this->_host, $this->_port, $timeout);
	}
	
	public function send($to, $from, $subject, $body, $mailType = 'TXT', $cc = null, $bcc = null, $posttime = null, $attch = null)
	{
		//邮件头
		$header = "MIME-Version:1.0\r\n";
        $header .= "To: {$to}\r\n";
        if (!empty($cc)) {
            $header .= "Cc: {$cc}\r\n";
        }
        $header .= "From: <{$from}>\r\n";
        $header .= "Subject: =?{$this->_charset}?B?".base64_encode($subject)."?=\r\n";
        $header .= $attch;
        $header .= "Date: ".($posttime ? $posttime : date('r'))."\r\n";
        $header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
        if($mailType=="HTML"){
            $header .= "Content-Type:text/html;charset=\"{$this->_charset}\"\r\n";
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
			$this->_send($from, $mail, $header, $body);
        }
	}
	
    protected function _send($from, $to, $header, $body = "")
    {
		$this->execute("HELO {$this->_host}");
		$this->execute("AUTH LOGIN " . base64_encode($this->_user));
		$this->execute(base64_encode($this->_pass));
		$this->execute("Mail FROM:<{$this->_user}>");
		$this->execute("RCPT TO:<{$to}>");
		$this->execute("DATA");
		$this->execute("{$header}\r\n{$body}");
		$this->execute(".");
		$this->execute("QUIT");
    }
    
    public function __destroy()
    {
    	$this->close();
    }
}