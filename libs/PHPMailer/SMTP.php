<?php
namespace PHPMailer\PHPMailer;

class SMTP
{
    const VERSION = '6.8.0';

    private function getResponse($socket)
    {
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    public function sendMail($host, $port, $username, $password, $from, $to, $subject, $body, $secure)
    {
        $scheme = '';
        if ($secure === 'ssl') {
            $scheme = 'ssl://';
        } elseif ($secure === 'tls') {
            $scheme = 'tls://';
        }

        $socket = @fsockopen($scheme . $host, $port, $errno, $errstr, 15);
        if (!$socket) {
            throw new Exception("Could not connect to SMTP host: $host ($errstr)");
        }

        $this->getResponse($socket);
        fputs($socket, "EHLO " . gethostname() . "\r\n");
        $this->getResponse($socket);

        if ($username && $password) {
            fputs($socket, "AUTH LOGIN\r\n");
            $this->getResponse($socket);

            fputs($socket, base64_encode($username) . "\r\n");
            $this->getResponse($socket);

            fputs($socket, base64_encode($password) . "\r\n");
            $auth_res = $this->getResponse($socket);
            if (strpos($auth_res, '235') === false) {
                fclose($socket);
                throw new Exception("SMTP Authentication failed for user: $username. Response: $auth_res");
            }
        }

        fputs($socket, "MAIL FROM: <$from>\r\n");
        $this->getResponse($socket);

        foreach ($to as $recipient) {
            fputs($socket, "RCPT TO: <" . $recipient[0] . ">\r\n");
            $this->getResponse($socket);
        }

        fputs($socket, "DATA\r\n");
        $this->getResponse($socket);

        $headers  = "From: $from\r\n";
        $headers .= "To: " . $to[0][0] . "\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

        fputs($socket, $headers . $body . "\r\n.\r\n");
        $this->getResponse($socket);

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }
}