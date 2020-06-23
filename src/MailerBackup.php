<?php
namespace Jgauthi\Component\BackupFile;

use Exception;
use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Mime\{Address, Email};
use Symfony\Component\Mailer\{Mailer, MailerInterface};
use Symfony\Component\Mailer\Transport\TransportInterface;
use ZipArchive;

// methods de test, implémentation sans idée précise
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
            ->setContent('<p>Voici une sauvegarde de plz fichiers paramétré sur le serveur.</p>')
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
            throw new InvalidArgumentException('Aucun fichier paramétré pour l\'email de backup.');
        }

        $zip = new ZipArchive();
        if (true !== $zip->open($zipfile, ZipArchive::CREATE)) {
            throw new Exception("Impossible d'ouvrir le fichier <$zipfile>");
        }

        foreach ($this->files as $file) {
            $zip->addFile($file->getPath(), $file->getFilename());
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
        $zipFile = tempnam(sys_get_temp_dir(), 'backup');
        if (!$this->compressFiles($zipFile)) {
            throw new Exception("Erreur avec la génération du fichier <$zipFile>");
        }

        if (!empty($from)) {
            $this->email->from($from);
        }

        $this->email
            ->to($to)
            ->priority(Email::PRIORITY_LOWEST)
            ->attachFromPath($zipFile, date('Y-m-d_H-i').'_backup-files.zip')
        ;

        $this->mailer->send($this->email);
        return true;
    }
}