<?php

require_once (DIR_STORAGE.'vendor/scssphp/scssphp/scss.inc.php');
require_once('soconfig_settings.php');
require_once('soconfig_minifier.php');
require_once('device.php');

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Server;
use ScssPhp\ScssPhp\ValueConverter;

class ClassSoconfig extends \stdClass {
    public $css_files;
    public $js_files;
    public $tools;
    private $settings = array();
	private $db;
	private $store_id;
    public $oc_config;
    private $device;
	
    public $minifier;
    public $cache;
    public $request;
    public $url;
    public $themes;
   
    public function __construct($registry) {
		
		$this->config = new SoconfigSettings($registry);
		$this->minifier = new SoconfigMinifier($registry);
		$this->device = new Device($registry);
		
		$this->css_files['header'] = array();
		$this->css_files['footer'] = array();
		$this->js_files['header'] = array();
		$this->js_files['footer'] = array();
		
		$this->db = $registry->get('db');
		$this->oc_config 	= $registry->get('config');
		$this->request 		= $registry->get('request');
		$this->url 			= $registry->get('url');
		
		if(!$this->is_admin()) {
			if (!file_exists(DIR_TEMPLATE.$this->oc_config->get('theme_default_directory')))  $this->themes = 'default';
			else $this->themes = $this->oc_config->get('theme_default_directory');
		}else{
			if (!file_exists(DIR_TEMPLATE_FRONT.$this->oc_config->get('theme_default_directory')))  $this->themes = 'default';
			else $this->themes = $this->oc_config->get('theme_default_directory');
		}
		
		$this->language = $registry->get('language');
		$this->store_id = $this->oc_config->get('config_store_id');
		$this->colorScheme_url   = (isset($_GET['scheme']))? $_GET['scheme'] :  $this->get_settings('themecolor'); 
		
		
    }
    
   
    public function get_settings($name,$default = null){
		return $this->config->get_cfg($name,$default);    
    }
    
	public function getmobile_settings($name,$default = null){
		return $this->config->getmobile_cfg($name,$default);    
    }
	
    public function set_settings($name,$value){
		$this->config->set_cfg($name,$value);
    }
    
    public function addJs($js_file,$position = 'header'){
    
      if (!in_array($js_file,$this->js_files[$position])) $this->js_files[$position][] = $js_file; 
    }
    
    public function addCss($css_file,$position = 'header'){
		if (!in_array($css_file,$this->css_files[$position])) $this->css_files[$position][] = $css_file; 
		
    }
    
  
    public function array_insert($array,$index,$val=null){
       $size = count($array); //because I am going to use this more than one time
       if (!is_int($index) || $index < 0 || $index > $size){
           return -1;
       } else{
           $temp   = array_slice($array, 0, $index);
		   if(isset($val[0])){
			   $temp[] = $val[0];
				return array_merge($temp, array_slice($array, $index, $size));
		   }else{
				return array_merge($temp, array_slice($array, $index, $size));
		   }
           
       }
    }
	
	public function js_out($JSExcludes = '',$position = 'header'){

		$optimizeJSExclude = array();	
		if(isset($JSExcludes))foreach ($JSExcludes as $JSExclude) $optimizeJSExclude[] = $JSExclude['namecss'];
		$js_files_to_out = isset($this->js_files[$position]) ? $this->js_files[$position] : array();
		if (!empty($js_files_to_out)) {
			foreach($js_files_to_out as $key => $item) {
				if(is_array($item)) {
					$js_files_to_out[$key] = $item['href'];
				}
			}			
		}

		if ($this->get_settings('jsminify','0') == 1){
			

            $optimizeJSExclude[] = 'catalog/view/javascript/jquery/datetimepicker/moment.min.js';
			$optimizeJSExclude[] = 'catalog/view/javascript/jquery/datetimepicker/moment-with-locales.min.js';
			$optimizeJSExclude[] = 'catalog/view/javascript/jquery/datetimepicker/daterangepicker.js';
			$optimizeJSExclude[] = 'extension/so_entry/catalog/view/javascript/so_html_content/js/script.js';
			$optimizeJSExclude[] = 'extension/so_entry/catalog/view/javascript/soconfig/js/jquery.elevateZoom-3.0.8.min.js';

			if(empty($JSExclude) && $optimizeJSExclude !== array_intersect( $js_files_to_out, $optimizeJSExclude)){
				
				$combined_js = $this->minifier->get_compliled_js_file_path($js_files_to_out);
				echo '<script src="'.SOCONFIG_CACHE_DIR.'/minify/'.$combined_js.'"></script>'."\n";
			}else{
				
				if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product') $optimizeJSExclude[]='extension/so_entry/catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js';
				$js_files_to_out = 	array_diff( $js_files_to_out, $optimizeJSExclude) ;
				$combined_js = $this->minifier->get_compliled_js_file_path($js_files_to_out);
				echo '<script src="'.SOCONFIG_CACHE_DIR.'/minify/'.$combined_js.'"></script>'."\n";
				foreach($optimizeJSExclude as $file){
					if(!empty($file))echo '<script src="'.$file.'"></script>'."\n";
				}
			}
		}else{		
			foreach($js_files_to_out as $file) 
			if(is_array($file))
				echo '<script src="'.$file['href'].'"></script>'."\n";
			else	
			    echo '<script src="'.$file.'"></script>'."\n";
		}
    }
	
    public function css_out($CSSExcludes = '',$position = 'header'){
		$optimizeCSSExclude = array();
		if(isset($CSSExcludes)) foreach ($CSSExcludes as $CSSExclude)$optimizeCSSExclude[] = $CSSExclude['namecss'];
		$css_files_to_out = isset($this->css_files[$position]) ? $this->css_files[$position] : array();

		if ($this->get_settings('cssminify','0') == 1){
			if(empty($CSSExclude) && $optimizeCSSExclude !== array_intersect( $this->css_files[$position], $optimizeCSSExclude)){
				$combined_css_style = $this->minifier->get_compliled_css_file_path($css_files_to_out);
				echo '<link rel="stylesheet" href="'.SOCONFIG_CACHE_DIR.'/minify/'.$combined_css_style.'">'."\n";
			}else{

				$css_files_to_out = 	array_diff( $this->css_files[$position], $optimizeCSSExclude) ;
				$combined_css_style = $this->minifier->get_compliled_css_file_path($css_files_to_out);
				echo '<link rel="stylesheet" href="'.SOCONFIG_CACHE_DIR.'/minify/'.$combined_css_style.'">'."\n";
				foreach($optimizeCSSExclude as $file){
					if(!empty($file))echo '<link rel="stylesheet" href="'.$file.'">'."\n";
					
				}
				
			}
	
		}else{
			foreach($css_files_to_out as $file)echo '<link rel="stylesheet" href="'.$file.'">'."\n";
			
		}
    }
    
    public function get_images_path(){
		$path = $this->oc_config->get('config_secure') ? HTTP_SERVER : HTTPS_SERVER;
		return $path.'image/';
    }
    
  
    public function is_admin(){
		return (defined('HTTP_CATALOG'));
    }
    
    public function is_home_page(){
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
		return ($route == 'common/home'); 
    }
	
	public function is_mobile_page(){
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'extension/so_entry/mobile/home';
		return ($route == 'extension/so_entry/mobile/home'); 
    }
	
	public function get_route(){
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
		return $route; 
    }
	
	public function decode_entities($string){
		return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }
	/**
	 * Function get_logo
	 * Change color to logo
	 *
	 * Parameters:
	 *     (name) - 
	 */
	public function get_logo(){
	
		$config_logo = $this->oc_config->get('config_logo');
		$href_home = $this->url->link('common/home', 'language=' . $this->oc_config->get('config_language'));
		
		$titleLogo = $this->oc_config->get('config_name');
		if ($this->request->server['HTTPS']) $server = $this->oc_config->get('config_ssl');
		else $server = $this->oc_config->get('config_url');
		
		if(!empty($config_logo)){
			$logo = $server.'image/'.$this->oc_config->get('config_logo');
		}else{
			$colorScheme_cache   = $this->colorScheme_url;
			if (is_file(DIR_TEMPLATE.$this->themes.'/images/styling/'.$colorScheme_cache.'/logo.png')) {
				$logo = $server . URL_TEMPLATE.$this->themes.'/images/styling/'.$colorScheme_cache.'/logo.png';
			} else {
				$logo = '';
			}
		}
		?>
			<?php if ($logo) { ?>
			   <a href="<?php echo $href_home; ?>"><img class="lazyload" data-sizes="auto" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="<?php echo $logo; ?>" title="<?php echo $titleLogo; ?>" alt="<?php echo $titleLogo; ?>" /></a>
			   <?php } else { ?>
			   <h1><a href="<?php echo $href_home; ?>"><?php echo $titleLogo; ?></a></h1>
			<?php } ?> 
        <?php 
	}
	
	/**
	 * Function get_logoMobile
	 * Change color to logo
	 *
	 * Parameters:
	 *     (name) - 
	 */
	public function get_logoMobile(){
		$config_logoMobile =  $this->get_settings('logomobile');
		$config_logo = $this->oc_config->get('config_logo');
		$href_home = $this->url->link('extension/so_entry/mobile/home');
		
		$titleLogo = $this->oc_config->get('config_name');
		if ($this->request->server['HTTPS']) $server = $this->oc_config->get('config_ssl');
		else $server = $this->oc_config->get('config_url');
		
		if(isset($this->request->get['logo'])){
			$logo = $server.'image/catalog/demo/logo/'.$this->request->get['logo'].'.png';
		}else{
			if(!empty($config_logoMobile)) $logo = $server.'image/'.$config_logoMobile;
			else $logo = $server.'image/'.$config_logo;
		}
		
		?>
		
		<?php if (!empty($config_logo) || !empty($config_logoMobile) ) { ?>
		   <a href="<?php echo $href_home; ?>"><img src="<?php echo $logo; ?>" title="<?php echo $titleLogo; ?>" alt="<?php echo $titleLogo; ?>" /></a>
		 <?php } else { ?>
			<a href="<?php echo $href_home; ?>"><?php echo $titleLogo; ?></a>
		<?php } ?> 
		
		<?php
	}
	
	
	public function scss_compass($colorHex = null,$colorNameHex = null,$typelayout = null,$onoff_muticolor = null,$listColor = null){
		
		$colorName   = $this->get_settings('themecolor');
		$theme_color = $this->get_settings('theme_color');

		if(!$this->is_admin()) {
			$typelayout   = $this->get_settings('typelayout');
			$typeheader   = $this->get_settings('typeheader');
			$typefooter   = $this->get_settings('typefooter');
		}else{
		 	$typeheader   ='';
		 	$typefooter   ='';
		}

		$responsive  = $this->get_settings('layouts');
		$direction   = $this->language->get('direction');


		
		$resCssName = 'responsive.css';
		$resCssNameRTL = 'responsive-rtl.css';
		$ie9CssName = 'ie9-and-up.css';
		$themeCssHeader   	= 'header/header'.$typeheader.'.css';
		$themeCssHeaderRTL  = 'header/header'.$typeheader.'-rtl.css';
		$themeCssFooter   	= 'footer/footer'.$typefooter.'.css';
		$themeCssFooterRTL  = 'footer/footer'.$typefooter.'-rtl.css';
		$imagePath	= 'extension/so_entry/catalog/view/template/images/styling/'.$colorName ? $colorName: 'default';
		$themeColors  = ($colorHex != null)? $colorHex : $theme_color ;

		$themeNameColors = ($this->is_admin())? $colorNameHex : $this->get_settings('name_color') ;

		//Create Name Css of Frontend
		if(isset($colorName)):
			$themeCssName = 'layout'.$typelayout.'/'.$colorName.'.css';
			$themeCssNameRTL = 'layout'.$typelayout.'/'.$colorName.'-rtl.css';
		else:
			$themeCssName = 'theme.css';
			$themeCssNameRTL = 'theme.css';
		endif;
		
		
		//Check compass find path  Compile file (css,sass)
		$scssDir = DIR_EXTENSION.'so_entry/catalog/view/template/sass/';
		$cssDir  = DIR_EXTENSION.'so_entry/catalog/view/template/css/';
			
		$scss = new Compiler();
			
		$cssFormat = $this->get_settings('scssformat') ? $this->get_settings('scssformat') :('Nested');
	
		//$scss->setOutputStyle('ScssPhp\ScssPhp\Formatter::' .$cssFormat);
		$scss->addImportPath($scssDir);

		
		//Check SCSS Compile Enable
		if( !$this->is_admin() && $this->get_settings('scsscompile')) {
			
			//1.Front end SCSS Compile
			$scss->replaceVariables(array(
				'$linkColor' => ValueConverter::parseValue($themeColors),
				'$imagePath' => ValueConverter::parseValue($imagePath),
				'$dir' => ValueConverter::parseValue($direction),
			));
			
			//1.SCSS Compile file ie9-and-up
			if ( $this->beenEditScss('ie9-and-up','ie9-and-up')) {
				$ie_css 	= $scss->compileString('@import "ie9-and-up.scss"')->getCss();
				file_put_contents($cssDir.$ie9CssName, $ie_css);
			};
			
			//2.SCSS Compile file responsive
			if ($this->beenEditScss('responsive','responsive') || $this->beenEditScss('responsive/responsive-1200px-min','responsive') || $this->beenEditScss('responsive/responsive-992px-1199px','responsive') || $this->beenEditScss('responsive/responsive-768px-991px','responsive') || $this->beenEditScss('responsive/responsive-767px-max','responsive') || $this->beenEditScss('responsive/responsive-479px-max','responsive')) {
				$res_css 	= $scss->compileString('@import "responsive.scss"')->getCss();
				if($direction =='ltr') file_put_contents($cssDir.$resCssName, $res_css);
				else file_put_contents($cssDir.$resCssNameRTL, $res_css);
			}
			
			//3.SCSS Compile file multi Header  
			
			$header_sass 	= $scss->compileString('@import "header/header'.$typeheader.'.scss"')->getCss();
			if(file_exists($scssDir.'header/header'.$typeheader.'.scss') ){
				if($direction =='ltr') file_put_contents($cssDir.$themeCssHeader, $header_sass);
				else file_put_contents($cssDir.$themeCssHeaderRTL, $header_sass);
				
			}else{
				echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Please Create correct file path header'.$typeheader.'.scss </div>';
			}
			
			//4.SCSS Compile file multi Footer  
			$footer_sass 	= $scss->compileString('@import "footer/footer'.$typefooter.'.scss"')->getCss();
			
			if(file_exists($scssDir.'footer/footer'.$typefooter.'.scss') ){
				if($direction =='ltr') file_put_contents($cssDir.$themeCssFooter, $footer_sass);
				else file_put_contents($cssDir.$themeCssFooterRTL, $footer_sass);
			}else{
				echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Please Create correct file path footer'.$typefooter.'.scss </div>';
			}
				
			//5.SCSS Compile file multi Layout 
			
			$string_css 	= $scss->compileString('@import "layout-'.$typelayout.'.scss"')->getCss();
			if(file_exists($cssDir.$themeCssName) ){		
				if($direction =='ltr') fwrite(fopen($cssDir.$themeCssName, 'w'),$string_css);
				else fwrite(fopen($cssDir . $themeCssNameRTL, 'w'), $string_css);
			}else{
				echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Pleases Create path of the  css file</div>';
			}			
		} else if ($this->is_admin()){
			//2.Backend end SCSS Compile
			

			if($onoff_muticolor){
				foreach ($listColor as $nameColors => $colorHex) {
					$themeMutiColor = trim($nameColors);
					$themeCssCode = 'layout'.$typelayout.'/'. $themeMutiColor.'.css';
					$themeCssCodeRTL = 'layout'.$typelayout.'/'.$themeMutiColor.'-rtl.css';
					
					$imagePath	= file_exists('extension/so_entry/catalog/view/template/images/styling/'.$themeMutiColor) ? $themeMutiColor : 'default';
					$scss->replaceVariables(array(
						'$linkColor' => ValueConverter::parseValue(trim($colorHex)),
						'$imagePath' => ValueConverter::parseValue($imagePath),
					));
					
					$string_css 		= $scss->compileString('$dir: ltr !default; @import "layout-'.$typelayout.'.scss"')->getCss();
					$string_sass_rtl 	= $scss->compileString('$dir: rtl !default; @import "layout-'.$typelayout.'.scss"')->getCss();
					file_put_contents ($cssDir.$themeCssCode, $string_css);
					file_put_contents ($cssDir.$themeCssCodeRTL, $string_sass_rtl);				
				}
			}else{			
				// Compile CSS : Select Name Color of backend
				$themeCssCode = 'layout'.$typelayout.'/'. $themeNameColors.'.css';
				$themeCssCodeRTL = 'layout'.$typelayout.'/'.$themeNameColors.'-rtl.css';
			
				$scss->replaceVariables(array(
					'$linkColor' => ValueConverter::parseValue($themeColors),
					'$imagePath' => ValueConverter::parseValue($imagePath),
				));
				
				$string_css 	= $scss->compileString('$dir: ltr !default; @import "layout-'.$typelayout.'.scss"')->getCss();
				$string_sass_rtl 	= $scss->compileString('$dir: rtl !default; @import "layout-'.$typelayout.'.scss"')->getCss();
				file_put_contents ($cssDir.$themeCssCode, $string_css);
				file_put_contents ($cssDir.$themeCssCodeRTL, $string_sass_rtl);
			}
			
		}
		
		
		//Begin General CSS (themes.css, responsive.css)
		if($direction =='ltr') {
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssName);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssHeader);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssFooter);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$resCssName);
		}else{
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssNameRTL);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssHeaderRTL);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$themeCssFooterRTL);
			$this->addCss('extension/so_entry/catalog/view/template/css/'.$resCssNameRTL);
		} 
	
	}

	public function scss_compassMobile($colorHex = null,$colorNameHex = null,$onoff_muticolor = null,$listColor = null){
		$colorName   = $this->get_settings('mobilecolor');
		
		$theme_color = $this->get_settings('colorHex') ;
		
		$direction   = $this->language->get('direction');
	
		$imagePath	= file_exists('extension/so_entry/catalog/view/template/images/styling/'.$colorName) ? $colorName: 'default';
		$themeColors  = ($colorHex != null)? $colorHex : $theme_color ;
	
		$themeNameColors = ($this->is_admin())? strtolower($colorNameHex) : $this->get_settings('nameColor')  ;
		
		//Create Name Css of Frontend
		if(isset($colorName)):
			$themeCssName = $colorName.'.css';
			$themeCssNameRTL = $colorName.'-rtl.css';
		else:
			$themeCssName = 'theme.css';
			$themeCssNameRTL = 'theme.css';
		endif;
		
		//Check compass find path  Compile file (css,sass)
		$scssDir = DIR_EXTENSION.'so_entry/catalog/view/template/somobile/sass/';
		$cssDir  = DIR_EXTENSION.'so_entry/catalog/view/template/somobile/css/';

		$scss = new Compiler();
		$cssFormat = $this->get_settings('mscssformat') ? $this->get_settings('mscssformat') :('Nested');
		//$scss->setOutputStyle('ScssPhp\ScssPhp\Formatter\\' .$cssFormat);
		$scss->addImportPath($scssDir);

		//Check SCSS Compile Enable

		
	

		if( !$this->is_admin() && $this->get_settings('mscsscompile') ) {
			//1.Front end SCSS Compile
			$scss->replaceVariables(array(
				'$linkColor' => ValueConverter::parseValue($themeColors),
				'$imagePath' => ValueConverter::parseValue($imagePath),
				'$dir' => ValueConverter::parseValue($direction),
			));
		    $string_css 	= $scss->compileString('@import "mobile.scss"')->getCss();
			//2.SCSS Compile file multi Layout 
				
			if(file_exists($cssDir.$themeCssName) ){
				if($direction =='ltr') fwrite(fopen($cssDir.$themeCssName, 'w'),$string_css);
				else fwrite(fopen($cssDir . $themeCssNameRTL, 'w'), $string_css);					
			}else{
				echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Pleases Create path of the  css file</div>';
			}
			
		} else if ($this->is_admin()){
			//2.Backend end SCSS Compile
			if($onoff_muticolor){
				
				foreach ($listColor as $nameColors => $colorHex) {
					$themeMutiColor = trim($nameColors);
					$themeCssCode = $themeMutiColor.'.css';
					$themeCssCodeRTL = $themeMutiColor.'-rtl.css';
					
					$imagePath	= file_exists('extension/so_entry/catalog/view/template/images/styling/'.$themeMutiColor) ? $themeMutiColor : 'default';
					$scss->replaceVariables(array(
						'$linkColor' => ValueConverter::parseValue(trim($colorHex)),
						'$imagePath' => ValueConverter::parseValue($imagePath),
					));
					
					$string_css 		= $scss->compileString('$dir: ltr !default; @import "mobile.scss"')->getCss();
					$string_sass_rtl 	= $scss->compileString('$dir: rtl !default; @import "mobile.scss"')->getCss();
					file_put_contents ($cssDir.$themeCssCode, $string_css);
					file_put_contents ($cssDir.$themeCssCodeRTL, $string_sass_rtl);
				}
			}else{
				// Compile CSS : Select Name Color of backend
				$themeCssCode =  $themeNameColors.'.css';
				$themeCssCodeRTL = $themeNameColors.'-rtl.css';
				
				$scss->replaceVariables(array(
					'$linkColor' => ValueConverter::parseValue($themeColors),
					'$imagePath' => ValueConverter::parseValue($imagePath),
				));
				$string_css 	= $scss->compileString('$dir: ltr !default; @import "mobile.scss"')->getCss();
				$string_sass_rtl 	= $scss->compileString('$dir: rtl !default; @import "mobile.scss"')->getCss();
				file_put_contents ($cssDir.$themeCssCode, $string_css);
				file_put_contents ($cssDir.$themeCssCodeRTL, $string_sass_rtl);
			}
			
		}

		//Begin General CSS (themes.css, responsive.css)
		if($direction =='ltr') $this->addCss('extension/so_entry/catalog/view/template/somobile/css/'.$themeCssName);
		else $this->addCss('extension/so_entry/catalog/view/template/somobile/css/'.$themeCssNameRTL);
		
	}
	
	
	
	public function getColorScheme($typelayout=null){	
		$log_directory  = DIR_TEMPLATE_FRONT.'/css/layout'.$typelayout;
		if (is_dir($log_directory)) {
			$allfiles = scandir($log_directory);
			foreach ($allfiles as  $value) {
				if (strpos($value, '-rtl') == false && strpos($value, '.css') == true) {
					list($themeColors) = explode('.css',$value); 
					$fileTheme[$themeColors] = ucfirst($themeColors);
					
				}
			}
		} 
		$fileTheme = isset($fileTheme) ? $fileTheme : '';
		
		return $fileTheme;
		
	}
	
	public function getColorMobile(){
		if($this->is_admin()) {
			$log_directory  = DIR_TEMPLATE_FRONT.'somobile/css';
			
			if (is_dir($log_directory)) {
				$allColors = scandir($log_directory);
				foreach ($allColors as  $value) {
					if (strpos($value, '-rtl') == false && strpos($value, '.css') == true) {
						list($themeColors) = explode('.css',$value); 
						$fileTheme[$themeColors] = ucfirst($themeColors);
					}
				}
			} 
			
			return isset($fileTheme) ? $fileTheme : '';
		}
	}
	
	public function beenEditScss($file_name1,$file_name2){
		/// do not compile if scss has not been recently updated
		
		$scssDir = 'extension/so_entry/catalog/view/template/sass/';
		$cssDir  = 'extension/so_entry/catalog/view/template/css/';
		
		return filemtime($scssDir.$file_name1.'.scss') > filemtime($cssDir.$file_name2.'.css');
	}
	
	
 }
  