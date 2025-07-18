<?php
namespace Opencart\Catalog\Model\Extension\SoEntry\Module;
class SoNewletterCustomPopup extends \Opencart\System\Engine\Model {
	public function subscribes($data) {
		$res = $this->db->query("select * from ". DB_PREFIX ."newsletter_custom_popup where news_email='".$data['email']."'");
		if($res->num_rows == 1)
		{
			return "1";
		}
		else
		{
			$name = 'null';
			$phone = 'null';
			$txtservice = 'null';

			if(isset($data['name'])) {
				$name = $data['name'];
			}
			if(isset($data['phone'])) {
				$phone = $data['phone'];
			}
			if(isset($data['txtservice'])) {
				$txtservice = $data['txtservice'];
			}
			
			if($this->db->query("INSERT INTO " . DB_PREFIX . "newsletter_custom_popup(news_email,news_name,news_phone,txtservice,news_create_date,news_status) values ('".$data['email']."' ,'".$name."' ,'".$phone."' , '".$txtservice."' , '".$data['createdate']."' , '".$data['status']."')"))
			{
				return "2";
			}
			else
			{
				return "3";
			}
		}
	}
		
}