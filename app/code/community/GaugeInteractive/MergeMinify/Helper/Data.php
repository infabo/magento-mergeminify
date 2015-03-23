<?php
/**
 * MergeMinify Data Helper
 *
 * @package    GaugeInteractive_MergeMinify
 * @author     GaugeInteractive <accounts@gaugeinteractive.com>
 */
class GaugeInteractive_MergeMinify_Helper_Data extends Mage_Core_Helper_Data
{
	/**
	 * System configuration constants
	 */
	const XML_PATH_JS_MERGE_ENABLE   = 'dev/js/merge_files';
	const XML_PATH_JS_HANDLE_ENABLE  = 'dev/js/merge_js_handle';
	const XML_PATH_JS_COMPRESSION_ENABLE  = 'dev/js/enable_compressor_js';
	const XML_PATH_CLOSURE_COMPILER_DEBUG  = 'dev/js/compressor_js_debug';
	const XML_PATH_CLOSURE_COMPILER_LEVEL  = 'dev/js/closure_compiler_level';

	const XML_PATH_CSS_MERGE_ENABLE  = 'dev/css/merge_css_files';
	const XML_PATH_CSS_HANDLE_ENABLE  = 'dev/css/merge_css_handle';
	const XML_PATH_CSS_COMPRESSION_ENABLE  = 'dev/css/enable_compressor_css';
	const XML_PATH_YUI_COMPRESSOR_DEBUG  = 'dev/css/compressor_css_debug';

	/**
	 * @return bool
	 */
	public function isJsMergeEnabled()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_JS_MERGE_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function isJsMergeHandle()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_JS_HANDLE_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function isJsCompressionEnabled()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_JS_COMPRESSION_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function getJsCompressionDebug()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_CLOSURE_COMPILER_DEBUG);
	}

	/**
	 * @return string
	 */
	public function getJsCompressionLevel()
	{
		return Mage::getStoreConfig(self::XML_PATH_CLOSURE_COMPILER_LEVEL);
	}

	/**
	 * @return bool
	 */
	public function isCssMergeEnabled()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_CSS_MERGE_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function isCssMergeHandle()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_CSS_HANDLE_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function isCssCompressionEnabled()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_CSS_COMPRESSION_ENABLE);
	}

	/**
	 * @return bool
	 */
	public function getCssCompressionDebug()
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_YUI_COMPRESSOR_DEBUG);
	}

	/**
	 * Checks if compression is enabled
	 * and compresses the file
	 *
	 * @param string $targetfile
	 *
	 * @return string
	 */
	public function compressJsCss($data, $targetFile)
	{
		$type = pathinfo($targetFile, PATHINFO_EXTENSION);

		switch ($type) {
			case 'js':
				if ($this->isJsCompressionEnabled()) {
					$jsCompressorFailed = false;

					try {
						MergeMinify_Minification::$jar = Mage::getBaseDir() . DS . 'lib' . DS . 'MergeMinify' . DS . 'Closure' . DS . 'compiler.jar';
						MergeMinify_Minification::$tmpDir = pathinfo($targetFile, PATHINFO_DIRNAME);
						$jsDebug = $this->getJsCompressionDebug();

						Varien_Profiler::start('MergeMinify_Minification::minifyJsFile');
						$jsCompressorFailed = MergeMinify_Minification::minifyFile($type, $data, $targetFile, $jsDebug);
						Varien_Profiler::stop('MergeMinify_Minification::minifyJsFile');

						$jsCompressorFailed = false;
					} catch(Exception $e) {
						Mage::logException($e);
						$jsCompressorFailed = true;
					}
				}

				if (!$this->isJsCompressionEnabled() || $jsCompressorFailed) {
					file_put_contents($targetFile, $data, LOCK_EX);
				}

				break;
			case 'css':
				if ($this->isCssCompressionEnabled()) {
					$cssCompressorFailed = false;

					try {
						MergeMinify_Minification::$jar = Mage::getBaseDir() . DS . 'lib' . DS . 'MergeMinify' . DS . 'YUICompressor' . DS . 'yuicompressor-2.4.8.jar';
						MergeMinify_Minification::$tmpDir = pathinfo($targetFile, PATHINFO_DIRNAME);
						$cssDebug = $this->getCssCompressionDebug();

						Varien_Profiler::start('MergeMinify_Minification::minifyCssFile');
						$cssCompressorFailed = MergeMinify_Minification::minifyFile($type, $data, $targetFile, $cssDebug);
						Varien_Profiler::stop('MergeMinify_Minification::minifyCssFile');

						$cssCompressorFailed = false;
					} catch(Exception $e) {
						Mage::logException($e);
						$cssCompressorFailed = true;
					}
				}

				if (!$this->isCssCompressionEnabled() || $cssCompressorFailed) {
					file_put_contents($targetFile, $data, LOCK_EX);
				}

				break;
			default:
				file_put_contents($targetFile, $data, LOCK_EX);
				break;
		}

		return $data;
	}
}