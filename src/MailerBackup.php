<?php
namespace Jgauthi\Component\BackupFile;

use Exception;
use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Mime\{Address, Email};
use Symfony\Component\Mailer\{Mailer, MailerInterface};
use Symfony\Component\Mailer\Transport\TransportInterface;
use ZipArchive;

class MailerBackup
{
    private Email $email;
    private MailerInterface $mailer;

    /** @var SplFileObject[] files */
    private array $files;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->email = new Email;

        $this
            ->setTitle(sprintf('[%s] Backup files', date('Y-m-d H:i')))
            ->setContent('<p>Here is a backup of several files configured on the server.</p>')
        ;
    }

    static public function initFromTransport(TransportInterface $transport): self
    {
        $mailer = new Mailer($transport);

        $class = __CLASS__;
        return new $class($mailer);
    }

    public function setTitle(string $title): self
    {
        $this->email->subject($title);
        return $this;
    }

    public function setContent(string $htmlContent): self
    {
        $this->email
            ->text(strip_tags($htmlContent))
            ->html($htmlContent)
        ;

        return $this;
    }

    /**
     * @param string|SplFileObject $file
     * @return self
     */
    public function addFile($file): self
    {
        if (!$file instanceof SplFileObject) {
            $file = new SplFileObject($file);
        }

        $this->files[] = $file;
        return $this;
    }

    /**
     * @throws Exception
     */
    private function compressFiles(string $zipfile): bool
    {
        if (empty($this->files)) {
            throw new InvalidArgumentException('No file configured for the backup email.');
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($zipfile, ZipArchive::CREATE)) {
            throw new Exception("Impossible to open the file <$zipfile>");
        }

        foreach ($this->files as $file) {
            $zip->addFile($file->getPathname(), $file->getFilename());
        }

        $zip->setArchiveComment('Archive generate the '. date('Y-m-d H:i'));
        return $zip->close();
    }

    /**
     * @param Address|string $to
     * @param Address|string|null $from
     * @return bool
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws Exception
     */
    public function send($to, $from = null): bool
    {
        if (empty($to)) {
            throw new InvalidArgumentException("The field 'to' is empty.");
        }

        $zipFile = tempnam(sys_get_temp_dir(), 'backup');
        if (!$this->compressFiles($zipFile)) {
            throw new Exception("Error generating file <$zipFile>");
        }

        if (empty($from)) {
            $server = (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
            $from = 'no-reply+'. (new \ReflectionClass($this))->getShortName() ."@$server";
        }

        $this->email
            ->from( ($from instanceof Address) ? $from : new Address($from) )
            ->to( ($to instanceof Address) ? $to : new Address($to) )
            ->priority(Email::PRIORITY_LOWEST)
            ->attachFromPath($zipFile, date('Y-m-d_H-i').'_backup-files.zip')
        ;

        $this->mailer->send($this->email);
        return true;
    }
}