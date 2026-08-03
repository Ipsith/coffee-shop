<?php
namespace PHPMailer\PHPMailer;

class PHPMailer
{
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority = null;
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/html';
    public $Encoding = '8bit';
    public $ErrorInfo = '';
    public $From = '';
    public $FromName = '';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $WordWrap = 0;
    public $Mailer = 'smtp';
    public $Sendmail = '/usr/sbin/sendmail';
    public $UseSendmailOptions = true;
    public $ConfirmReadingTo = '';
    public $Hostname = '';
    public $MessageID = '';
    public $MessageDate = '';
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = '';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $SMTPOptions = array();
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $Timeout = 30;
    public $dsn = '';
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    public $SMTPKeepAlive = false;

    protected $smtp = null;
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $ReplyTo = array();
    protected $all_recipients = array();
    protected $attachment = array();
    protected $CustomHeader = array();
    protected $message_type = '';
    protected $boundary = array();
    protected $language = array();
    protected $error_count = 0;
    protected $sign_cert_file = '';
    protected $sign_key_file = '';
    protected $sign_extracerts_file = '';
    protected $sign_key_pass = '';
    protected $exceptions = false;

    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool)$exceptions;
        }

        if (file_exists(__DIR__ . '/SMTP.php')) {
            require_once __DIR__ . '/SMTP.php';
        }
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function setFrom($address, $name = '', $auto = true)
    {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    public function addAddress($address, $name = '')
    {
        $this->to[] = array($address, $name);
        return true;
    }

    public function isHTML($isHtml = true)
    {
        if ($isHtml) {
            $this->ContentType = 'text/html';
        } else {
            $this->ContentType = 'text/plain';
        }
    }

    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $e) {
            $this->mailHeader = '';
            $this->setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            return false;
        }
    }

    protected function preSend()
    {
        if (empty($this->smtp)) {
            $this->smtp = new SMTP();
        }
        return true;
    }

    protected function postSend()
    {
        return $this->smtp->sendMail(
            $this->Host,
            $this->Port,
            $this->Username,
            $this->Password,
            $this->From,
            $this->to,
            $this->Subject,
            $this->Body,
            $this->SMTPSecure
        );
    }

    protected function setError($msg)
    {
        $this->error_count++;
        $this->ErrorInfo = $msg;
    }
}