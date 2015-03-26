<?php
/**
 * MergeMinify - Mage_Core_Model_Layout_Update Rewrite
 *
 * @package    GaugeInteractive_MergeMinify
 * @author     GaugeInteractive <accounts@gaugeinteractive.com>
 */
class GaugeInteractive_MergeMinify_Model_Core_Layout_Update extends Mage_Core_Model_Layout_Update
{
    const HANDLE_ATTRIBUTE = 'data-handle'; //attribute used to store handle

    /**
     * Collect and merge layout updates from
     * file based on handle
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param integer|null $storeId
     * @return Mage_Core_Model_Layout_Element
     */
    public function getFileLayoutUpdatesXml($area, $package, $theme, $storeId = null)
    {
        $xml = parent::getFileLayoutUpdatesXml($area, $package, $theme, $storeId);
        $shouldMergeJs = Mage::helper('mergeminify')->isJsMergeEnabled() && Mage::helper('mergeminify')->isJsMergeHandle();
        $shouldMergeCss = Mage::helper('mergeminify')->isCssMergeEnabled() && Mage::helper('mergeminify')->isCssMergeHandle();
            $methods = array();
            if ($shouldMergeJs) {
                $methods[] = 'addJs';
            }
            if ($shouldMergeCss) {
                $methods[] = 'addCss';
            }
            if ($shouldMergeJs || $shouldMergeCss) {
                $methods[] = 'addItem';
            }
            foreach ($methods as $method) {
                foreach ($xml->children() as $handle => $child) {
                    $items = $child->xpath('.//action[@method=\''.$method.'\']');
                    foreach ($items as $item) {
                        if ($method == 'addItem' && ((!$shouldMergeCss && (string)$item->{'type'} == 'skin_css') || (!$shouldMergeJs && (string)$item->{'type'} == 'skin_js'))) {
                            continue;
                        }
                        $params = $item->xpath('params');
                        if (count($params)) {
                            foreach ($params as $param) {
                                if (trim($param)) {
                                    $param->{0} = (string)$param . ' ' . static::HANDLE_ATTRIBUTE . '="' . $handle . '"';
                                } else {
                                    $param->{0} = static::HANDLE_ATTRIBUTE . '="' . $handle . '"';
                                }
                            }
                        } else {
                            $item->addChild('params', static::HANDLE_ATTRIBUTE . '="'.$handle.'"');
                        }
                    }
                }
            }
                return $xml;
    }
}