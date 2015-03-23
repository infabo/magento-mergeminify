<?php
/**
 * Compress JavaScript using the Closure Compiler
 * https://developers.google.com/closure/
 *
 * Compress CSS using YUI Compressor
 * http://yui.github.io/yuicompressor/
 *
 * @package		GaugeInteractive_MergeMinify
 * @author		GaugeInteractive <accounts@gaugeinteractive.com>
 */
class MergeMinify_Minification
{
	/**
	 * Filepath of the jar file
	 *
	 * @var string
	 */
	public static $jar = null;

	/**
	 * Writable temporary directory 
	 *
	 * @var string
	 */
	public static $tmpDir = null;
	
	/**
	 * Performs minification of file
	 *
	 * @param $data
	 * @param $targetFile
	 * @param $debug
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function minifyFile($type, $data, $targetFile, $debug = true)
	{
		self::_validate();
		// Creates temporary file to be used for minification - throws error if file can't be created
		$tmpTargetFile = self::_createTmpFilename($targetFile);
		if (!file_put_contents(self::_createTmpFilename($targetFile), $data, LOCK_EX)) {
			throw new Exception('MergeMinify_Minification:could not create temporary file.');
		}

		$output = array();
		$status = 0;
		$command = self::_command($type, $targetFile, $tmpTargetFile, $debug);
		Mage::log($command, null, 'chris.log', true);
		exec(escapeshellcmd($command)  . ' 2>&1', $output, $status);

		if ($debug) {
			switch ($type) {
				case 'js':
					Mage::log($output, null, 'JSCompression.log', true);
					break;
				case 'css':
					Mage::log($output, null, 'CSSCompression.log', true);
					break;
			}
		}

		if ((int)$status != 0) {
			throw new Exception('MergeMinify_Minification:compression execution failed.');
		}

		return $output;
	}

	/**
	 * Checks to see if the jar file and tmp directory
	 * are set up correctly before continuing
	 *
	 * @throws Exception
	 */
	protected static function _validate()
	{
		if (!file_exists(self::$jar)) {
			throw new Exception('MergeMinify_Minification:$jar(' . self::$jar . ') is not a valid file.');
		}
		if (!is_dir(self::$tmpDir) || !is_writable(self::$tmpDir)) {
			throw new Exception('MergeMinify_Minification:$temp(' . self::$temp . ') is not a valid directory.');
		}
	}

	/**
	 * Creates temporary filename
	 *
	 * @param $targetFile
	 *
	 * @return string
	 */
	protected static function _createTmpFilename($targetFile)
	{
		$filename = pathinfo($targetFile, PATHINFO_FILENAME);
		$tmpFilename = $filename . '-expanded';
		$tmpTargetFile = str_replace($filename, $tmpFilename, $targetFile);

		return $tmpTargetFile;
	}

	/**
	 * Creates execution command for compression
	 *
	 * @param $targetFile
	 * @param $tmpTargetFile
	 * @param $debug
	 *
	 * @return string
	 */
	protected static function _command($type, $targetFile, $tmpTargetFile, $debug)
	{

		switch ($type) {
			case 'js':
				$compressionLevel = Mage::helper('mergeminify')->getJsCompressionLevel();
				$command = sprintf('java -jar %s --js %s --compilation_level %s --js_output_file %s', self::$jar, $tmpTargetFile, $compressionLevel, $targetFile);
				if ($debug) {
					$command .= ' --summary_detail_level 3 --warning_level VERBOSE';
				}
				break;
			case 'css':
				$command = sprintf('java -jar %s --type css -o %s %s -v', self::$jar, $targetFile, $tmpTargetFile);
				break;
		}

		return $command;
	}
}
