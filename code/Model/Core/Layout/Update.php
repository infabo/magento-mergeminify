<?php
class GaugeInteractive_MergeMinify_Model_Core_Layout_Update extends Mage_Core_Model_Layout_Update
{
    /**
     * Collect and merge layout updates from file
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
        if(Mage::getDesign()->getArea() != 'adminhtml') {
            $shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files') &&
                Mage::getStoreConfigFlag('dev/js/merge_js_by_handle');
            $methods = array();
            if($shouldMergeJs) {
                $methods[] = 'addJs';
                $methods[] = 'addItem';
            }
            /**
             * Don't exactly know what's going on here - probably unnecessary
             * Title parameter isn't valid for the script or link tag anyway
             */
            /* foreach($methods as $method) {
                foreach($xml->children() as $handle => $child){
                    $items = $child->xpath(".//action[@method='".$method."']");
                    foreach($items as $item) {
                        $params = $item->xpath("params");
                        if(count($params)) {
                            foreach($params as $param){
                                if(trim($param)) {
                                    $param->{0} = (string)$param . ' title="' . $handle . '"';
                                } else {
                                    $param->{0} = 'title="' . $handle . '"';
                                }
                            }
                        } else {
                            $item->addChild('params', 'title="'.$handle.'"');
                        }
                    }
                }
            } */
        }
        return $xml;
    }

}