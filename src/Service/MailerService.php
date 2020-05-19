<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    /** @var MailerInterface */
    private $mailer;

    /**
     * MailerService constructor.
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMail($from, $to, $subject, $body, $html)
    {
        $mail = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($body)
            ->html($html)
        ;

        $this->mailer->send($mail);
    }
}
