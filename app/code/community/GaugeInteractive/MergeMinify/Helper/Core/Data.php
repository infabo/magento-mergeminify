<?php

class GaugeInteractive_MergeMinify_Helper_Core_Data extends Mage_Core_Helper_Data
{
    const XML_PATH_MINIFY_ENABLE_JSCOMPRESSOR  = 'dev/js/enable_compressor_js';
    const XML_PATH_MINIFY_ENABLE_CSSCOMPRESSOR  = 'dev/css/enable_compressor_css';
    const XML_PATH_CLOSURE_COMPILER_LEVEL  = 'dev/js/closure_compiler_level';
    const XML_PATH_CLOSURE_COMPILER_DEBUG  = 'dev/js/compressor_js_debug';
    const XML_PATH_CLOSURE_STYLESHEETS_DEBUG  = 'dev/css/compressor_css_debug';

    /**
     * @return bool
     */
    public function isJsCompressEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MINIFY_ENABLE_JSCOMPRESSOR);
    }

    /**
     * @return bool
     */
    public function isCssCompressEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MINIFY_ENABLE_CSSCOMPRESSOR);
    }

    /**
     * @return bool
     */
    public function getCompressionLevel()
    {
        return Mage::getStoreConfig(self::XML_PATH_CLOSURE_COMPILER_LEVEL);
    }

    /**
     * @return bool
     */
    public function getJsCompressionDebug()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CLOSURE_COMPILER_DEBUG);
    }

    /**
     * @return bool
     */
    public function getCssCompressionDebug()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CLOSURE_STYLESHEETS_DEBUG);
    }

    /**
     * Checks if Google Closure Compiler is enabled
     * and compresses the file
     *
     * @param string $targetfile
     *
     * @return string
     */
    public function compressJsCss($data, $targetFile)
    {
        switch (pathinfo($targetFile, PATHINFO_EXTENSION)) {
            case 'js':
                if ($this->isJsCompressEnabled()) {
                    $tmptargetfile = $this->getTmpFilename($targetFile);
                    file_put_contents($tmptargetfile, $data, LOCK_EX);
                    try {
                        $jsCompilerJAR = Mage::getBaseDir() . DS . 'lib' . DS . 'Closure' . DS . 'compiler.jar';

                        if (!file_exists($jsCompilerJAR)) {
                            throw new Exception('Can\'t minify JavaScript: Compiler not found! ' . $jsCompilerJAR);
                        }

                        $output = array();
                        $status = 0;
                        $command = sprintf('java -jar %s --js %s --compilation_level %s --js_output_file %s', $jsCompilerJAR, $tmptargetfile, $this->getCompressionLevel(), $targetFile);
                        if ($this->getJsCompressionDebug() == 1) {
                            $command .= ' --summary_detail_level 3 --warning_level VERBOSE';
                        }
                        exec(escapeshellcmd($command)  . ' 2>&1', $output, $status);
                        
                        if ($this->getJsCompressionDebug() == 1) {
                            Mage::log($output, null, 'JSCompression.log', true);
                        }

                        if ($status == 0) {
                            $jsCmpressorFailed = false;
                        } else {
                            $jsCmpressorFailed = true;
                        }
                    } catch(Exception $e) {
                        Mage::logException($e);
                        $jsCmpressorFailed = true;
                    }
                }   

                if (!$this->isJsCompressEnabled() || $jsCmpressorFailed) {
                    file_put_contents($targetFile, $data, LOCK_EX);
                }

                break;

            case 'css':
                if ($this->isCssCompressEnabled()) {
                    $tmptargetfile = $this->getTmpFilename($targetFile);
                    file_put_contents($tmptargetfile, $data, LOCK_EX);
                    try {
                        //$cssCompilerJAR = Mage::getBaseDir() . DS . 'lib' . DS . 'Closure' . DS . 'closure-stylesheets.jar';
                        $cssCompilerJAR = Mage::getBaseDir() . DS . 'lib' . DS . 'YUICompressor' . DS . 'yuicompressor-2.4.8.jar';

                        if (!file_exists($cssCompilerJAR)) {
                            throw new Exception('Can\'t minify CSS: Compiler not found! ' . $cssCompilerJAR);
                        }

                        $output = array();
                        $status = 0;

                        // Google Closure Stylesheets Command
                        //$command = sprintf('java -jar %s --output-file %s %s', $cssCompilerJAR, $targetFile, $tmptargetfile);
                        // YUI Compression Command
                        $command = sprintf('java -jar %s --type css -o %s %s -v', $cssCompilerJAR, $targetFile, $tmptargetfile);

                        exec(escapeshellcmd($command)  . ' 2>&1', $output, $status);
                        
                        if ($this->getCssCompressionDebug() == 1) {
                            Mage::log($output, null, 'CSSCompression.log', true);
                        }

                        if ($status == 0) {
                            $cssCompressorFailed = false;
                        } else {
                            $cssCompressorFailed = true;
                        }
                    } catch(Exception $e) {
                        Mage::logException($e);
                        $cssCompressorFailed = true;
                    }
                }   

                if (!$this->isCssCompressEnabled() || $cssCompressorFailed) {
                    file_put_contents($targetFile, $data, LOCK_EX);
                }
                break;

            default:
                file_put_contents($targetFile, $data, LOCK_EX);
                break;
        }
        return $data;
    }

    /**
     * Creates temporary name for target file
     *
     * @param string $targetFile
     *
     * @return string
     */
    public function getTmpFilename($targetFile)
    {
        $filename = pathinfo($targetFile, PATHINFO_FILENAME);
        $tmpfilename = $filename . '-expanded';
        $tmptargetfile = str_replace($filename, $tmpfilename, $targetFile);

        return $tmptargetfile;
    }

    /**
     * Merge specified files into one
     *
     * By default will not merge, if there is already merged file exists and it
     * was modified after its components
     * If target file is specified, will attempt to write merged contents into it,
     * otherwise will return merged content
     * May apply callback to each file contents. Callback gets parameters:
     * (<existing system filename>, <file contents>)
     * May filter files by specified extension(s)
     * Returns false on error
     *
     * @param array $srcFiles
     * @param string|bool $targetFile - file path to be written
     * @param bool $mustMerge
     * @param callback $beforeMergeCallback
     * @param array|string $extensionsFilter
     *
     * @throws Exception
     * @return bool|string
     */
    public function mergeFiles(array $srcFiles, $targetFile = false, $mustMerge = false,
        $beforeMergeCallback = null, $extensionsFilter = array())
    {
        try {
            // check whether merger is required
            $shouldMerge = $mustMerge || !$targetFile;
            if (!$shouldMerge) {
                if (!file_exists($targetFile)) {
                    $shouldMerge = true;
                } else {
                    $targetMtime = filemtime($targetFile);
                    foreach ($srcFiles as $file) {
                        if (!file_exists($file) || @filemtime($file) > $targetMtime) {
                            $shouldMerge = true;
                            break;
                        }
                    }
                }
            }

            // merge contents into the file
            if ($shouldMerge) {
                if ($targetFile && !is_writeable(dirname($targetFile))) {
                    // no translation intentionally
                    throw new Exception(sprintf('Path %s is not writeable.', dirname($targetFile)));
                }

                // filter by extensions
                if ($extensionsFilter) {
                    if (!is_array($extensionsFilter)) {
                        $extensionsFilter = array($extensionsFilter);
                    }
                    if (!empty($srcFiles)){
                        foreach ($srcFiles as $key => $file) {
                            $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (!in_array($fileExt, $extensionsFilter)) {
                                unset($srcFiles[$key]);
                            }
                        }
                    }
                }
                if (empty($srcFiles)) {
                    // no translation intentionally
                    throw new Exception('No files to compile.');
                }

                $data = '';
                foreach ($srcFiles as $file) {
                    if (!file_exists($file)) {
                        continue;
                    }
                    $contents = file_get_contents($file) . "\n";
                    if ($beforeMergeCallback && is_callable($beforeMergeCallback)) {
                        $contents = call_user_func($beforeMergeCallback, $file, $contents);
                    }
                    $data .= $contents;
                }
                if (!$data) {
                    // no translation intentionally
                    throw new Exception(sprintf("No content found in files:\n%s", implode("\n", $srcFiles)));
                }
                if ($targetFile) {
                    $data = $this->compressJsCss($data, $targetFile);
                } else {
                    return $data; // no need to write to file, just return data
                }
            }

            return true; // no need in merger or merged into file successfully
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return false;
    }
}