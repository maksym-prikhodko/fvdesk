<?php
namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Model\Email\Emails;
use App\Model\Ticket\Ticket_attachments;
use App\Model\Ticket\Ticket_Thread;
class MailController extends Controller {
	public $email = "";
	public $stream = "";
	function decode_imap_text($str) {
		$result = '';
		$decode_header = imap_mime_header_decode($str);
		foreach ($decode_header AS $obj) {
			$result .= htmlspecialchars(rtrim($obj->text, "\t"));
		}
		return $result;
	}
	function getdata() {
		$email = new Emails;
		$mailboxes = $email->get();
		if (count($mailboxes) >= 0) {
			foreach ($mailboxes as $current_mailbox) {
				if ($current_mailbox['fetching_status']) {
					$stream = @imap_open($current_mailbox['fetching_host'], $current_mailbox['email_address'], $current_mailbox['password']);
					$testvar = "";
					if ($stream >= 0) {
						$emails = imap_search($stream, 'SINCE ' . date('d-M-Y', strtotime("-10 day")));
						if ($emails != false) {
							if (count($emails) >= 0) {
								rsort($emails);
								foreach ($emails as $email_id) {
									$overview = imap_fetch_overview($stream, $email_id, 0);
									$var = $overview[0]->seen ? 'read' : 'unread';
									if ($var == 'unread') {
										$testvar = 'set';
										$from = $this->decode_imap_text($overview[0]->from);
										$subject = $this->decode_imap_text($overview[0]->subject);
										$datetime = $overview[0]->date;
										$date_time = explode(" ", $datetime);
										$date = $date_time[1] . "-" . $date_time[2] . "-" . $date_time[3] . " " . $date_time[4];
										$emailadd = explode('&', $from);
										$username = $emailadd[0];
										$emailadd = substr($emailadd[1], 3);
										$date = date('Y-m-d H:i:s', strtotime($date));
										$system = "Email";
										$phone = "";
										$helptopic = $this->default_helptopic();
										$sla = $this->default_sla();
										$structure = imap_fetchstructure($stream, $email_id);
										if ($structure->subtype == 'HTML') {
											$body2 = imap_fetchbody($stream, $email_id, 1);
											if ($body2 == null) {
												$body2 = imap_fetchbody($stream, $email_id, 1);
											}
											$body = quoted_printable_decode($body2);
										}
										if ($structure->subtype == 'ALTERNATIVE') {
											if (isset($structure->parts)) {
												$body2 = imap_fetchbody($stream, $email_id, 1.2);
												if ($body2 == null) {
													$body2 = imap_fetchbody($stream, $email_id, 1);
												}
												$body = quoted_printable_decode($body2);
											}
										}
										if ($structure->subtype == 'RELATED') {
											if (isset($structure->parts)) {
												$parts = $structure->parts;
												$i = 0;
												$body2 = imap_fetchbody($stream, $email_id, 1.2);
												if ($body2 == null) {
													$body2 = imap_fetchbody($stream, $email_id, 1);
												}
												$body = quoted_printable_decode($body2);
												foreach ($parts as $part) {
													if ($parts[$i]) {
													}
													$i++;
													if (isset($parts[$i])) {
														if ($parts[$i]->ifid == 1) {
															$id = $parts[$i]->id;
															$imageid = substr($id, 1, -1);
															$imageid = "cid:" . $imageid;
															if ($parts[$i]->ifdparameters == 1) {
																foreach ($parts[$i]->dparameters as $object) {
																	if (strtolower($object->attribute) == 'filename') {
																		$filename = $object->value;
																	}
																}
															}
															if ($parts[$i]->ifparameters == 1) {
																foreach ($parts[$i]->parameters as $object) {
																	if (strtolower($object->attribute) == 'name') {
																		$name = $object->value;
																	}
																}
															}
															$body = str_replace($imageid, $filename, $body);
														}
													}
												}
											}
										}
										elseif ($structure->subtype == 'MIXED') {
											if (isset($structure->parts)) {
												$parts = $structure->parts;
												if ($parts[0]->subtype == 'ALTERNATIVE') {
													if (isset($structure->parts)) {
														$body2 = imap_fetchbody($stream, $email_id, 1.2);
														if ($body2 == null) {
															$body2 = imap_fetchbody($stream, $email_id, 1);
														}
														$body = quoted_printable_decode($body2);
													}
												}
												if ($parts[0]->subtype == 'RELATED') {
													if (isset($parts[0]->parts)) {
														$parts = $parts[0]->parts;
														$i = 0;
														$body2 = imap_fetchbody($stream, $email_id, 1.1);
														if ($body2 == null) {
															$body2 = imap_fetchbody($stream, $email_id, 1);
														}
														$body = quoted_printable_decode($body2);
														$name = "";
														foreach ($parts as $part) {
															if ($parts[0]) {
															}
															$i++;
															if (isset($parts[$i])) {
																if ($parts[$i]->ifid == 1) {
																	$id = $parts[$i]->id;
																	$imageid = substr($id, 1, -1);
																	$imageid = "cid:" . $imageid;
																	if ($parts[$i]->ifdparameters == 1) {
																		foreach ($parts[$i]->dparameters as $object) {
																			if (strtolower($object->attribute) == 'filename') {
																				$filename = $object->value;
																			}
																		}
																	}
																	if ($parts[$i]->ifparameters == 1) {
																		foreach ($parts[$i]->parameters as $object) {
																			if (strtolower($object->attribute) == 'name') {
																				$name = $object->value;
																			}
																		}
																	}
																}
																$body = str_replace($imageid, $name, $body);
															}
														}
													}
												}
											}
										}
										if ($this->create_user($emailadd, $username, $subject, $body, $phone, $helptopic, $sla, $system) == true) {
											$thread_id = Ticket_Thread::whereRaw('id = (select max(`id`) from ticket_thread)')->first();
											$thread_id = $thread_id->id;
											if ($this->get_attachment($structure, $stream, $email_id, $thread_id) == true) {
											}
										}
									} else {
									}
								}
							}
						}
						imap_close($stream);
					}
				}
			}
		}
	}
	public function get_attachment($structure, $stream, $email_id, $thread_id) {
		if (isset($structure->parts) && count($structure->parts)) {
			for ($i = 0; $i < count($structure->parts); $i++) {
				$attachments[$i] = array(
					'is_attachment' => false,
					'filename' => '',
					'name' => '',
					'attachment' => '');
				if ($structure->parts[$i]->ifdparameters) {
					foreach ($structure->parts[$i]->dparameters as $object) {
						if (strtolower($object->attribute) == 'filename') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['filename'] = $object->value;
						}
					}
				}
				if ($structure->parts[$i]->ifparameters) {
					foreach ($structure->parts[$i]->parameters as $object) {
						if (strtolower($object->attribute) == 'name') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['name'] = $object->value;
						}
					}
				}
				if ($attachments[$i]['is_attachment']) {
					$attachments[$i]['attachment'] = imap_fetchbody($stream, $email_id, $i + 1);
					if ($structure->parts[$i]->encoding == 3) {
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					} elseif ($structure->parts[$i]->encoding == 4) {
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
			}
			if ($this->save_attcahments($attachments, $thread_id) == true) {
				return true;
			}
		}
	}
	public function save_attcahments($attachments, $thread_id) {
		if (count($attachments) != 0) {
			foreach ($attachments as $at) {
				if ($at['is_attachment'] == 1) {
					$str = str_shuffle('abcdefghijjklmopqrstuvwxyz');
					$filename = $at['filename'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$tmpName = $at['filename'];
					$fp = fopen($tmpName, 'r');
					$content = fread($fp, filesize($tmpName));
					$content2 = file_put_contents($at['filename'], $at['attachment']);
					$filesize = $content2;
					$ticket_Thread = new Ticket_attachments;
					$ticket_Thread->thread_id = $thread_id;
					$ticket_Thread->name = $filename;
					$ticket_Thread->size = $filesize;
					$ticket_Thread->type = $ext;
					$ticket_Thread->content = $fp; 
					$ticket_Thread->save();
				}
			}
		}
		return true;
	}
}
