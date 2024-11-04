<?php
namespace App\Service;

use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class RuleEngine
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends an email notification to the recipient.
     *
     * @param string $recipient The email address to send the notification.
     * @param string $content The content of the email notification.
     */
    public function sendEmail(string $scanResult, string $userEmail): void
    {

        $email = (new Email())
            ->from('prabakaranp1971@gmail.com')
            ->to($userEmail)
            ->subject('Scan Result Notification')
            ->text($scanResult);
        try {
            $this->mailer->send($email);
        } 
        catch (Exception $e) {
            throw new \Exception('Failed to send email: ' . $e->getMessage());
        }
    }
}
