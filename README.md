# Minify script
Minify script is an HTTP content server. It compresses sources of content 
(usually files), combines the result and serves it with appropriate 
HTTP headers. These headers can allow clients to perform conditional 
GETs (serving content only when clients do not have a valid cache) 
and tell clients to cache the file for a period of time.

Example: http://www.aon.net.br/tmp/minify-script/minify.php
Example filter single extension: http://www.aon.net.br/tmp/minify-script/minify.php?extensions=html&source=../../tmp/minify-script/source&target=target
Example multiple extensions: http://www.aon.net.br/tmp/minify-script/minify.php?extensions=css,js&source=../../tmp/minify-script/source&target=target

Example single file (source/contato.html): http://www.aon.net.br/tmp/minify-script/minify.php?file=source/contato.html

It compresses multiple files (HTML, PHP, CSS and JS) at the same time or you can filter by extension.
