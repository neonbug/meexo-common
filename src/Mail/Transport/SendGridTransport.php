<?php namespace Neonbug\Common\Mail\Transport;

use Swift_Transport;
use Swift_Mime_Message;
use Swift_Events_EventListener;

class SendGridTransport implements Swift_Transport {

	/**
	 * The SendGrid API key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The SendGrid API end-point.
	 *
	 * @var string
	 */
	protected $url;
	
	protected $tmp_files = [];

	/**
	 * Create a new SendGrid transport instance.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function __construct($key)
	{
		$this->key = $key;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function start()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function stop()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$sendgrid = new \SendGrid($this->key);
		$email = new \SendGrid\Email();
		
		list($from, $from_name) = $this->getFromAddresses($message);
		
		$email->setFrom($from);
		$email->setFromName($from_name);
		
		$email->setSubject($message->getSubject());
		$email->setHtml($message->getBody());
		
		$this->setTo($email, $message);
		$this->setCc($email, $message);
		$this->setBcc($email, $message);
		$this->setText($email, $message);
		$this->setAttachment($email, $message);
		
		$sendgrid->send($email);
		
		$this->deleteTempAttachments();
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		//
	}

	/**
	 * Get the API key being used by the transport.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Set the API key being used by the transport.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function setKey($key)
	{
		return $this->key = $key;
	}

	/**
	 * @param SendGrid\Email $email
	 * @param Swift_Mime_Message $message
	 */
	protected function setTo($email, Swift_Mime_Message $message)
	{
		if ($to = $message->getTo()) {
			foreach ($to as $to_email=>$to_name)
			{
				$email->addTo($to_email, $to_name);
			}
		}
	}

	/**
	 * @param SendGrid\Email $email
	 * @param Swift_Mime_Message $message
	 */
	protected function setCc($email, Swift_Mime_Message $message)
	{
		if ($cc = $message->getCc()) {
			foreach ($cc as $cc_email=>$cc_name)
			{
				$email->addCc($cc_email, $cc_name);
			}
		}
	}

	/**
	 * @param SendGrid\Email $email
	 * @param Swift_Mime_Message $message
	 */
	protected function setBcc($email, Swift_Mime_Message $message)
	{
		if ($bcc = $message->getBcc()) {
			foreach ($bcc as $bcc_email=>$bcc_name)
			{
				$email->addBcc($bcc_email, $bcc_name);
			}
		}
	}

	/**
	 * Get From Addresses.
	 *
	 * @param Swift_Mime_Message $message
	 * @return array
	 */
	protected function getFromAddresses(Swift_Mime_Message $message)
	{
		if ($message->getFrom()) {
			foreach ($message->getFrom() as $address => $name) {
				return [$address, $name];
			}
		}
		return [];
	}

	/**
	 * Set text contents.
	 *
	 * @param SendGrid\Email $email
	 * @param Swift_Mime_Message $message
	 */
	protected function setText($email, Swift_Mime_Message $message)
	{
		foreach ($message->getChildren() as $attachment) {
			if (!$attachment instanceof Swift_MimePart) {
				continue;
			}
			$email->setText($attachment->getBody());
		}
	}

	/**
	 * Set Attachment Files.
	 *
	 * @param SendGrid\Email $email
	 * @param Swift_Mime_Message $message
	 */
	protected function setAttachment($email, Swift_Mime_Message $message)
	{
		foreach ($message->getChildren() as $attachment) {
			$tmp_filename = tempnam(sys_get_temp_dir(), 'att');
			file_put_contents($tmp_filename, $attachment->getBody());
			
			$email->addAttachment($tmp_filename, $attachment->getFilename());
			
			$this->tmp_files[] = $tmp_filename;
		}
	}
	
	protected function deleteTempAttachments()
	{
		foreach ($this->tmp_files as $tmp_filename)
		{
			if (file_exists($tmp_filename)) unlink($tmp_filename);
		}
		$this->tmp_files = [];
	}

}
