<?php
use Ifsnop\Mysqldump as IMysqldump;
use Jgauthi\Component\BackupFile\MailerBackup;
use Symfony\Component\Mailer\{Mailer, Transport};

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

// Init Transport: https://symfony.com/doc/current/mailer.html#transport-setup
$configSmtp = 'smtp://[login]:[password]@[host]:[port]?encryption=tls&auth_mode=cram-md5';
$transport = Transport::fromDsn($configSmtp);
$mailer = new MailerBackup( new Mailer($transport) );

// Use library: https://github.com/ifsnop/mysqldump-php (add "ifsnop/mysqldump-php" on composer)
// [Alternative] You can create the file with mysqldump command
try {
    $dumpFile = sys_get_temp_dir().'/dump.sql';
    $dump = new IMysqldump\Mysqldump('mysql:host=localhost;dbname=testdb', 'username', 'password');
    $dump->start($dumpFile);

} catch (Exception $e) {
    die('mysqldump-php error: ' . $e->getMessage());
}

$mailer->addFile($dumpFile);
$mailer->send('johndoe@example.com');