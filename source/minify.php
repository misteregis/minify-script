<?php

$md5 = $_POST['md5'];
$action = $_GET['action'];
$source = $_GET['source'];
$target = $_GET['target'];
$download = $_GET['download'];

$self = $_SERVER['PHP_SELF'];$self = str_replace(basename($self), '', $self);
$split = @explode('/', $self);foreach($split as $current) if(!empty($current)) $path[] = $current;for($i = 0;$i < count($path);$i++) $folder .= '../';
$tg = @explode('..', $target);if((count($tg)-1) == count($path)) $_target = str_replace('../', '', $target);$_target = str_replace(implode('/', $path).'/', '', $_target);

$cookie_source = $folder.substr($self, 1).'source';
$cookie_target = $folder.substr($self, 1).'target';

if($action == 'clear_cookie') {
	setcookie("list_dir[0]", '', time() - 3600);
	setcookie("list_dir[1]", '', time() - 3600);
	setcookie("list_dir[2]", '', time() - 3600);
	setcookie("source", '', time() - 3600);
	setcookie("target", '', time() - 3600);
	echo "<script>parent.location = '".basename(__FILE__)."';</script>";exit;
}elseif($action == 'redirect') {
	setcookie("source", urldecode($source), time() + (10 * 365 * 24 * 60 * 60));
	setcookie("target", urldecode($target), time() + (10 * 365 * 24 * 60 * 60));
	echo "<script>parent.location = '?source=$source&target=$_target';</script>";exit;
}

if ($download) {
 	$extension = strtolower(substr(strrchr($download,"."),1));
	switch($extension){
		case "html": $type = "text/html"; break;
		case "php": $type = "text/plain"; break;
		case "css": $type = "text/css"; break;
		case "js": $type = "text/javascript"; break;
	}

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	header("Content-Type: $type; charset=utf-8");
	header("Content-Disposition: attachment; filename=\"$download\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize("minify.tmp"));
	readfile("minify.tmp");
	exit;
}

$code = true;
$options = array();

if($_GET) foreach($_GET as $key => $val){
	if ($key == 'extensions') $val = explode(',', $val);
	$options[$key] = $val;if ($val) $code = false;
}

$options['file'] = $options['file'] ? $options['file'] : $code;

$options['min'] = strval($options['min']);

$minify = new minify($options);

$minify->run($options['file']);

class minify {
	public $f='';
	public $code;
	public $filename;
	public $cSize = 0;
	public $files_source;
	public $files_target;
	public $total_source = 0;
	public $total_target = 0;
	public $subtitle = 'Minify script';
	public $title = '&reg; MS &reg; Minify';
	public $file_get_contents = array('html', 'htm', 'css', 'js');

	protected $options = array(
		'min' => false,
		'source' => 'source/',
		'target' => 'target/',
		'exclusions' => array('md'),
		'extensions' => array('html', 'htm', 'php', 'css', 'js'),
		'fs' => 'sykpsrMpSVFIzMlMz7NVKspMzyhRsqsuScxNzMvIr7XRL0lBls9JTQNJJxYVlmaWwaT1gWYAAA==',
		'ft' => 'ZcxNCoAgEEDhq4gXcB/qppMMaDrgX+PURrx7RARB64/3NJNg5OSNXGtu5HvHWhYxWqUprWYnIGEoRhKGyNIOhgwl1qkVu68nv90MtB94/vnN7+9jisle'
	);

	public function __construct(array $options = array())
	{
		$this->options = array_merge($this->options, $options);
	}

	public function run($code = false){
		if($code and file_exists($code)){
			$this->options['min'] = true;
			$this->run_file($code);
			$size_source = $this->formatBytes($code);
			$source = gzinflate(base64_decode($this->options['fs']));
			$source = str_replace('{arquivo}', str_replace($GLOBALS['folder'], '/', $code), $source);
			$this->total_source = filesize($code);
			$source = str_replace('{tamanho}', $size_source, $source);
			$this->files_source = $source;
			$this->save_file($code);
			$this->_print();
		}elseif($code){
			if ($_POST) {
				$this->run_file($_POST['minify'], false, true);
			}else{
				$this->run_file($code, false, true);
			}
			$this->_print(true);
		}else{
			$this->run_files();
		}
	}

	public function run_file($code, $target = false, $content = false) {
		$ext = end(@explode('.', $code));
		if(!in_array(strtolower($ext), $this->options['extensions'])) {
			preg_match('/<script\s+|}[)];|function\(|function\s+\(/', $code, $matches); if($matches) $ext = "js";
			preg_match('@style\s+|([\w/]+)\s+{\s+([\w/]+)}?@i', $code, $matches); if($matches) $ext = "css";
			preg_match('@<!DOCTYPE\s+?@i', $code, $matches); if($matches) $ext = "html";
			preg_match('@<html\s+?@i', $code, $matches); if($matches) $ext = "html";
			preg_match('@<?php\s+?@i', $code, $matches); if($matches) $ext = "php";
		}

		if(in_array(strtolower($ext), $this->options['extensions']))
		$this->content($code, $ext, $target, $content);
		if($_POST) $this->filename = $this->filename . $ext;

		if(trim($this->code) === "") return $this->code;

		$this->$ext();
	}

	public function html() {
		$this->code = preg_replace(
			array(
				"/\r|\n|\t/",
				'#<\!--(?!\[if)([\s\S]+?)-->#s',
				'#/\*[^(\*/)]*\*/#',
				'/<!--.*?-->/',
				'#>[^\S ]+#s',
				'#[^\S ]+<#s',
				'#>\s{1,}<#s',
				'/\s+/'
			),
			array(
				"",
				"",
				'',
				'>',
				'',
				'<',
				'><',
				' '
			),
		$this->code);
	}

	public function htm(){$this->html();}

	public function php() {
		$this->code = preg_replace(
			array(
				'/([-+=%\/()[{}\[\]<>|&?!:;.,])\s*/',
				'/<!--.*?-->/',
				'/\r|\n|\t/',
				'/\s+/'
			),
			array(
				'$1',
				'',
				' ',
				' '
			),
		$this->code);
	}

	public function css() {
		$this->code = preg_replace(
			array(
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|/\*(?>.*?\*/)#s',
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				'#([\s:])(0)(cm|em|ex|in|mm|pc|pt|px|%)#',
				'#:0 0 0 0([;\}])#',
				'#background-position:0([;\}])#',
				'#([\s:])0+\.(\d+)#'
			),
			array(
				'$1',
				'$1$2$3$4$5$6$7',
				'$1$2',
				':0$1',
				'background-position:0 0$1',
				'$1.$2'
			),
		$this->code);
	}

	public function js() {
		$this->code = preg_replace(
			array(
				'#\/\*([\s\S]*?)\*\/|(?<!:)\/\/.*([\n\r]+|$)#',
				'#(?|\s*(".*?"|\'.*?\'|(?<=[\(=\s])\/.*?\/[gimuy]*(?=[.,;\s]))\s*|\s*([+-=\/%(){}\[\]<>|&?!:;.,])\s*)#s',
				'/\r|\n|\t/',
				'#;+\}#',
				'/\s+/'
			),
			array(
				"",
				'$1',
				'',
				'}',
				' '
			),
		$this->code);
	}

	public function content($code, $ext, $target, $content) {
		$min = $this->options['min'] ? '.min.' : '.';
		$array = @explode('.', $code);$file = $code;

		if(in_array($ext, $this->file_get_contents) and !$content){
			$getContent = @file_get_contents($code);
		}elseif($ext == 'php'){
			$getContent = @php_strip_whitespace($code);
		}else{
			$getContent = $code;
		}

		$code = (end($array) == $ext) ? $getContent : $code;

		if(!strpos($file, "min.")){
			$file_name = (end($array) == $ext) ? str_replace(".".end($array), $min, $file).end($array) : "minify.";
		}else{
			$file_name = (end($array) == $ext) ? $file : "minify.";
		}

		if($target) $file_name = str_replace($this->getSource(), $this->getTarget(), $file_name);
		$this->filename = $file_name;
		$this->code = $getContent;
	}

	public function formatBytes($file) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = is_file($file) ? filesize($file) : $file;

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? @log($bytes) : 0) / @log(1024));
		$pow = min($pow, count($units) - 1);

		switch($units[$pow]){
			case 'TB':
				$bytes = round(round($bytes/1099511627776*100000)/100000, 2);
				break;

			case 'GB':
				$bytes = round(round($bytes/1073741824*100000)/100000, 2);
				break;

			case 'MB':
				$bytes = round(round($bytes/1048576*100000)/100000, 2);
				break;

			default:
				$bytes = round(round($bytes/1024*100000)/100000, 2);;
		}
		
		if(strlen($bytes) == 6) $bytes = substr($bytes, 0, -3);
		if(strlen($bytes) == 5) $bytes = substr($bytes, 0, -1);

		return "$bytes $units[$pow]";
	}

	public function save_file($code) {
		if($this->filename) if(@file_put_contents($this->filename, $this->code)){
			$tSize = @filesize($this->filename);$this->total_source = filesize($code);
			$size_target = $this->formatBytes($this->filename);
			$por = round(($tSize*100)/filesize($code)-100);
			$por = (strlen($por) > 1) ? substr($por, 1):$por;
			$target = gzinflate(base64_decode($this->options['ft']));
			$target = str_replace('{arquivo}', $this->filename, $target);
			$this->total_target = ($this->total_target + $tSize);
			$target = str_replace('{tamanho}', $size_target, $target);
			$target = str_replace('{por}', "$por%", $target);
			$this->files_target = $target;
		}
	}

	public function save() {
		if($this->filename) if(@file_put_contents($this->filename, $this->code)){
			$tSize = @filesize($this->filename);
			$size_target = $this->formatBytes($this->filename);
			$por = round(($tSize*100)/$this->cSize-100);
			$por = (strlen($por) > 1) ? substr($por, 1):$por;
			$target = gzinflate(base64_decode($this->options['ft']));
			$target = str_replace('{arquivo}', $this->filename, $target);
			$this->total_target = ($this->total_target + $tSize);
			$target = str_replace('{tamanho}', $size_target, $target);
			$target = str_replace('{por}', "$por%", $target);
			$this->files_target .= $target;
			$this->f = 's';
		}
	}

	public function _print($content = false){
		$codex .= '1ZLJbsIwEIZfxbJEbyECwh58qdQXgDsysUMsmYxrDxSKePc6DmFRkbqol548i+ebsf9JhdqRTHPnZhT5SsvISmegdGonKXF40HJG35TAYtLvjcx+mmvgONEyR8rSImFz2NpMkhel5TE/pbEPpQF0RyU1ewVWSCvF2XVolbl4Beyk9dAM9N';
		$codex .= 'rC1gSroXgz2ruoQx9Fhz4aX8uwkFz4w1YmCcPPaGfQurwH5R4jrtW6nGSyxKrrXL3Xj0hjLG7rRkmLsmsirqhx02EF4sDIkdvXrdqBW4JVa7k5EX8jpFLMAfA8iiCh5Yz6SwVS4gd2hnu/S9kCkOvJU7lyZnrEymlYHiUubWtaHP7Ln147';
		$codex .= 'lv5AwHH3ImA9Q1Bwwe1a4j9QMGn3+78TcTho9zp3Ol5z43YneUg9y4QKq8QzbIz/WaegpKz1nWUQ0qEq4W+2oYHV6/Cp+jxkXUiOBuwy1J0ou3G+2qUP';

		$xcode .= 'rVZtb9s2EP4rVxVBGsCS7LVuElsWsCbtsA/ZhjUDtk8DJdISYYnUSMp2FuQv7Ufsl+1OlN8yJ92QWoAk3stzz53O5CWvrn+8uv3tp49QurpKE7rDuq6UnQWlc80kjlerVbR6G2lTxKPLy8t4TTYBGU0qpopZ0Ljww88B+grG06QWjgG5hu';
		$xcode .= 'KPVi5nwZVWTigXurtGBJD71SxwYu1igprmJTNWuNkvt5/CiyA+ApGzvBQhuRpd7WEoHXaq4IhPY1hRs2eMFavFLODC5kY2Tmq1Z3tow1pXarOnvpHWCSMKadHQSVeJ9B4fbaUfktivk0qqBZRGzJG+tXGmtbPOsCaqpYpQEoAR1Syw7q4S';
		$xcode .= 'thTCBb2PF2NAl7cOZE7EPE7/QVAcZXfx+odvF47K1UEAlbevKqGn3QcdQKb5HdxDKWRRugmMhsOTKTzA6xVSQTmSCQ91mxUmreGVrBskwpSbPoKomSmkmsDQG4bvh816Cg3jXKqCxEPwIow1x9yF2WPhNRnLF4XRreL4aSttJmg5pgtV2n';
		$xcode .= 'BhQqcbjNeswepKcnj98YKuHf8UIvomTKoOfhud8GE0xtuQjPeMotwILh1l3ifwzWhjVo4GUL6D+w2Z9xd0TR82/I9HCysxp6qM9/IPTV+qsa+Ao7/GABwB7fDPz8+nqENF1jqn1eYZZU6FXMxZW7kBRFI1rQupUE2I8FrtEMYf6Jr2AZDT';
		$xcode .= 'rqSo/dT9pgfCsNZ/hthmghmEZFxiP7/BMg825jA8ofchXXA+Pjl75L8S2UK6na/Hwtx0A1QJ/9blP4COZ2hR8gZh+whnB3IMMejDnT0R60t0n2GrX+Jc2xd4/9sTnneeywqbbAKN0YXkk+tfv69ZIW4NU3auTR3dyNxoq+cu2mKCdcy4Ky';
		$xcode .= 'ombi2z0x77dABC8X1xF+d08F3veEtbxQjOutb0PfmyzvF5+M7x6dEm8VVbx3fIgZxibJvq//XOlvBzfPWLvJ/unv/i/nT7POX9Vfqnb5TH/dO31bH+SeLu+EmT2B//dN6kCZdLkHwW0C4d+GVeMYtDhWLLjBnwj80ut1nO5Vpw2vUPfba7';
		$xcode .= '7jGokOJ2Ktsw9UiXYfo8SP/+C24+A94/y0IYpIyWHqojj8OCtpJGgAnL8KhpnZgiXowGx+5fZtZg5Xe8ylF6b9tsOyDgeg+pZ7CS3JWT0egdHY10ioeskoWa5FhvQqFNH99arqnmRzlRwf1R9XT5mo2wC1Fjphz8kRik18IKtdTVUnINeO';
		$xcode .= 'xDwg4HD5oE6+3oE7lFHAB2UIGzW/B7hsPgIkh3o1ESszRK4uZRCX2DdJNf+g8=';
		$codex = gzinflate(base64_decode($codex));
		$xcode = gzinflate(base64_decode($xcode));

		$xcode = str_replace('{subtitulo}', '<a href="' . basename(__FILE__) . '">' . $this->subtitle . '</a>', $xcode);
		$xcode = str_replace('{subtitulo}', $this->subtitle, $xcode);
		$xcode = str_replace('{titulo}', $this->title, $xcode);

		if (!$content) {
			$por_total = round((
				$this->total_target*100)/
				$this->total_source-100).
			'%';
			$codex = str_replace('{f}', $this->f, $codex);
			$codex = str_replace('{arquivos_origem}', $this->files_source, $codex);
			$codex = str_replace('{arquivos_destino}', $this->files_target, $codex);
			$codex = str_replace('{total_origem}', $this->formatBytes($this->total_source), $codex);
			$codex = str_replace('{total_destino}', $this->formatBytes($this->total_target), $codex);
			$codex = str_replace('{por_total}', (strlen($por_total) > 1) ? substr($por_total, 1):$por_total, $codex);
			$xcode = str_replace('{conteudo}', $codex, $xcode);

			echo $xcode;
		}else{
			$md5 = $_GET['md5'];
			if(!isset($md5)){
				$xcode = str_replace('{conteudo}', "<iframe class=\"form-control\" src=\"?file=post&md5=c4f448df316abb13eb7eada722c57902\" frameborder=\"0\"></iframe>", $xcode);
				$xcode .= '<style>iframe.form-control{position: relative;width: 1140px;height: 720px;border: 0;}</style>';

				echo $xcode;
			}else{
				@file_put_contents('minify.tmp', $this->code);
				$link = basename(__FILE__) . "?action=clear_cookie";
				$form = '<form action="'.basename(__FILE__).'" method="GET">';
				$form .= '<div class="form-group-sm form-inline" style="margin-top:6px;">';
				$form .= '<input type="hidden" name="action" value="redirect">';
				$form .= '<label for="source">Source:</label>';
				$form .= '<select id="source" class="form-control input-sm" name="source">' . $this->list_dir('source') . '</select>';
				$form .= '<label for="target">&nbsp;Target:</label>';
				$form .= '<select id="target" class="form-control input-sm" name="target">' . $this->list_dir('target') . '</select>';
				$form .= '<button type="submit" class="btn btn-primary input-sm">Minify</button>';
				$form .= '<button type="button" class="btn btn-default input-sm" onClick="location.href=\''.$link.'\'">Update List</button>';
				$iframe = '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-BR"<head><meta http-equiv="Content-type"';
				$iframe .= ' content="text/html;charset=UTF-8"/><meta name="author" content="Misteregis"><title>'.$this->title.'</title>';
				$iframe .= '<link href="css/bootstrap.min.css" rel="stylesheet"><link rel="shortcut icon" href="http://cut.by/xNAkt"/>';
				$iframe .= '</head><body scroll="no" style="overflow: hidden;">'.$form.'</div></form>';
				$iframe .= '<style>label{margin-left:5px;margin-right:5px;}select.form-control{margin-top:0;width: 400px;}';
				$iframe .= '.btn{margin-left:5px;}select.form-control{font-size: 15px;color:#686868;}';
				$iframe .= 'body{margin: 0 auto;color:#686868;}textarea.form-control{width: 1115px;height: 595px;margin-bottom: 10px;margin-top:10px;}</style>';
				$iframe .= '<form action="?file=post&md5=c4f448df316abb13eb7eada722c57902" method="POST">';
				$iframe .= "<textarea name=\"minify\" class=\"form-control\">".htmlentities($this->code)."</textarea>";
				$iframe .= '<div class="form-group-sm form-inline">';
				$iframe .= '<button type="submit" class="btn btn-primary input-sm">Minify</button>';

				if ($_POST) {
					$link = basename(__FILE__) . "?download=$this->filename";
					$iframe .= "<button type=\"button\" class=\"btn btn-default input-sm\" onClick=\"location.href='$link'\">Download</button></div>";
				}

				echo "$iframe</form>";
			}
		}
	}

    public function getSource()
    {
        return $this->fixSlashes($this->options['source']);
    }

    public function getTarget()
    {
        return $this->fixSlashes($this->options['target']);
    }

    public function getExclusions()
    {
        return $this->options['exclusions'];
    }

    public function fixSlashes($filename)
    {
        if (DIRECTORY_SEPARATOR != '/') {
            return str_replace(DIRECTORY_SEPARATOR, '/', $filename);
        }
        return $filename;
    }

    public function run_files()
    {
		if(!is_dir($this->getTarget())) @mkdir($this->getTarget(), 0777, true);
        $dirIterator = new RecursiveDirectoryIterator($this->getSource());
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $key => $value) {
            if (in_array($value->getFilename(), array('..', '.DS_Store'))) { // Exclude system
                continue;
            }

            $pattern = '/^' . preg_quote($this->getSource(), '/') . '/';
            $sourcePathname = $this->fixSlashes($value->getPathname());
			$targetPathname = preg_replace($pattern, $this->getTarget(), $sourcePathname);

			if(!is_dir(dirname($targetPathname))) {@mkdir(dirname($targetPathname), 0777, true);}
			if ($value->isFile() && !strpos($sourcePathname, $this->options['target'])) {
                if (in_array(strtolower(end(@explode('.', $targetPathname))), $this->options['extensions'])) {
					$this->cSize = filesize($sourcePathname);if($this->cSize == 0) continue;
					$this->run_file($sourcePathname, true);

					$size_source = $this->formatBytes($sourcePathname);
					$source = gzinflate(base64_decode($this->options['fs']));
					$source = str_replace('{arquivo}', str_replace($GLOBALS['folder'], '/', $sourcePathname), $source);
					$this->total_source = ($this->total_source + filesize($sourcePathname));
					$source = str_replace('{tamanho}', $size_source, $source);
					$this->files_source .= $source;

					$this->save();
                }
            }
        }
		$this->_print();
    }

	public function list_dir($sel) {
		$self = $_SERVER['PHP_SELF'];$self = str_replace(basename($self), '', $self);$split = explode('/', $self);

		$path = array();$folder;$select = '';
		foreach($split as $current) if(!empty($current)) $path[] = $current;
		for($i = 0;$i < count($path);$i++) $folder .= '../';

		$select .= $_COOKIE['list_dir'][0];
		$select .= $_COOKIE['list_dir'][1];
		$select .= $_COOKIE['list_dir'][2];
		$select = @gzinflate(base64_decode($select));

		if (empty($_COOKIE['list_dir'])) {
			$dir = new RecursiveDirectoryIterator($folder);
			$iterator = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

			$select;
			foreach ($iterator as $fileinfo) {
			    if ($fileinfo->isDir() && !in_array($fileinfo->getFilename(), array('..', '.DS_Store'))) {
			 		$val = $this->fixSlashesDir($fileinfo->getPathname(), $folder, $folder);
			 		$dir = $this->fixSlashesDir($fileinfo->getPathname(), $folder, '/');
					$select .= "<option value=\"$val\">$dir</option>";
			   }
			}

			$base64 = base64_encode(gzdeflate($select, 9));$select = '';
			$chars = round(strlen($base64)/3);$start = 0;$cookies = array();

			for($i = 0; $i < 3; $i++) {
				$cookies = substr($base64, $start, $chars);$start = $start + $chars;
				$select .= $cookies;
				setcookie(
					"list_dir[$i]", $cookies,
					time() + (10 * 365 * 24 * 60 * 60)
				);
			}

			setcookie('target', $GLOBALS['cookie_target'], time() + (10 * 365 * 24 * 60 * 60));
			setcookie('source', $GLOBALS['cookie_source'], time() + (10 * 365 * 24 * 60 * 60));
			$_COOKIE['target'] = $GLOBALS['cookie_target'];
			$_COOKIE['source'] = $GLOBALS['cookie_source'];
			$select = @gzinflate(base64_decode($select));
		}

		if (strpos($select, $_COOKIE[$sel])){
			$select = str_replace($_COOKIE[$sel].'"', $_COOKIE[$sel].'" selected', $select);
		}

		return $select;
	}

	public function fixSlashesDir($filename, $folder, $self) {
		if (DIRECTORY_SEPARATOR != '/') {
			$filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
			return str_replace($folder, $self, $filename);
		}

		return $filename;
	}
}
