<?php

/*
 * Website view.
 */


class View extends ViewCompiler
{
	protected $currentView			= '';
	protected $viewContents			= '';
	protected $cacheDirectory		= '';
	protected $templateDirectory	= '';
	protected $cacheTime			= 0; //0 always update, -1 forever not update, > 0 for setting a valid cache time.
	protected $isCheckViewChange	= true;
	protected $cachedViewMtime		= 0;
	protected $importedCssList		= array();
	protected $viewModifiers		= array();

	const  TEMPLATE_EXT				= '.tpl';
	public static $instance 		= null;
	


	protected function __construct() {
		$this->startTime = microtime(true);
		parent::__construct();
		$this->loadConfig();
		$this->now = time();
		$this->compiledNamePrefix = md5('0');
	}


	public function show($view_name, $clean_cache = 1, $cache_time = null, $force_compile = false) {
		$this->currentView = $view_name;
		$this->forceCompileView = $force_compile;
		if (!$this->isVaild()) {
			throw new ViewException($this->getFullName(), ViewException::NOT_FOUND);
		}

		if (!$this->isCompiled() || $this->isForceCompileView()) {
			$this->cacheTime = $cache_time <> null ? $cache_time : $this->cacheTime;
			$this->loadAndCompile($this->getFullName());
		}

		$this->loadedViewName = $this->getFullName();
		$this->loadCompileInfo();
		if ($this->isCompiled() && ($this->isForceCompileView() || !$this->isCached() || $this->isExpire())) {
			if ($this->isCached() && $this->isExpire()) {
				$this->compiledViewContents = implode('', $this->loadedViewContents);
				$this->generatCompileHeader();
				$this->writeCompiledHeader();
				$this->saveCompiledViewContents();
			}

			$output_buff = OutputBuffer::getInstance();
			$output_buff->createBuffer();
			require $this->getCompiledViewName();
			$this->viewContents = $output_buff->getContents(true);
			$this->cachingView();
		}

		$this->showCachedView();
		$this->cleanCache($clean_cache);
	}


	public function importCss($css_name) {
		if (!isset($this->viewModifiers['import_css'])) {
			$this->registerViewModifier('import_css', function(View $view){
				$contents = $view->viewContents;
				$csslnk = '<link rel="stylesheet" type="text/css" href="/%s" />';
				$offset = stripos($contents, '<link');
				$insert = '';
				foreach($view->importedCssList as $css) {
					$insert .= sprintf($csslnk, $css);
				}
				$view->viewContents = substr($contents, 0, $offset) . $insert . substr($contents, $offset);
			});
		}

		$this->importedCssList[] = $css_name;
	}


	private function loadCompileInfo() {
		$file = $this->getCompiledViewName();
		$text = file($file);
		$info = $text[0];
		array_shift($text);
		$this->loadedViewContents = $text;

		eval('$info = '.str_replace(array('<?php', '?>'), array(''), $info).';');
		$this->cacheTime	   = $info['cache_time'];
		$this->cacheNamePrefix = $this->cacheNamePrefix<>null ? $this->cacheNamePrefix : $info['cache_prefix'];
	}


	private function showCachedView() {
		require $this->getCacheViewName();
	}


	private function loadConfig() {
		$config = Config::get('view');
		$this->cacheTime = $config['cache_time'];
		$this->setCacheDir($config['cache_dir']);
		$this->setCompiledDir($config['compiled_dir']);
		$this->setTemplateDir($config['template_dir']);
	}


	private function callViewModifiers() {
		foreach($this->viewModifiers as $name) {
			call_user_func($name, $this);
		}
	}


	private function registerViewModifier($name, $func) {
		$this->viewModifiers[$name] = $func;
	}


	private function isVaild($name = null) {
		$full_name = $this->getFullName($name);
		if (is_file($full_name) && is_readable($full_name) && is_writable($full_name)) {
			return true;
		}
		return false;
	}


	public function getFullName($name = null) {
		return $this->templateDirectory . DS . ($name <> null ? $name : $this->currentView);
	}


	private function setTemplateDir($dir) {
		$this->templateDirectory = $dir;
	}


	private function setCompiledDir($dir) {
		$this->compiledDirectory = $dir;
	}


	private function setCacheDir($dir) {
		$this->cacheDirectory = $dir;
	}


	private function isCached() {
		return is_file($this->getCacheViewName()) && is_readable($this->getCacheViewName());
	}


	private function isExpire() {
		return time() - filemtime($this->getCacheViewName()) > $this->cacheTime;
	}


	private function cachingView() {
		$this->callViewModifiers();
		if (!file_put_contents($this->getCacheViewName(), $this->viewContents)) {
			throw new Exception('Can not caching the view '. $this->currentView .'!');
		}
	}


	private function getCacheViewName() {
		return $this->cacheDirectory . DS . $this->cacheNamePrefix . basename($this->currentView) . PHP_EXT;
	}


	private function cleanCache($before_day) {
		$time  = $before_day * 3600 * 24;
		$files = glob($this->cacheDirectory . DS . '*' . basename($this->currentView) . PHP_EXT);
		foreach($files as $file) {
			if (time() - filemtime($file) > $time) {
				unlink($file);
			}
		}
	}


	private function isCompiled() {
		return is_file($this->compiledDirectory . DS . $this->compiledNamePrefix . basename($this->currentView) . PHP_EXT);
	}


	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}



class ViewCompiler
{
//* some compile options:
	const COMPILE_LOADED_VIEW			= 0x01;
	const COMPILE_SINGLE_FILE			= 0x02;
	const COMPILE_STRING_CODE			= 0x03;

	protected $now						= '';
//* Start processing time.
	protected $startTime				= 0;

//* Left limiter of Template syntax.
	protected $leftLimiter				= '(?<!\\\\){\s*';
//* Right limiter of Template syntax.
	protected $rightLimiter				= '\s*(?!\\\\)}';
//* Contents of current loaded view.
	protected $loadedViewContents		= array();
//* Which line of parsing now.
	protected $currentLine				= 0;
//* 
	protected $compileFileUsedTime		= array();

	protected $compileTotalUsedTime		= 0;

	protected $compiledNamePrefix		= '';

	protected $cacheNamePrefix			= '';

//* Template syntax regex.
	protected $variableRegex			= '';
	protected $variableModifierRegex 	= '';
	protected $internalVariableRegex	= '';
	protected $configVariableRegex		= '';
	protected $normalVariableRegex		= '';
	protected $numberRegex				= '';
	protected $mathOperator				= '';
	protected $numberValueRegex			= '';
	protected $mathExpressionRegex		= '';
	protected $mathExpWithBrackets		= '';
	protected $normalFuncArgsRegex  	= '';
	protected $normalFuncCallRegex		= '';
	protected $phpFuncCallRegex			= '';
	protected $phpMathComputeRegex		= '';
	protected $stringRegex				= '';
	protected $controlStructRegex		= '';
	protected $blockFunctionRegex		= '';

	protected $templateVariables		= array();
	protected $loadedViewName			= '';
	protected $compiledDirectory		= '';
	protected $compiledViewName			= '';
	protected $compiledViewContents		= '';
	protected $normalFunctionPrefix		= 'N_';
	protected $forceCompileView			= false;
	protected $variableModifierPrefix	= 'M_';
	protected $compileTimeFilters		= array();
	protected $compiledViewHeader		= '';
	protected $blockFunctionPrefix		= 'B_';

	

	protected function __construct() {
		$this->registerAfterFilter(array($this, 'defaultCompileTimeFilter'));
		$this->initMatchRules();
	}


	private function initMatchRules() {
		$this->stringRegex				= '(?: \'(?>[^\'\\\\]+|\\\\\')*\' | "(?>[^\\\\"]+|\\\\")*")';
		$this->configVariableRegex		= '\#[a-zA-Z_]\w*(?:\.\#?[a-zA-Z_]\w*)*';
		$this->internalVariableRegex	= '@[a-zA-Z_]\w*(?:\.@?[a-zA-Z_]\w*)*';
		$this->normalVariableRegex 		= '\$[a-zA-Z_]\w*(?:->\$?[a-zA-Z_]\w*|\.\$?[a-zA-Z_]\w*)*';
		$this->numberRegex				= '\s*-?\d+(?:\.\d+)?\s*';
		$this->mathOperator				= '\s*[+*\/-]\s*';
		$this->variableRegex			= "(?: :?(?: $this->normalVariableRegex | $this->configVariableRegex |
			$this->internalVariableRegex )". '(?:\[(?:(?>[^\[\]]+)| \[(?:(?>[^\[\]]+)|\[(?>[^\[\]]+)\])*\])*\])*)';
		$this->variableModifierRegex	= $this->variableRegex . '(?:\s*\|\s*[a-zA-Z_]\w*(?::(?>"[^\"]*"|\d+))*)+';
		$this->normalFuncArgsRegex		= '(?:\s[a-zA-Z_]\w*="(?>[^\\\\"]+|\\\\")")+';
		$this->normalFuncCallRegex		= '(?| (' . $this->variableRegex . ')'. $this->normalFuncArgsRegex . '|
												([a-zA-Z_]\w*)(?:'. $this->normalFuncArgsRegex . ')?)';
		$this->phpFuncCallRegex			= '(?:
			(?:\s*\$[a-zA-Z_]\w*(?:\s*->\s*\$?[a-zA-Z_]\w*|\s*\.\s*$[a-zA-Z_]\w*)* |
			\$[a-zA-Z_]\w*|[a-zA-Z_]\w*)(?:\((?: [^()]+ | \((?:[^()]+ | \((?: [^()]+ |
			\((?: [^()]+ | \((?:[^()]+| \((?:[^()+] | \([^()]+)*\)* )* \) )* \) )* \) )* \) )* \) )+)';
		$this->numberValueRegex			= "(?:$this->numberRegex | $this->variableRegex | $this->phpFuncCallRegex)";
		$this->mathExpressionRegex		= "$this->numberValueRegex (?: $this->mathOperator $this->numberValueRegex)+";
		$this->mathExpWithBrackets		= "
			(?:(?:$this->numberValueRegex)?$this->mathOperator)?\((?: $this->mathExpressionRegex |
			(?:(?:$this->numberValueRegex)?$this->mathOperator)?\((?: $this->mathExpressionRegex |
			(?:(?:$this->numberValueRegex)?$this->mathOperator)?\((?: $this->mathExpressionRegex |
			(?:(?:$this->numberValueRegex)?$this->mathOperator)?\((?: $this->mathExpressionRegex |
			(?:(?:$this->numberValueRegex)?$this->mathOperator)?\( $this->mathExpressionRegex \) )+ \) )+ \) )+ \) )+ \)";
		$this->phpMathComputeRegex		= "(?:$this->mathExpressionRegex | $this->mathExpWithBrackets)
			(?: $this->mathOperator (?:$this->mathExpressionRegex | $this->mathExpWithBrackets))*";
		$this->controlStructRegex		= '(if|endif|else|elseif|:?while|endwhile|do|for|endfor|foreach|endforeach)(.*?)';
		$this->blockFunctionRegex		= $this->leftLimiter . '((?|
			(?: (' .  $this->normalVariableRegex . '|' .$this->configVariableRegex . '|' . $this->internalVariableRegex  . ')
			(?:\[(?:(?:[^\[\]]+)| \[(?:(?:[^\[\]]+)|\[(?:[^\[\]]+)\])*\])*\])*' . $this->normalFuncArgsRegex .')|
			([a-zA-Z_]\w*)(?:' . $this->normalFuncArgsRegex . ')?))' .
			$this->rightLimiter . '(.+?)' . $this->leftLimiter . '\/\2' . $this->rightLimiter;
	}


	protected function generatCompileHeader() {
		$this->cacheNamePrefix = $this->isForceCompileView() ?  md5('') : md5(rand());
		$this->compiledViewHeader = sprintf("<?php %s; ?>\n", str_replace("\n", '', var_export(array(
			'cache_time'	=> $this->cacheTime,
			'cache_prefix'	=> $this->cacheNamePrefix,
			), true
		)));
	}


	protected function writeCompiledHeader() {
		$this->compiledViewContents = $this->compiledViewHeader . $this->compiledViewContents;
	}


	protected function loadAndCompile($file_name) {
		$this->generatCompileHeader();
		$this->loadedViewName		= $file_name;
		$this->loadedViewContents	= file_get_contents($file_name);
		$this->compiledViewName		= $this->getCompiledViewName();
		$this->compiledViewContents = $this->compile();
		$this->writeCompiledHeader();
		$this->saveCompiledViewContents();
	}


	protected function compile($compile_opts = self::COMPILE_LOADED_VIEW, $file = '', $contents = '') {
		$start_compile	= microtime(true);
		$matched_data	= array();
		$block_function = true;

		switch($compile_opts) {
			case self::COMPILE_LOADED_VIEW:
				$file	  = $this->loadedViewName;
				$contents = $this->loadedViewContents;
				break;
			case self::COMPILE_SINGLE_FILE:
				break;
			case self::COMPILE_STRING_CODE:
				$block_function = false;
				break;
		}

		if ($block_function && preg_match_all('/'.$this->blockFunctionRegex.'/sx', $contents, $matched_data)) {
			$this->parseBlockFunction($contents, $matched_data[0], $matched_data[1], $matched_data[2], $matched_data[3]);
		}

		if (preg_match_all("/$this->leftLimiter $this->controlStructRegex $this->rightLimiter/x", $contents,
				$matched_data)) {
			$this->parseControlStruct($contents, $matched_data[0], $matched_data[1], $matched_data[2]);
		}

		if (preg_match_all("/$this->leftLimiter ($this->variableRegex) $this->rightLimiter/x", $contents, $matched_data)) {
			$this->injectVariable($contents, $matched_data[0], $matched_data[1]);
		}

		if (preg_match_all("/$this->leftLimiter($this->variableModifierRegex) $this->rightLimiter/x", $contents,
				$matched_data))
		{
			$this->parseVariableModifier($contents, $matched_data[0], $matched_data[1]);
		}

		if (preg_match_all("/$this->leftLimiter($this->normalFuncCallRegex) $this->rightLimiter/x", $contents,
				$matched_data))
		{
			$this->parseNormalFunction($contents, $matched_data[0], $matched_data[1], $matched_data[2]);
		}

		if (preg_match_all("/$this->leftLimiter($this->phpFuncCallRegex | $this->phpMathComputeRegex)
				$this->rightLimiter/x",
				$contents, $matched_data)) 
		{
			$this->convertToPHP($contents, $matched_data[0], $matched_data[1]);
		}

		$this->callCompileTimeFilters($file, $contents);
		$this->compileFileUsedTime[basename($file)] = microtime(true) - $start_compile;
		return $contents;
	}


	private function parseControlStruct(&$contents, $raw, $ctr, $data) {
		$data = preg_replace("/$this->variableRegex/xe", "\$this->replaceVariable('$0')", $data);
		$func = function($key, $value) {
			if ($key == ':while') {
				$value = '<?php }while('. $value . '); ?>';
			}else if ($key == 'do') {
				$value = '<?php do { ?>';
			}else{
				$value = '<?php '.$key.(trim($value) <> '' ? '('.$value.') :' : ';').'?>';
			}
			return $value;
		};

		$contents = str_replace($raw, array_map($func, $ctr, $data), $contents);
	}


	private function parseBlockFunction(&$contents, $raw, $func_info, $func_name, $block_contents) {
		$counter   = 0;
		$new_data  = array();
		$has_error = false;
		$cd_model  = '$this->callBlockFunction(%s, %s)';
		foreach($func_info as $value) {
			$code = '';
			$name = trim($func_name[$counter], ': ');
			$args = str_replace($name, '', $value);
			$args = array($this->encode($block_contents[$counter++]), parse_var($args));

			if ($name{0} == '$') {
				$name = $this->replaceVariable($name);
				eval("\$_name = $name;");
				if ($this->isBlockFunction($_name)) {
					$name = sprintf('{:%s}', $matches[1]);
					$code .= sprintf($cd_model, var_export($name, true), var_export($args, true));
				}else{
					$has_error = true;
				}
			}else if ($this->isBlockFunction($name)) {
				$code .= sprintf($cd_model, var_export($name, true), var_export($args, true));
			}else{
				$has_error = true;
			}

			if ($has_error !== false) {
				throw new Exception('No such a block function named '.$name.'!');
			}
			$new_data[] = sprintf('<?php %s; ?>', $code);
		}
		$contents = str_replace($raw, $new_data, $contents);
	}


	private function parseVariableModifier(&$contents, $raw, $data) {
		$new_data  = array();
		$cd_model  = '$this->callVariableModifier(%s, %s)';
		foreach($data as $value) {
			$funs = explode('|', $value);
			$var  = $funs[0];
			array_shift($funs);
			$func_name	= '';
			$func_args = array(sprintf('{:%s}', $var));
			$func_count = count($funs);

			for ($i = 0; $i < $func_count; $i++) {
				$func_name = $funs[$i];
				if (strpos($func_name, ':') !== false) {
					$temp_args = explode(':', $func_name);
					$func_name = $temp_args[0];
					array_shift($temp_args);
					$func_args = array_merge($func_args, $temp_args);
				}

				if (!$this->isVariableModifier($func_name)) {
					throw new Exception("Call a not defined variable modifier $func_name !");
				}
				
				$func_name  = sprintf($cd_model, var_export($func_name, true), var_export($func_args, true));
				$func_args  = array();
			}

			$new_data[] = sprintf('<?php echo %s; ?>', $func_name);
		}

		$contents = str_replace($raw, $new_data, $contents);
	}


	private function parseNormalFunction(&$contents, $raw, $data, $names) {
		$counter   = 0;
		$new_data  = array();
		$has_error = false;
		$cd_model  = '$this->callNormalFunction(%s, %s)';
		foreach($data as $value) {
			$code = '';
			$name = trim($names[$counter++], ': ');
			$args = str_replace($name, '', $value);
			$args = parse_var($args);

			if ($name{0} == '$') {
				$name = $this->replaceVariable($name);
				eval("\$_name = $name;");
				if ($this->isNormalFunction($_name)) {
					$name = sprintf('{:%s}', $matches[1]);
					$code .= sprintf($cd_model, var_export($name, true), var_export($args, true));
				}else{
					$has_error = true;
				}
			}else if ($this->isNormalFunction($name)) {
				$code .= sprintf($cd_model, var_export($name, true), var_export($args, true));
			}else{
				$has_error = true;
			}

			if ($has_error !== false) {
				throw new Exception('No such a template function named '.$name.'!');
			}
			$new_data[] = sprintf('<?php %s; ?>', $code);
		}

		$contents = str_replace($raw, $new_data, $contents);
	}


	private function convertToPHP(&$contents, $raw, $data) {
		$new_data = array();
		foreach($data as $value) {
			$new_data[] = sprintf('<?php echo %s; ?>', $value);
		}
		$contents = str_replace($raw, $new_data, $contents);
	}


	private function injectVariable(&$contents, $raw, $data) {
		$new_data = array();
		foreach($data as $value) {
			$value 		= trim($value);
			$text 		= $value{0} == ':' ? '%s' : '<?php echo %s; ?>';
			$new_data[] = sprintf($text, $this->replaceVariable($value));
		}

		$contents = str_replace($raw, $new_data, $contents);
	}


	public function replaceVariable($var) {
		$exp_str = $var;
		$var = trim($var, ': ');
		$bracket_var = '%s';
		$bracket_key = '[\'%s\']';

		if (strpos($var, '.') !== false) {
			$exp_str = '';
			$tmp_var = explode('.', $var);
			foreach($tmp_var as $each_key) {
				$each_key = trim($each_key, ': ');
				$var_type = $each_key{0};
				$each_key = substr($each_key, 1);
					
				switch($var_type) {
					case '$':
						$data_res = '$this->templateVariables';
						break;
					case '#':
						$data_res = '$_ENV';
						break;
					case '@':
						$data_res = '$this->';
						$bracket_key = '%s';
						break;
					default:
						$data_res = '';
						$bracket_var = '%s';
						$each_key = $var_type . $each_key;
						break;
				}
				$each_key = sprintf($bracket_var, $data_res . sprintf($bracket_key, $each_key));
				$exp_str .= $each_key;
				$bracket_var = '[%s]';
				$bracket_key = '[\'%s\']';
			}
		}else{
			$type = $var{0};
			$var = substr($var, 1);
			switch ( $type ) {
				case '$':
					$data_res = '$this->templateVariables';
					break;
				case '@':
					$data_res = '$this->';
					$bracket_key = '%s';
					break;
				case '#':
					$data_res = '$_ENV';
					break;
				default:
					$data_res = '';
				
			}
			$exp_str =  $data_res . sprintf($bracket_key, $var);
		}

		return $exp_str;
	}


	protected function encode($code) {
		return str_replace(array('{', '}'), array('\{', '\}'), htmlentities($code));
	}


	protected function decode($code) {
		return str_replace(array('\{', '\}'), array('{', '}'), html_entity_decode($code));
	}


	public function registerAfterFilter($filter) {
		if (!is_callable($filter)) {
			throw new Exception("The filter $filter can't call!");
		}

		$this->compileTimeFilters[] = $filter;
	}

	private function callCompileTimeFilters($file, &$contents) {
		foreach($this->compileTimeFilters as $filter) {
			$contents = call_user_func($filter, $this, $file, $contents);
		}
	}


	private function defaultCompileTimeFilter(View $view, $view_name, $cpd_cnt) {
		return preg_replace(
			"/'$this->leftLimiter($this->variableRegex)$this->rightLimiter'/xSe",
			'\$this->replaceVariable(\'$1\');',
			$cpd_cnt
		);
	}


	private function isTemplateVar($var_name) {
		if (isset($this->templateVariables[$var_name])) {
			return true;
		}
		return false;
	}


	private function isNormalFunction($func_name) {
		if (function_exists($this->normalFunctionPrefix . $func_name)) {
			return true;
		}
		return false;
	}


	private function isBlockFunction($func_name) {
		if (function_exists($this->blockFunctionPrefix . $func_name)) {
			return true;
		}
		return false;
	}


	private function isVariableModifier($vm) {
		if (function_exists($this->variableModifierPrefix . $vm)) {
			return true;
		}
		return false;
	}


	private function callVariableModifier($vm_name, array $args) {
		if (!is_callable($this->variableModifierPrefix . $vm_name)) {
			throw new Exception('Can\'t call the variable modifier ' . $vm_name . '!');
		}
		return call_user_func_array($this->variableModifierPrefix . $vm_name, $args);
	}


	private function callNormalFunction($fc_name, $args) {
		if (!is_callable($this->normalFunctionPrefix . $fc_name)) {
			throw new Exception('Can\'t call the normal function ' . $fc_name . '!');
		}
		return call_user_func($this->normalFunctionPrefix . $fc_name, $this, $args);
	}


	private function callBlockFunction($fc_name, $args) {
		if (!is_callable($this->blockFunctionPrefix . $fc_name)) {
			throw new Exception('Can\'t call the block function ' . $fc_name . '!');
		}
		return call_user_func($this->blockFunctionPrefix . $fc_name, $this, $this->decode($args[0]), $args[1]);
	}


	public function assignVariable($key, $value = null) {
		if (is_array($key)) {
			$this->templateVariables = array_merge($this->templateVariables, $key);
			return;
		}
		$this->templateVariables[$key] = $value;
	}


	protected function saveCompiledViewContents() {
		file_put_contents($this->getCompiledViewName(), $this->compiledViewContents);
	}


	protected function getCompiledViewName($name = null) {
		if ($name <> null) {
			return $this->compiledDirectory . DS . $this->compiledNamePrefix . basename($name) . PHP_EXT;
		}
		return $this->compiledDirectory . DS . $this->compiledNamePrefix . basename($this->loadedViewName) . PHP_EXT;
	}


	protected function isForceCompileView() {
		return $this->forceCompileView === true;
	}


	public function includeView($file) {
		if (!is_file($this->compiledDirectory . DS . $this->compiledNamePrefix . basename($file) . PHP_EXT) ||
				$this->forceCompileView) {
			$compiled = $this->compile(self::COMPILE_SINGLE_FILE, $file, file_get_contents($this->getFullName($file)));
			file_put_contents($this->getCompiledViewName($file), $compiled);
		}
		require $this->compiledDirectory . DS . $this->compiledNamePrefix. basename($file). PHP_EXT;
	}


	protected function setForceCompile($bool = false) {
		$this->forceCompileView = $bool;
	}


	public function __get($name) {
		if (isset($this->{$name})) {
			return $this->{$name};
		}
		throw new Exception('Acess a not defined variable '. $name .' of View!');
	}


	public function __set($name, $var) {
		if (isset($this->{$name})) {
			$this->{$name} = is_array($var) ? array_merge($this->{$name}, $var) : $var;
		}else{
			throw new Exception('Acess a not defined variable '. $name .' of View!');
		}
	}


	public function __call($func, $args) {
		if (method_exists($this, $func)) {
			return call_user_func_array(array($this, $func), $args);
		}
		throw new Exception('Call a not defined method '. $func .' on line '. __LINE__);
	}


	public function __destruct() {
		unset($this);
	}
}


class ViewException extends Exception
{
	const NOT_FOUND	   = 0x00;
	const SYNTAX_ERROR = 0x01;
	const INVAILD_VIEW = 0x02;

	protected static $ERRMSG = array(
		'View "%s" not found!',
		'Template syntax error on %d line, code %s.',
		'"%s" is a invaild view!'
	);


	public function __construct($msg, $code) {
		parent::__construct(sprintf(self::$ERRMSG[$code], $msg), $code);
	}
}