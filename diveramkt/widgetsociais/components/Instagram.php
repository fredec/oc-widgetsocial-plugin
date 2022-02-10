<?php
namespace Diveramkt\Widgetsociais\Components;
// namespace diveramkt\whtasappfloat\components;

use Diveramkt\Widgetsociais\Models\Settings;
// use Detection\MobileDetect as Mobile_Detect;
use Cache;

// SITES WIDGEST INSTAGRAM SUPORTE
// lightwidget.com/

class Instagram extends \Cms\Classes\ComponentBase
{
	public function componentDetails(){
		return [
			'name' => 'Instagram',
			'description' => 'Pegar postagens do widget do instagram.'
		];
	}

	public function defineProperties()
	{
		return [
			'link_widget' => [
				'title'       => 'Link do widget',
				'description' => 'Digite o link do widget do instagram que deseja buscar as postagens',
				// 'type'        => 'text',
				'default'	  => '',
			],
			'total' => [
				'title'       => 'Quantidade',
				'description' => 'Total de postagens',
				// 'type'        => 'text',
				'default'	  => 8,
			],
			'num_horizontal' => [
				'title'       => 'Quantidade horizontal',
				'description' => 'Quantidade na horizontal',
				// 'type'        => 'text',
				'default'	  => 4
			],
			'cache' => [
				'title'       => 'Cache',
				'description' => 'Tempo para verificar widget, digitar em minutos',
				'default'	  => 30
			],
			'formato' => [
				'title'       => 'Formato',
				'description' => 'Formato para mostrar as postagens',
				'default'	  => 'imagens',
				'type' => 'dropdown',
				'options' => [
					'imagens' => 'Imagens',
					'carrosel' => 'Carrosel',
				]
			],
			'load_carrosel' => [
				'title'       => 'Load carrosel',
				'description' => 'Carregar arquivos(css,js) do plugin caso seja carrosel',
				'type'        => 'checkbox',
				'default'	  => 1
			],
		];
	}

	public $postagens=array();
	public $count=0;
	public $total=0;
	public $horizontal=0;
	public $cache=30;
	public $formato='imagens';
	public $load_carrosel=1;

	public function onRun(){
		$this->addCss('/plugins/diveramkt/widgetsociais/assets/style.css');
		// $this->addJs('/plugins/diveramkt/widgetsociais/assets/scripts.js');

		if(str_replace(' ','',$this->property('cache')) != '') $this->cache=$this->property('cache');
		$this->formato=$this->property('formato');
		$this->load_carrosel=$this->property('load_carrosel');

		$url=$this->property('link_widget');
		$this->total=$this->property('total');
		$this->horizontal=$this->property('num_horizontal');

		if(!str_replace(' ','',$url)) return;

		$url_instagram=$this->strLink($url);
		// Cache::forget($url_instagram);
		if(!Cache::has($url_instagram)){
		// if(isset($_GET['teste'])){

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_NOBODY, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			$content = curl_exec($curl);  
			curl_close($curl);
			// $content=$this->file_get_content($url);

			//lightwidget.com/widgets/fd60778ecbfb5413a769a4c2c10f3d4a.html
			if(strpos("[".$url."]", "//lightwidget.com/")){
				$this->site1($content);
			}

			Cache::pull($url_instagram);
			Cache::add($url_instagram, serialize($this->postagens), $this->cache);

		}else $this->postagens=unserialize(Cache::get($url_instagram));

		if(isset($this->postagens['count'])) $this->count=$this->postagens['count'];
	}

	public function site1($content){
		$inicio='href="'; $fim='"';
		preg_match_all("#".$inicio."(.*?)".$fim."#s", $content, $links);

		if(isset($links[1][0])){
			$this->postagens['links']=$links[1];
			$this->postagens['count']=count($this->postagens['links']);
		}

		$inicio='<img '; $fim='>';
		preg_match_all("#".$inicio."(.*?)".$fim."#s", $content, $images);
		if(isset($images[0][0])) $this->postagens['imagens']=$images[0];

		$inicio='src="'; $fim='"';
		preg_match_all("#".$inicio."(.*?)".$fim."#s", $content, $images);
		if(isset($images[1][0])) $this->postagens['src']=$images[1];

		$inicio='alt="'; $fim='"';
		preg_match_all("#".$inicio."(.*?)".$fim."#s", $content, $description);
		if(isset($description[1][0])) $this->postagens['descriptions']=$description[1];
	}

	function file_get_content($url){
		$html=@file_get_contents($url);
		if(!empty($html)) return $html;

		$ch = curl_init(); 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true); 
		$contents = curl_exec($ch); 
		if (curl_errno($ch)) { 
            // echo curl_error($ch); 
            // echo "\n<br />"; 
			$contents = ''; 
		} else { 
			curl_close($ch); 
		} if (!is_string($contents) || !strlen($contents)) { 
            // echo "Failed to get contents."; 
			$contents = ''; 
		} 
		return $contents;
	}

	function url_title($str, $separator = '-', $lowercase = FALSE)
	{
		if ($separator === 'dash'){$separator = '-';}
		elseif ($separator === 'underscore'){$separator = '_';}
		$q_separator = preg_quote($separator, '#');
		$trans = array(
			'&.+?;'         => '',
			'[^\w\d _-]'        => '',
			'\s+'           => $separator,
			'('.$q_separator.')+'   => $separator
		);
		$str = strip_tags($str);
		foreach ($trans as $key => $val){$str = preg_replace('#'.$key.'#iu', $val, $str);}
		if ($lowercase === TRUE){$str = strtolower($str);}
		return trim(trim($str, $separator));
	}

	function strLink($string) {
		$string=str_replace('_', '-', $string);
		$string=str_replace('º', '', $string);
		$string=str_replace('/', '-', $string);
		$string=$this->url_title($string);
		$table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-'
		);
		$stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);
		return strtolower(strtr($string, $table));
	}

}