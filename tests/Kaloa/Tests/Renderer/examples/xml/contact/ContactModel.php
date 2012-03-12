<?php

namespace org\example\Contact;

use PHPMailer;

/**
 * Application logic for contact form
 */
class ContactModel
{
    /**
     * @var array Holds all titles (salutations) that may be chosen from
     */
    protected $titles = array(
       #0 => intentionally left blank ("not chosen")
        1 => 'Mr',
        2 => 'Ms',
        3 => 'Company'
    );

    /**
     * @var string Path to PHPMailer directory in local file system
     */
    protected $pathToMailer = './library/phpmailer/PHPMailer_v5.1';

    /**
     * @var string All e-mails will be sent to this address
     */
    protected $emailRecipient = 'example@example.org';

    /**
     * @var int Number of seconds a session has to wait before a second e-mail
     *          will be accepted (protection from spam and accidental double
     *          posts)
     * @todo Probably not the most clever way to do this
     */
    protected $emailInterval = 60;

    /**
     * Returns all titles (salutations) a user may chose from
     *
     * @return array Titles to chose from
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * Sanitizes and validates input, calls the method to mail the supplied
     * contact form data and returns an array of errors
     *
     * @return array Errors (an empty array indicates there are no errors)
     */
    public function sendEmailFromPOST()
    {
        $errors = array();

        // Do not overwrite original data in $_POST
        $data = $_POST;

        // Make sure every field is set
        $data['email'] = (isset($data['email']))
                       ? trim((string) $data['email']) : '';
        $data['title'] = (isset($data['title']))
                       ? (int) $data['title'] : 0;
        $data['name']  = (isset($data['name']))
                       ? trim((string) $data['name']) : '';
        $data['phone'] = (isset($data['phone']))
                       ? trim((string) $data['phone']) : '';
        $data['callback'] = (isset($data['callback']))
                          ? (bool) $data['callback'] : false;

        // Validate input
        $data['email'] = preg_match('/^[^@\s]+@[^@\s]+\.[^@\s.]+$/',
                                    $data['email'])
                       ? $data['email'] : '';
        $data['title'] = (isset($this->titles[$data['title']]))
                       ? $data['title'] : 0;

        // Generate error messages for fields that have to be filled
        if ($data['email'] === '') {
            $errors[] = 'Please provide a valid e-mail address.';
        }
        if ($data['title'] === 0) {
            $errors[] = 'Please choose a title from the list.';
        }
        if ($data['name'] === '') {
            $errors[] = 'Please enter your name.';
        }

        // In case of error, return
        if (!empty($errors)) {
            return $errors;
        }

        // Otherwise, we're clear to mail the data
        $errors = $this->doSendEmail($data);

        return $errors;
    }

    /**
     * Mails data from contact form
     *
     * @param array $data Sanitized and validated form input
     * @return array Errors (an empty array indicates there are no errors)
     */
    protected function doSendEmail($data)
    {
        // Application relies on session for spam protection
        if (!isset($_SESSION['contact']['lastEmailSent'])) {
            return array('You have to accept the session cookie.');
        }

        // See if enough time has passed to send another e-mail
        if (isset($_SESSION['contact']['lastEmailSent'])
                && ($_SESSION['contact']['lastEmailSent']
                        + $this->emailInterval > time())
        ) {
            return array(sprintf(
                    'You have to wait a total of %d seconds before you can '
                    . 'send another e-mail.', $this->emailInterval));
        }

        require_once $this->pathToMailer . '/class.phpmailer.php';

        // Generate body
        $body = sprintf("E-Mail:   %s\n", $data['email'])
              . sprintf("Title:    %s\n", $this->titles[$data['title']])
              . sprintf("Name:     %s\n", $data['name'])
              . sprintf("Phone:    %s\n", $data['phone'])
              . sprintf("Callback: %s", ($data['callback']) ? 'yes' : 'no');

        $mail = new PHPMailer();

        // Setup mailing method
        /** @todo Configuration settings should be moved to a config file */
        $mail->IsSMTP(true);
        $mail->Host     = 'ssl://smtp.example.org:465';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';

        // Set e-mail headers to more or less fitting values
        $mail->Subject = 'example.org -- Contact inquiry';
        $mail->Body    = $body;
        $mail->CharSet = 'UTF-8';

        $mail->AddReplyTo($data['email'], $data['name']);
        $mail->SetFrom('mailer@example.org');
        
        $mail->AddAddress($this->emailRecipient);        

        // Try to send mail, return errors on failure
        if (!$mail->Send()) {
            return array('Mailer Error: ' . $mail->ErrorInfo);
        }

        // E-mail sent, as far as we can tell. No errors
        $_SESSION['contact']['lastEmailSent'] = time();
        $_POST = array();

        return array();
    }
}