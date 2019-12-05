<?php

require('../vendor/autoload.php');

class SpamKilla
{

    protected $key;
    protected $url;
    protected $message;

    /**
     * SpamKilla constructor.
     * @param  null  $author
     * @param  null  $email
     * @param  null  $message
     */
    public function __construct($author = null, $email = null, $message = null)
    {

        $dotenv = Dotenv\Dotenv::create(__DIR__,);
        $dotenv->load();

        $this->key = getenv('AKISMET_KEY');
        $this->url = $this->key.'rest.akismet.com/1.1/comment-check';
        $this->message = $this->buildRequest($author, $email, $message);

    }

    /**
     * @param $author
     * @param $email
     * @param $message
     * @return array
     */
    private function buildRequest($author, $email, $message)
    {

        $arr = [
            'blog' => getenv('SITE_URL'),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'permalink' => "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'comment_type' => 'contact-form',
            'comment_author' => $author,
            'comment_author_email' => $email,
            'comment_content' => $message,
        ];

        return $arr;

    }

    /**
     * @return bool
     */
    public function SendTheBoysRound()
    {
        $request = '';
        foreach ($this->message as $k => $v) {
            $request .= $k.'='.urlencode($v).'&';
        }

        substr($request, 0, -1);

        $path = '/1.1/comment-check';
        $port = 443;
        $akismet_ua = "WordPress/4.4.1 | Akismet/3.1.7";
        $content_length = strlen($request);
        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $this->url\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $http_request .= "Content-Length: {$content_length}\r\n";
        $http_request .= "User-Agent: {$akismet_ua}\r\n";
        $http_request .= "\r\n";
        $http_request .= $request;
        $response = '';
        if (false !== ($fs = @fsockopen('ssl://'.$this->url, $port, $errno, $errstr, 10))) {

            fwrite($fs, $http_request);

            while (!feof($fs)) {
                $response .= fgets($fs, 1160);
            } // One TCP-IP packet
            fclose($fs);

            $response = explode("\r\n\r\n", $response, 2);
        }

        if ('true' === $response[1]) {
            return true;
        }

        return false;
    }

}