<?php
namespace Opencart\Admin\Model\Extension\SoEntry\Module;
class SoHomeSlider extends \Opencart\System\Engine\Model {
	
	public function getModuleId() {
		$sql = " SHOW TABLE STATUS LIKE '" . DB_PREFIX . "module'" ;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function getSlideId() {
		$sql = " SHOW TABLE STATUS LIKE '" . DB_PREFIX . "so_homeslider'" ;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function checkInstall(){

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."so_homeslider'";
		$query = $this->db->query( $sql );
		if( count($query->rows) <=0 ){ 
			$this->createTables();	
		}
	}
	protected function createTables(){
		$sql = array();
		$sql[] = "
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."so_homeslider` (
				   	  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `module_id` int(11) NOT NULL,
					  `url` varchar(255) NOT NULL,
					  `position` int(11) NOT NULL,
					  `image` varchar(255) NOT NULL,
					  `status` tinyint(1) NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `id` (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

		";
		$sql[] = "
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."so_homeslider_description` (
				   	  `homeslider_id` int(11) NOT NULL AUTO_INCREMENT,
					  `language_id` int(11) NOT NULL,
					  `title` varchar(255) NOT NULL,
					  `caption` varchar(255) NOT NULL,
					  `description` text NOT NULL,
					  `image_lang` varchar(255) NOT NULL,
					  `url_lang` varchar(255) NOT NULL,
					  `status_lang` tinyint(1) NOT NULL,
					  `jusv` VARCHAR(11) NOT NULL,
					   PRIMARY KEY (`homeslider_id`,`language_id`),
					   KEY `title` (`title`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

		";

		foreach( $sql as $q ){
			$query = $this->db->query( $q );
		}
		
	}
	
	/**
	 */

	public function getListSliderGroups($module_id){
		
		$query = "SELECT * FROM " . DB_PREFIX . "so_homeslider WHERE module_id = ".$module_id." ORDER BY position";
		$query = $this->db->query( $query );
		$row = $query->rows;
		
		$data = array();
		foreach ($query->rows as $item) {
			$sql = "SELECT * FROM " . DB_PREFIX . "so_homeslider_description WHERE jusv = ".$item['id']." ";
			$sql = $this->db->query( $sql );
			foreach ($sql->rows as $value) {
				array_push($data,$value);
			}
            
		}

	 	return $data;
	}
	/**
	 *
	 */
	 public function getSliderById( $id ){
		 
		$query = ' SELECT hd.*,h.* FROM '. DB_PREFIX . 'so_homeslider_description hd LEFT JOIN '. DB_PREFIX . 'so_homeslider h ON (h.id = hd.jusv) ';
		$query .= ' WHERE hd.jusv='.(int)$id;

		$query = $this->db->query($query ); 
		foreach($query->rows as $item)
		{
			$row[$item['language_id']] = $item;
		}
	 	return $row;		 
		 
	}
	
	/**
	 * Get Slide by id slide and language id
	 */
	 public function getSliderByIdLanguage( $id , $language_id){
		$query = ' SELECT hd.*,h.* FROM '. DB_PREFIX . 'so_homeslider_description hd LEFT JOIN '. DB_PREFIX . 'so_homeslider h ON (h.id = hd.jusv) ';
		$query .= ' WHERE hd.jusv='.(int)$id.' AND hd.language_id ='.(int)$language_id;

		$query = $this->db->query($query ); 
		if(count($query->rows) > 0){
			return true;
		}else{
			return false;	
		}
	}
	
	public function addSlide($data) {
		

		$query1 = ' SELECT MAX(position) AS maxposition FROM '. DB_PREFIX . "so_homeslider";
		$query1 = $this->db->query($query1);

		
		foreach($query1->rows as $item)
		{
			$position = $item['maxposition'] +1;
		}
			
		$this->db->query("INSERT INTO " . DB_PREFIX . "so_homeslider SET module_id = '" . $this->db->escape($data['moduleid']) . "', position = '" . $this->db->escape($position) . "' ");

		$slide_id = $this->db->getLastId();	

		foreach ($data['slide_description'] as $language_id => $value) {
		
			$this->db->query("INSERT INTO " . DB_PREFIX . "so_homeslider_description SET homeslider_id = '" . (int)$slide_id . "', jusv = '" . (int)$slide_id . "', url_lang = '" . $this->db->escape($value['url_lang']) . "', image_lang = '" . $this->db->escape($value['image_lang']) . "', status_lang = '" . $this->db->escape($value['status_lang']) . "' ,  language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['slide_title']) . "', description = '" . $this->db->escape($value['slide_desciption']) . "', caption = '" . $this->db->escape($value['slide_caption']) . "'");
		}
		return $slide_id;		
	}
	public function editSlide($id,$data) {

		foreach ($data['slide_description'] as $language_id => $value) {
			if($this->getSliderByIdLanguage($id,$language_id)){
				$this->db->query("UPDATE " . DB_PREFIX . "so_homeslider_description SET url_lang = '" . $this->db->escape($value['url_lang']) . "', image_lang = '" . $this->db->escape($value['image_lang']) . "', status_lang = '" . $this->db->escape($value['status_lang']) . "' , title = '" . $this->db->escape($value['slide_title']) . "', description = '" . $this->db->escape($value['slide_desciption']) . "', caption = '" . $this->db->escape($value['slide_caption']) . "' WHERE jusv = '" . (int)$id . "' AND language_id = '" . (int)$language_id . "'");
			}else{
				$this->db->query("INSERT INTO " . DB_PREFIX . "so_homeslider_description SET url_lang = '" . $this->db->escape($value['url_lang']) . "', image_lang = '" . $this->db->escape($value['image_lang']) . "', status_lang = '" . $this->db->escape($value['status_lang']) . "' , jusv = '" . (int)$id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['slide_title']) . "', description = '" . $this->db->escape($value['slide_desciption']) . "', caption = '" . $this->db->escape($value['slide_caption']) . "'");
			}
		}
		return $id;
	}
	public function deleteSlide($slide_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "so_homeslider_description WHERE jusv = '" . (int)$slide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "so_homeslider WHERE id = '" . (int)$slide_id . "'");
		$this->cache->delete('category');
	}
	public function deleteAllSlide($module_id) {
		$query = "SELECT * FROM " . DB_PREFIX . "so_homeslider WHERE module_id = ".$module_id;
		$query = $this->db->query( $query );
		foreach($query->rows as $item){
			$this->db->query("DELETE FROM " . DB_PREFIX . "so_homeslider_description WHERE jusv = '" . $item['id'] . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "so_homeslider WHERE id = '" . $item['id'] . "'");
		}
		$this->cache->delete('category');
	}
	public function updatePositionSlide($data) {
		$data = explode( ',' ,$data);
		
		foreach($data as $position => $item){
			$item = str_replace('slides_', '', $item);
			$this->db->query("UPDATE " . DB_PREFIX . "so_homeslider SET position = '" . $position . "' WHERE id = '".$item."'");
		}
		return $data;
	}
}
?>