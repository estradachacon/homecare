<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'fcencomiendasnet@gmail.com';
    public $fromName  = 'FC Encomiendas';
    public string $recipients = '';
    public string $userAgent = 'CodeIgniter';
    public $protocol  = 'smtp';
    public string $mailPath = '/usr/sbin/sendmail';
    public string $SMTPHost   = 'smtp.gmail.com';
    public string $SMTPUser   = 'fcencomiendasnet@gmail.com';
    public $SMTPPass  = 'bsni sxrb diyw rosf';
    public $SMTPPort  = 587;
    public int $SMTPTimeout = 5;
    public bool $SMTPKeepAlive = false;
    public $SMTPCrypto = 'tls';
    public int $wordWrap   = 80;
    public int $wrapChars = 76;
    public $mailType  = 'html';
    public string $charset = 'UTF-8';
    public bool $validate = true;
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;
}
