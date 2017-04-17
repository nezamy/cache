<?php
namespace System;
use System\FileSystem;

class Cache
{
	private static $instance;
    private $config    = [];
	private $ext       = '.php';

	public static function instance(array $conf = [])
	{
		if (null === self::$instance) {
			self::$instance = new Cache($conf);
		}
		return self::$instance;
	}

    public function __construct(array $conf = [])
    {
        $this->config = array_merge([
            'path'      => 'storage/cache'
        ], $conf);
    }


	// public function get($key){
    //
	// 	return $this->isCompiled('mode',BASE_PATH.'mode.php','new value2');
	// }
    //
	// public function set($key, $val, $minutes){}
    //
	// public function has($key){}

	public function extension($ext) {
		$this->ext = $ext;
		return $this;
	}

	/**
	 *	Cache compiled file one time forever. If original file is changed will renew the cache file.
     *	@example Cache::compiled('views.admin.OSC.home', 'fullpath/original/file.php', 'value compiled')
     *
	 *	@param string $key   		the file cache path key i.e 'folder.file'
	 *	@param string $original   	the original file full path
	 *	@param string $val   		the value after compiled
     *
	 *  @return string file path
	 */
	public function compiled($key, $original, $val)
	{
		$o = $this->compiledPrepare($key, $original);

		if( $o->original->getMTime() > $o->cacheFile->getMTime() || !$o->cacheFile->getSize() ){
			$o->cacheFile->openFile('w')->fwrite($val);
		}

		return $o->cacheFile->getRealPath();
	}

	/**
	 *	Check if compiled is cached before .
     *	@example Cache::isCompiled('views.admin.OSC.home', 'fullpath/original/file.php')
	 *
	 *	@param string $key   		the file cache path key i.e 'folder.file'
	 *	@param string $original   	the original file full path
     *
	 *	@return bool
	 */
	public function isCompiled($key, $original)
	{
		$o = $this->compiledPrepare($key, $original);

		return $o->original->getMTime() > $o->cacheFile->getMTime() ? false : true;
	}

	/**
	 *	Prepare compiled and create file cache if not exsists.
     *	@example Cache::compiledPrepare('views.admin.OSC.home', 'fullpath/original/file.php')
	 *
	 *	@param string $key   		the file cache path key i.e 'folder.file'
	 *	@param string $original   	the original file full path
     *
	 *  @return object
	 */
	private function compiledPrepare($key, $original)
	{
		$cachePath = $this->config['path'];

		$fs = new FileSystem;

		$cacheFile = $cachePath . DS . $this->prepare($key) . $this->ext;

		$return = new \stdClass;

		//create cache file if not exsits
		$return->cacheFile 	= $fs->makeFile($cacheFile);
		$return->original 	= $fs->get($original);

		if(!$return->original->isFile()){
			throw new \Exception("Original file {$return->original->getRealPath()} not found");
		}

		return $return;
	}

    /**
	 *	Prepare cache key.
	 *
	 *	@param string $key   	the file cache path key i.e 'folder.file'
     *
	 *  @return string
	 */
	private function prepare($key)
	{
		return str_replace('.', DS, str_replace($this->ext, '', $key) );
	}
}
