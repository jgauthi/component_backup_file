<?php
use Jgauthi\Component\BackupFile\MailerBackup;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

// Init Transport: https://symfony.com/doc/current/mailer.html#transport-setup
$configSmtp = 'smtp://[login]:[password]@[host]:[port]?encryption=tls&auth_mode=cram-md5';
$transport = Transport::fromDsn($configSmtp);
$to = 'johndoe@example.com';
$from = 'server@example.com';

// Init Mailer
$mailer = MailerBackup::initFromTransport($transport);
$mailer->addFile(__FILE__);

try {
    $mailer->send($to, $from);
    echo "Mail successfully sent to {$to->getAddress()}";

} catch (Exception | TransportExceptionInterface $e) {
    echo $e->getMessage();
}
