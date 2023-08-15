<?php
namespace App\Service;

use Jgauthi\Component\BackupFile\MailerBackup;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * You can use also in Symfony Controller
 * example for Symfony 6.3+, require emails VAR on .env: MAILER_TO, MAILER_FROM
 */
class SymfonyBackupService
{
    private Address $to;
    private ?Address $from = null;

    public function __construct(
        private MailerBackup $mailer,
        #[Autowire(env: 'MAILER_TO')] string $to,
        #[Autowire(env: 'MAILER_FROM')] ?string $from = null,
    ) {
        $this->mailer->setTitle('Backup files service');

        $this->to = new Address($to);
        if (!empty($this->from)) {
            $this->from = new Address($from);
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
