<?php
namespace Opencart\Admin\Model\Extension\SoEntry\Module\Soconfig;
 class Setting extends \Opencart\System\Engine\Model {
	 
	 
	public function createTableSoconfig(){
		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'soconfig` (
          id int(11) auto_increment,
          `store_id` int(11) NOT NULL DEFAULT 0,
          `key` varchar(255) NOT NULL,
          `value` mediumtext NOT NULL,
          `serialized` tinyint(1) NOT NULL,
		   PRIMARY KEY(id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}
	
	public function getSetting($store_id = 0) {
		$setting_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "soconfig WHERE store_id = '" . (int)$store_id . "' AND `key` != 'mobile_general'");
		foreach ($query->rows as $result) {
				$setting_data[$result['key']] = json_decode($result['value'], true);
		}

		return $setting_data;
	}
	

	public function editSetting( $data, $store_id = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "soconfig` WHERE store_id = '" . (int)$store_id . "' AND `key` != 'mobile_general' ");
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "soconfig SET store_id = '" . (int)$store_id . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
			}
		}
	}
	
	
	
	
	public function deleteSetting() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "soconfig`");
	}
	

	public function editMobile($data, $store_id = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "soconfig` WHERE store_id = '" . (int)$store_id . "' AND `key` = 'mobile_general'");
		//var_dump($data);die();
		foreach ($data as $key => $value) {
			if (is_array($value) && $key =='mobile_general' ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "soconfig SET store_id = '". (int)$store_id."', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");

			}
		}
	}

	public function getMobile( $store_id = 0) {
		$setting_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "soconfig WHERE store_id = '" . (int)$store_id . "' AND `key` = 'mobile_general'");
		foreach ($query->rows as $result) {
			$setting_data[$result['key']] = json_decode($result['value'], true);

		}
		return $setting_data;
	}

	public function addEvent(string $code, string $description, string $trigger, string $action, bool $status = true, int $sort_order = 0): int { 
	    $this->db->query("INSERT INTO " . DB_PREFIX . "event SET `code` = '" . $this->db->escape($code) . "', description = '" . $this->db->escape($description) . "', `trigger` = '" . $this->db->escape($trigger) . "', `action` = '" . $this->db->escape($action) . "', `status` = '" . (int)$status . "', `sort_order` = '" . (int)$sort_order . "'"); 
	    return $this->db->getLastId(); 
	}	
	
}
