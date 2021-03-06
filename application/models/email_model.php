<?php

class Email_model extends CI_Model{

	protected $accounts_manager;
	protected $sessions_manager;
	protected $errors;
	protected $mailer;

	public function __construct(){

		parent::__construct();
		
		$ioc = $this->config->item('ioc');
		$this->accounts_manager = $ioc['PolyAuth\Accounts\AccountsManager'];
		$this->sessions_manager = $ioc['PolyAuth\Sessions\UserSessions'];
		$this->sessions_manager->start();

		$this->mailer = new \PHPMailer;
		$this->load->library('form_validation', false, 'validator');

	}

	public function send($input_data){

		//on the other hand if the the user is a developer or admin, we also allow them to send contact emails
		if(!$this->sessions_manager->authorized(false, 'developer') AND !$this->sessions_manager->authorized(false, 'admin')){
			$this->errors = array(
				'error'	=> 'Not authorised to email.'
			);
			return false;
		}

		$data = elements(array(
			'toUser',
			'fromEmail',
			'replyTo',
			'message',
			'authorName',
			'senderName',
			'ideaId',
			'ideaUrl',
			'ideaTitle',
		), $input_data, null, true);

		$this->validator->set_data($data);

		$this->validator->set_rules(array(
			array(
				'field'	=> 'toUser',
				'label'	=> 'User ID',
				'rules'	=> 'required|trim|integer',
			),
			array(
				'field'	=> 'fromEmail',
				'label'	=> 'From Email',
				'rules'	=> 'required|trim|valid_email',
			),
			array(
				'field'	=> 'replyTo',
				'label'	=> 'Reply To Email',
				'rules'	=> 'required|trim|valid_email',
			),
			array(
				'field'	=> 'message',
				'label'	=> 'Message',
				'rules'	=> 'required|htmlspecialchars|trim|min_length[100]|max_length[13500]'
			),
			array(
				'field'	=> 'authorName',
				'label'	=> 'Author Name',
				'rules'	=> 'required|trim|htmlspecialchars',
			),
			array(
				'field'	=> 'senderName',
				'label'	=> 'Sender Name',
				'rules'	=> 'required|trim|htmlspecialchars',
			),
			array(
				'field'	=> 'ideaId',
				'label'	=> 'Idea ID',
				'rules'	=> 'required|numeric',
			),
			array(
				'field'	=> 'ideaUrl',
				'label'	=> 'Idea URL',
				'rules'	=> 'required|alpha_dash',
			),
			array(
				'field'	=> 'ideaTitle',
				'label'	=> 'Idea Title',
				'rules'	=> 'required|trim|htmlspecialchars',
			),
		));

		if($this->validator->run() ==  false){

			$this->errors = array(
				'validation_error'	=> $this->validator->error_array()
			);

			return false;

		}

		$user = $this->accounts_manager->get_user($data['toUser']);
		$data['toEmail'] = $user['email'];

		$message = $this->load->view('emails/developer_contact_email', $data, true);

		$query = $this->send_email($data['toEmail'], $data['fromEmail'], $data['replyTo'], $message, true);

		if(!$query){

			$this->errors = array(
				'error'	=> 'Problem sending email. Try again.'
			);

			log_message('error', 'Mailer Error: ' . $this->mailer->ErrorInfo);

			return false;

		}

		return true;

	}

	public function send_enquiry($input_data){

		$data = elements(array(
			'fromEmail',
			'replyTo',
			'message'
		), $input_data, null, true);

		$this->validator->set_data($data);

		$this->validator->set_rules(array(
			array(
				'field'	=> 'fromEmail',
				'label'	=> 'From Email',
				'rules'	=> 'required|trim|valid_email',
			),
			array(
				'field'	=> 'replyTo',
				'label'	=> 'Reply To Email',
				'rules'	=> 'required|trim|valid_email',
			),
			array(
				'field'	=> 'message',
				'label'	=> 'Message',
				'rules'	=> 'required|htmlspecialchars|trim|min_length[16]|max_length[13500]'
			),
		));

		if($this->validator->run() ==  false){

			$this->errors = array(
				'validation_error'	=> $this->validator->error_array()
			);
			return false;

		}

		//all enquiry emails are sent to info@dreamitapp.com
		$data['toEmail'] = $this->config->item('sitemeta')['email'];

		$query = $this->send_email($data['toEmail'], $data['fromEmail'], $data['replyTo'], $data['message'], false);

		if(!$query){

			$this->errors = array(
				'error'	=> 'Problem sending email. Try again.'
			);

			log_message('error', 'Mailer Error: ' . $this->mailer->ErrorInfo);

			return false;

		}

		return true;

	}

	protected function send_email($to, $from, $reply_to, $message, $html){

		$this->mailer->IsSMTP();
		$this->mailer->Host = 'smtp.mandrillapp.com';
		$this->mailer->Port = 587;
		$this->mailer->SMTPAuth = true;
		$this->mailer->Username = $_ENV['secrets']['mandrill_user'];
		$this->mailer->Password = $_ENV['secrets']['mandrill_key'];
		$this->mailer->SMTPSecure = 'tls';

		$this->mailer->From = $from;
		$this->mailer->FromName = 'Dream it App Notifications';
		$this->mailer->addReplyTo($reply_to);
		$this->mailer->AddAddress($to);

		$this->mailer->Subject = 'Message from Dream it App Notifications';
		$this->mailer->Body = $message;
		$this->mailer->isHTML($html);

		if(!$this->mailer->Send()) {
			return false;
		}

		return true;

	}

	public function get_errors(){

		return $this->errors;

	}

}