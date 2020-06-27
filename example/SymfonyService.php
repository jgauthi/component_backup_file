<?php
namespace App\Service;

use Jgauthi\Component\BackupFile\MailerBackup;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

// You can use also in Symfony Controller
class SymfonyService
{
    private MailerBackup $mailer;
    private Address $to;
    private ?Address $from;

    /**
     * Service constructor.
     * @param MailerInterface $mailer
     * @param Address|string $to
     * @param Address|string|null $from
     */
    public function __construct(MailerInterface $mailer, $to, $from = null)
    {
        $this->mailer = new MailerBackup($mailer);
        $this->mailer->setTitle('Backup files service');

        $this->to = (($to instanceof Address) ? $to : new Address($to));
        if (!empty($this->from)) {
            $this->from = (($from instanceof Address) ? $from : new Address($from));
        }
    }

    public function setFiles(iterable $files): self
    {
        $titleFiles = [];
        foreach ($files as $file) {
            $titleFiles[] = basename($file);
            $this->mailer->addFile($file);
        }

        $this->mailer->setContent('Files contained in the archive: '. implode(', ', $titleFiles));
        return $this;
    }

    /**
     * @return bool
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface|\Exception
     */
    public function send(): bool
    {
        return $this->mailer->send($this->to, $this->from);
    }
}