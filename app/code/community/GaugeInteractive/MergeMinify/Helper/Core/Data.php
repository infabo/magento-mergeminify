<?php
/**
 * MergeMinify - Helper_Core_Date Rewrite
 *
 * @package    GaugeInteractive_MergeMinify
 * @author     GaugeInteractive <accounts@gaugeinteractive.com>
 */
class GaugeInteractive_MergeMinify_Helper_Core_Data extends GaugeInteractive_MergeMinify_Helper_Data
{
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
     * @param string|false $targetFile - file path to be written
     * @param bool $mustMerge
     * @param callback $beforeMergeCallback
     * @param array|string $extensionsFilter
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
                    // compress JavaScript and CSS with custom function
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