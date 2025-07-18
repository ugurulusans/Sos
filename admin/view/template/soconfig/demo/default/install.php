<?php
	//Delete & Create Data Table
	$sql = "Delete from ".DB_PREFIX."setting WHERE `code` IN ('theme_default','so_sociallogin','so_onepagecheckout','so_google_amp')" ; $this->db->query($sql);
	$sql = "Delete from ".DB_PREFIX."soconfig"; $this->db->query($sql);
	
	$sql = "Delete from ".DB_PREFIX."layout where `layout_id` = 32"; $this->db->query($sql);
	$sql = "Delete from ".DB_PREFIX."layout_module"; $this->db->query($sql);
	$sql = "Delete from ".DB_PREFIX."layout_route where `layout_id` = 32"; $this->db->query($sql);
		

	$sql = "Delete from ".DB_PREFIX."module where `code` REGEXP 'so|bestseller'" ; $this->db->query($sql);
	//$sql = "Delete from ".DB_PREFIX."extension where `code` REGEXP 'so|simple_blog'"; $this->db->query($sql);
	
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."newsletter_custom_popup`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."mega_menu`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_homeslider`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_homeslider_description`"; $this->db->query($sql);
	//$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."simple_blog_category`"; $this->db->query($sql);
	//$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."simple_blog_category_description`"; $this->db->query($sql);

	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_countdown_popup`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_countdown_popup_description`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_countdown_popup_store`"; $this->db->query($sql);
	
	$product_lookbook_id = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'lookbook_id'");
	if (!$product_lookbook_id->num_rows) {
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product ADD lookbook_id INT COLLATE utf8_bin NOT NULL AFTER `date_modified`");
	}
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_lookbook`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_lookbook_slide`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_lookbook_slide_items`"; $this->db->query($sql);
	
	
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_engine`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_make`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_model`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_product_to_mmy`"; $this->db->query($sql);
	$sql ="DROP TABLE IF EXISTS `".DB_PREFIX."so_year`"; $this->db->query($sql);	
	
	$sql="CREATE TABLE `". DB_PREFIX."newsletter_custom_popup` (`news_id` int(11) NOT NULL AUTO_INCREMENT,`news_email` varchar(255) NOT NULL, `news_name` varchar(255) NOT NULL, `news_phone` varchar(255) NOT NULL, `txtservice` varchar(255) NOT NULL , `news_create_date` datetime NOT NULL,`news_status` tinyint(1) NOT NULL,`confirm_mail` tinyint(1) NOT NULL,PRIMARY KEY (`news_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"; $this->db->query($sql);
	$sql="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."mega_menu` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`id_old` int(11) NOT NULL,`parent_id` int(11) NOT NULL,`rang` int(11) NOT NULL,`icon` varchar(255) NOT NULL DEFAULT '',`name` text,`type_link` int(11),`module_id` int(11),`link` text,`description` text,`new_window` int(11) NOT NULL DEFAULT '0',`status` int(11) NOT NULL DEFAULT '0',`position` int(11) NOT NULL DEFAULT '0',`submenu_width` text,`submenu_type` int(11) NOT NULL DEFAULT '0',`content_width` int(11) NOT NULL DEFAULT '12',`content_type` int(11) NOT NULL DEFAULT '0',`content` text,`label_item` varchar(255) NOT NULL DEFAULT '',`icon_font` varchar(255) NOT NULL DEFAULT '',`class_menu` varchar(255),PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"; $this->db->query($sql);
	$sql="CREATE TABLE `".DB_PREFIX."so_homeslider` (`id` int(11) NOT NULL AUTO_INCREMENT, `module_id` int(11) NOT NULL, `url` varchar(255) NOT NULL, `position` int(11) NOT NULL, `image` varchar(255) NOT NULL, `status` tinyint(1) NOT NULL, PRIMARY KEY (`id`), KEY `id` (`id`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"; $this->db->query($sql);
	$sql="CREATE TABLE `".DB_PREFIX."so_homeslider_description` (`homeslider_id` int(11) NOT NULL AUTO_INCREMENT,`language_id` int(11) NOT NULL,`title` varchar(255) NOT NULL,`caption` varchar(255) NOT NULL,`description` text NOT NULL,`image_lang` varchar(255) NOT NULL,`url_lang` varchar(255) NOT NULL,`status_lang` tinyint(1) NOT NULL,`jusv` VARCHAR(11) NOT NULL,PRIMARY KEY (`homeslider_id`,`language_id`),KEY `title` (`title`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"; $this->db->query($sql);	
    //$sql="CREATE TABLE IF NOT EXISTS ". DB_PREFIX . "simple_blog_category( `simple_blog_category_id` int(16) NOT NULL AUTO_INCREMENT, `image` text NOT NULL, `parent_id` int(16) NOT NULL, `top` tinyint(1) NOT NULL, `blog_category_column` int(16) NOT NULL, `external_link` text NOT NULL, `column` int(8) NOT NULL, `sort_order` int(8) NOT NULL, `status` tinyint(1) NOT NULL, `date_added` datetime NOT NULL, `date_modified` datetime NOT NULL, PRIMARY KEY (`simple_blog_category_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"; $this->db->query($sql);
	//$sql="CREATE TABLE IF NOT EXISTS ". DB_PREFIX . "simple_blog_category_description( `simple_blog_category_description_id` int(16) NOT NULL AUTO_INCREMENT, `simple_blog_category_id` int(16) NOT NULL, `language_id` int(16) NOT NULL, `name` varchar(256) NOT NULL, `description` text NOT NULL, `meta_description` varchar(256) NOT NULL, `meta_keyword` varchar(256) NOT NULL, PRIMARY KEY (`simple_blog_category_description_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"; $this->db->query($sql);
	
	$sql="CREATE TABLE `".DB_PREFIX."so_countdown_popup` (
	  `id` int(11) UNSIGNED NOT NULL,
	  `status` tinyint(1) NOT NULL,
	  `name` varchar(255) NOT NULL,
	  `priority` int(11) NOT NULL,
	  `width` int(11) NOT NULL,
	  `height` int(11) NOT NULL,
	  `opacity` char(3) NOT NULL,
	  `display_countdown` tinyint(1) NOT NULL,
	  `date_start` datetime NOT NULL,
	  `date_expire` datetime NOT NULL,
	  `image` varchar(500) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_countdown_popup_description` (
	  `language_id` int(11) NOT NULL,
	  `popup_id` int(11) NOT NULL,
	  `description` text NOT NULL,
	  `heading_title` varchar(500) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_countdown_popup_store` (
	  `popup_id` int(11) NOT NULL,
	  `store_id` int(11) NOT NULL,
	  `link` varchar(500) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);
	
	$sql="CREATE TABLE `".DB_PREFIX."so_engine` (
	  `engine_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `model_id` int(11) NOT NULL,
	  `make_id` int(11) NOT NULL,
	  `engine_name` varchar(255) NOT NULL,
	  PRIMARY KEY (`engine_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_make` (
	  `make_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  		`make_name` varchar(255) NOT NULL,
		PRIMARY KEY (`make_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_model` (
	  `model_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `make_id` int(11) NOT NULL,
	  `model_name` varchar(255) NOT NULL,
	  PRIMARY KEY (`model_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_product_to_mmy` (
	  `product_mmy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `product_id` int(11) NOT NULL,
	  `make_id` int(11) NOT NULL,
	  `model_id` int(11) NOT NULL,
	  `engine_id` int(11) NOT NULL,
	  `year_id` int(11) NOT NULL,
	  PRIMARY KEY (`product_mmy_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_year` (
	  `year_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `engine_id` int(11) NOT NULL,
	  `model_id` int(11) NOT NULL,
	  `make_id` int(11) NOT NULL,
	  `year_name` varchar(255) NOT NULL,
	  PRIMARY KEY (`year_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);	
	
	$sql="CREATE TABLE `".DB_PREFIX."so_lookbook` (
	  `lookbook_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) NOT NULL,
	  `image` varchar(255) NOT NULL,
	  `pins` mediumtext NOT NULL,
	  `status` smallint(6) NOT NULL,
	  PRIMARY KEY (`lookbook_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_lookbook_slide` (
	  `slide_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(255) NOT NULL,
	  `custom_class` varchar(255) NOT NULL,
	  `auto_play` smallint(6) NOT NULL,
	  `auto_play_timeout` varchar(255) NOT NULL,
	  `stop_auto` smallint(6) NOT NULL,
	  `navigation` smallint(6) NOT NULL,
	  `pagination` smallint(6) NOT NULL,
	  `loop` smallint(6) NOT NULL,
	  `next_image` varchar(255) NOT NULL,
	  `prev_image` varchar(255) NOT NULL,
	  `status` smallint(6) NOT NULL,
	  PRIMARY KEY (`slide_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	$sql="CREATE TABLE `".DB_PREFIX."so_lookbook_slide_items` (
	  `item_id` INT(11) NOT NULL AUTO_INCREMENT,
	  `slide_id` int(11) NOT NULL,
	  `lookbook_id` int(11) NOT NULL,
	  `position` int(11) NOT NULL,
	  PRIMARY KEY (`item_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"; $this->db->query($sql);

	
	//Inset Data Table - themes, soconfig, module, layout_module
	$settings_sql = DIR_EXTENSION.'so_entry/admin/view/template/soconfig/demo/default/themes.sql';	
	if( file_exists($settings_sql) ){
		$query_setting = loo_parse_queries($settings_sql,DB_PREFIX);
		foreach ($query_setting as $query) {
			$this->db->query($query);
		}
	} 
	
	/**
	 * Function loo_parse_queries
	 * Performs a query on the database
	 *
	 * Parameters:
	 *     ($db) 			- 
	 *     ($sql_file) 		- Source File SQL
	 *     ($prefix) 		- Prefix of DB
	 */
	function loo_parse_queries($sql_file,$prefix) {
		$contents = file_get_contents($sql_file);
		$contents 	= preg_replace('/(?<=t);(?=\n)/', "{{semicolon_in_text}}", $contents);
		$statements = preg_split('/;(?=\n)/', $contents);
		
		$queries = array();
		foreach ($statements as $query) {
			if (trim($query) != '') {
				$query = str_replace("{{semicolon_in_text}}", ";", $query);
				//apply db prefix parametr
				preg_match("/\{table_prefix}\w*/i", $query, $matches);
				$table_name = str_replace('{table_prefix}', DB_PREFIX, $matches[0]);
				if ( !empty($table_name) ) {
					$query = str_replace(array($matches[0], 'key = '), array($table_name, '`key` = '), $query);
				}
				$queries[] = $query;
			}
		}
		
		return $queries ;
		
	}
	
	
?>