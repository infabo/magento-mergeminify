#MergeMinify
###Description
MergeMinify allows for JavaScript and CSS to be merged by the handle and also allows for files to be minified using Closure Compiler for JavaScript and YUI Compressor for CSS files. Both Closure and YUI require Java to be installed on the server.
###Compatibility
EE 14.0.1

If you would like to install on another version, below are the Magento classes that have been extended so you can compare.
###System Configurations
System > Configuration > Developer > JavaScript Settings
*	- Merge JavaScript by Handle - Merges all JavaScript files based on the Magento Layout handle - ex. `<default>, <catalog_category_default>`
*	- Enable JavaScript Compression - Utilizes Closer Compiler: https://developers.google.com/closure/compiler/ - Merged JS files are saved to media/js. You will be able to see the expanded version and the minified version when this is enabled.
*	- Debug - Adds additional command parameters to debug and logs to var/log/JSCompression.log
*	- Closure Compiler Compilation Level - Read more: https://developers.google.com/closure/compiler/docs/compilation_levels - Default value is WHITESPACE_ONLY

System > Configuration > Developer > CSS Settings
*	- Merge CSS by Handle - Merges all CSS files based on the Magento Layout handle - ex. `<default>`, `<catalog_category_default>`
*	- Enable CSS Compression - Utilizes YUI Compressor: http://yui.github.io/yuicompressor/ - Merged CSS files are saved to media/css. You will be able to see the expanded version and the minified version when this is enabled.
*	- Debug - Adds additional command parameters to debug and logs to var/log/CSSCompression.log

###Class Rewrites
Mage_Core_Helper_Data: function mergeFiles<br/>
Mage_Core_Model_Layout_Update: function getFileLayoutUpdatesXml<br/>
###Dependecies
Java
