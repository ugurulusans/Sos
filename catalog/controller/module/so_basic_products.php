<?php
namespace Opencart\Catalog\Controller\Extension\SoEntry\Module;
class SoBasicProducts extends \Opencart\System\Engine\Controller {	
	protected $hash = null;
	public function index($setting) {
		$this->load->language('extension/so_entry/module/so_basic_products','',$this->config->get('config_language'));
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('extension/so_entry/module/so_basic_products');
		$this->load->model('tool/image');
		$this->document->addStyle('extension/so_entry/catalog/view/javascript/so_basic_products/css/style.css');	
		$setting['cart'] = $this->url->link('common/cart|info', 'language=' . $this->config->get('config_language'));

		$setting['add_to_cart'] = $this->url->link('checkout/cart|add', 'language=' . $this->config->get('config_language'));
		$setting['add_to_wishlist'] = $this->url->link('account/wishlist|add', 'language=' . $this->config->get('config_language'));
		$setting['add_to_compare'] = $this->url->link('product/compare|add', 'language=' . $this->config->get('config_language'));
		// Config default 
		$default = array(
			'objlang'				=> $this->language,
			'name' 					=> '',
			'head_name' 			=> array(),
			'action' 				=> '',
			'module_description'	=> array(),
			'disp_title_module'		=> '1',
			'status'				=> '1',
			'class_suffix'			=> '',
			'item_link_target'		=> '_blank',
			
			'layout_theme'			=> 'default',
			
			'nb_column0'			=> '4',
			'nb_column1'			=> '4',
			'nb_column2'			=> '3',
			'nb_column3'			=> '2',
			'nb_column4'			=> '1',
			'categorys'				=> array(),
			'child_category'		=> '1',
			'category_depth'		=> '1',
			'product_sort'			=> 'p.price',
			'product_ordering'		=> 'ASC',
			'limitation'			=> '6',
			'display_title'			=> '1',
			'title_maxlength'		=> '50',
			'display_description'	=> '1',
			'description_maxlength' => '100',
			'display_price'			=> '1',
			'display_add_to_cart'	=> '1',
			'display_wishlist' 		=> '1',
			'display_compare'		=> '1',
			'display_rating'		=> '1',
			'display_sale'			=> '1',
			'display_new'			=> '1',
			'date_day'				=> '7',
			'product_image_num' 	=> '1',
			
			'product_image'			=> '1',
			'product_get_image_data'=> '1',
			'product_get_image_image'=> '1',
			'width'					=> '200',
			'height'				=> '200',
			'product_placeholder_path'		=> 'nophoto.png',
			
			'post_text'				=> '',
			'pre_text'				=> '',
			'use_cache'				=> '0',
			'cache_time'			=> '3600'
		);
		
		$data =  array_merge($default,$setting);//check data empty setting

		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['head_name'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['head_name'], ENT_QUOTES, 'UTF-8');
		}else{
			$data['head_name']  = $setting['head_name'];
		}

		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['custom_url'] = isset($setting['module_description'][$this->config->get('config_language_id')]['custom_url']) ? $setting['module_description'][$this->config->get('config_language_id')]['custom_url'] : '';
		}else{
			$data['custom_url']  = $setting['custom_url'];
		}
		
		if ($data['custom_url'] != '' && substr($data['custom_url'], 0, 7) != 'http://' && substr($data['custom_url'], 0, 8) != 'https://') {
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$base = $this->config->get('config_ssl');
			} else {
				$base = $this->config->get('config_url');
			}
			$data['custom_url'] = $base.'/'.$data['custom_url'];
		}
		
		if (isset($setting['post_text'])) $data['post_text']  = html_entity_decode($setting['post_text'], ENT_QUOTES, 'UTF-8');
		if (isset($setting['pre_text'])) $data['pre_text']  = html_entity_decode($setting['pre_text'], ENT_QUOTES, 'UTF-8');
		$data['theme_directory'] = $this->config->get('theme_default_directory');
		
		// Get Category list
		$str_categorys = self::getCategoryChild($setting);
		// Get Product
		if($str_categorys != ''){
			$data['products']= self::getProducts($setting, $str_categorys);
		}else{
			$data['products'] = self::getProducts($setting);
		}
		// caching
		$use_cache = (int)$setting['use_cache'];
		$cache_time = (int)$setting['cache_time'];
		$folder_cache = DIR_CACHE.'so/BasicProducts/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);
		if (!class_exists('Cache_Lite'))
	    	require_once (DIR_EXTENSION . 'so_entry/system/library/so/basic_products/Cache_Lite/Lite.php');

		$options = array(
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		);
		$Cache_Lite = new \Cache_Lite($options);
		if ($use_cache){
			$cacheid = (object)(md5( serialize(array($this->config->get('config_language'), $this->session->data['currency'], $setting))));
			$_data = $Cache_Lite->get($cacheid);
			if (!$data_view) {
				$data_view = $this->load->view('extension/so_entry/module/so_basic_products/default', $data);
				$Cache_Lite->save($data_view);
				return  $data_view;
			} else {
				return  $data_view;
			}
		}else{
			if(file_exists($folder_cache))
			$Cache_Lite->_cleanDir($folder_cache);
			return $this->load->view('extension/so_entry/module/so_basic_products/default', $data);
		}
	}

	public function getCategoryChild($setting){
		$category_list = array();
		if(!empty($setting['category'])){
			foreach($setting['category'] as $category_item){
				$checkCategory = $this->model_extension_so_entry_module_so_basic_products->checkCategory($category_item);
				if(isset($checkCategory) && $checkCategory != null &&  $checkCategory[0]['status'] == 1 ){
					$category_list[] =  $category_item;
				}
			}
		}else{
			$category_list[] = '';
		}

		if($category_list != null){
			if($setting['child_category']){
				for($i=1; $i<=$setting['category_depth'];$i++){
					foreach ($category_list as $categorys){
						$filter_data = array(
							'category_id'  => $categorys,
						);
						$categoryss = $this->model_extension_so_entry_module_so_basic_products->getCategories_son($filter_data);
						foreach ($categoryss as $category){
							$category_list[]  = $category['category_id'];
						}
					}
				}
			}
			$category_list = array_unique($category_list);
		}
		$str_categorys = implode(",",$category_list);
		return $str_categorys;
	}
	
	public function getProducts($setting, $str_categorys=null){
		if ($str_categorys) {
			$filter_data = array(
				'filter_category_id'  => $str_categorys,
				'sort'         => $setting['product_sort'],
				'order'        => $setting['product_ordering'],
				'limit'        => $setting['limitation'] ,
				'start'        => 0
			);
		}
		else {
			$filter_data = array(
				'sort'         => $setting['product_sort'],
				'order'        => $setting['product_ordering'],
				'limit'        => $setting['limitation'],
				'start'        => 0
			);
		}
		$data['products'] = array();
		$products_arr = $this->model_extension_so_entry_module_so_basic_products->getProducts_basic_products($filter_data);
		
		foreach($products_arr as $product_info){
			// get image
			$product_image = $this->model_extension_so_entry_module_so_basic_products->getImageProduct_basic_products($product_info['product_id']);
			$product_image_first = array_shift($product_image);
			$image2 = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
			if($product_image_first != null){
				$image2 = $this->model_tool_image->resize($product_image_first['image'], $setting['width'], $setting['height']);
			}
			if ($product_info['image'] && $setting['product_get_image_data'] == 1) {
				$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
			}elseif($product_image_first['image'] && $setting['product_get_image_image'] == 1){
				$image = $this->model_tool_image->resize($product_image_first['image'], $setting['width'], $setting['height']);
			} else {
				$url = file_exists("image/so_basic_products/images/".$setting['product_placeholder_path']);
				if ($url) {
					$image_name = "so_basic_products/images/".$setting['product_placeholder_path'];
				} else {
					$image_name = "no_image.png";
				}
				$image = $this->model_tool_image->resize($image_name, $setting['width'], $setting['height']);
			}
			// Name
			$name = $product_info['name'];
			$name_maxlength = ((strlen($product_info['name']) > $setting['title_maxlength'] && $setting['title_maxlength'] !=0)  ? substr(strip_tags(html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')), 0, $setting['title_maxlength']) . '..' : $product_info['name']);
			// Description
			$description = ((strlen($product_info['description']) > $setting['description_maxlength'] && $setting['description_maxlength'] != 0) ? substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['description_maxlength']) . '..' : $product_info['description']);
			
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$product_info['special']) {
				$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = $product_info['rating'];
			} else {
				$rating = false;
			}
			// Product New
			$datetimeNow = new \DateTime();
			$datetimeCreate = new \DateTime($product_info['date_available']);
			$interval = $datetimeNow->diff($datetimeCreate);
			$dateDay = $interval->format('%a');
			$productNew = ($dateDay <= $setting['date_day'] ? 1 : 0);
				
			$data['products'][] = array(
				'product_id'  		=> $product_info['product_id'],
				'thumb'       		=> $image,
				'thumb2'       		=> $image2,
				'name'        		=> $product_info['name'],
				'name_maxlength'	=> $name_maxlength,
				'description' 		=> $product_info['description'],
				'description_maxlength'=> $description,
				'price'       		=> $price,
				'special'     		=> $special,
				'productNew'		=> $productNew,
				'tax'         		=> $tax,
				'rating'      		=> $rating,
				'href'        		=> $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_info['product_id'])
			);
		}
		return $data['products'];
	}
}