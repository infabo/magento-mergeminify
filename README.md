MergeMinify
===========

Merge by handle and minify JavaScript and CSS assets in Magento

Multiple configurations have been added to handle the new merge and minify techniques. Below is a list of the new configurations and a quick summary.

System > Configuration > Developer > JavaScript Settings
*	- Merge JavaScript by Handle - Merges all JavaScript files based on the Magento Layout handle - ex. `<default>, <catalog_category_default>`, etc.
*	- Enable JavaScript Compression - Utilizes Closer Compiler: https://developers.google.com/closure/compiler/ - Merged JS files are saved to media/js. You will be able to see the expanded version and the minified version when this is enabled.
*	- Debug - Adds additional command parameters to debug and logs to var/log/JSCompression.log
*	- Closure Compiler Compilation Level - Read more: https://developers.google.com/closure/compiler/docs/compilation_levels - Default value is WHITESPACE_ONLY

System > Configuration > Developer > CSS Settings
*	- Merge CSS by Handle - Merges all CSS files based on the Magento Layout handle - ex. <default>, <catalog_category_default>, etc.
*	- Enable CSS Compression - Utilizes YUI Compressor: http://yui.github.io/yuicompressor/ - Merged CSS files are saved to media/css. You will be able to see the expanded version and the minified version when this is enabled.
*	- Debug - Adds additional command parameters to debug and logs to var/log/CSSCompression.log
