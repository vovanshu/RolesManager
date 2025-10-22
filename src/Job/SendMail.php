<?php declare(strict_types=1);

namespace RolesManager\Job;

use Omeka\Job\AbstractJob;
// use Omeka\Stdlib\Mailer as MailerService;
use Laminas\Mail\Exception\ExceptionInterface as MailException;

class SendMail extends AbstractJob
{

    public function perform(): void
    {

        $process = $this->getArg('process');
        $receivers = $this->getArg('receivers');
        $subject = $this->getArg('subject');
        $text = $this->getArg('text');

        // The reference id is the job id for now.
        $referenceIdProcessor = new \Laminas\Log\Processor\ReferenceId();
        $referenceIdProcessor->setReferenceId('items-review/sent-mail/job_' . $this->job->getId());

        $logger = $this->serviceLocator->get('Omeka\Logger');
        $logger->addProcessor($referenceIdProcessor);

        $mailer = $this->serviceLocator->get('Omeka\Mailer');

        try {
            $message = $mailer->createMessage();
            $message->addTo($receivers)->setSubject($subject)->setBody($text);
            $mailer->send($message);
        } catch (MailException $e) {
            $logger->err((string) $e);
            // $this->messenger()->addWarning('Unable to send email.'); // @translate
        }

        if ($this->job->getStatus() === \Omeka\Entity\Job::STATUS_ERROR) {
            return;
        }

    }


}
