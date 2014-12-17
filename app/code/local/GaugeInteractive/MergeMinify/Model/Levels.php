<?php

class GaugeInteractive_MergeMinify_Model_Levels
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'WHITESPACE_ONLY', 'label'=>Mage::helper('mergeminify')->__('WHITESPACE_ONLY')),
            array('value'=>'SIMPLE_OPTIMIZATIONS', 'label'=>Mage::helper('mergeminify')->__('SIMPLE_OPTIMIZATIONS')),
            array('value'=>'ADVANCED_OPTIMIZATIONS', 'label'=>Mage::helper('mergeminify')->__('ADVANCED_OPTIMIZATIONS'))               
        );
    }
}