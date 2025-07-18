<?php
namespace Opencart\Catalog\Controller\Extension\SoEntry\Event;

require_once (DIR_EXTENSION.'so_entry/admin/view/template/soconfig/class/soconfig.php');

class SoSomobile extends \Opencart\System\Engine\Controller {
	
	public function so_entry_home_before(&$route, &$data){}
	public function so_entry_header_before(&$route, &$data){}
	public function so_entry_footer_before(&$route, &$data){}
	public function so_entry_search_before(&$route, &$data){}
	public function so_entry_language_before(&$route, &$data){}
	public function so_entry_currency_before(&$route, &$data){}
	public function so_entry_product_before(&$route, &$data){}	
	public function so_entry_category_before(&$route, &$data){}
	public function so_entry_information_before(&$route, &$data){}
	public function so_entry_contact_before(&$route, &$data){}
	public function so_entry_sitemap_before(&$route, &$data){}
	
}