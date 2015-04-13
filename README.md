# Minify script
Minify script is an HTTP content server. It compresses sources of content 
(usually files), combines the result and serves it with appropriate 
HTTP headers. These headers can allow clients to perform conditional 
GETs (serving content only when clients do not have a valid cache) 
and tell clients to cache the file for a period of time.

Example: http://bit.do/minify-example1

Example filter single extension (extension=html): http://bit.do/minify-example2

Example multiple extensions (extension=css,js): http://bit.do/minify-example3

Example single file (source/contato.html): http://bit.do/minify-example4

It compresses multiple files (HTML, PHP, CSS and JS) at the same time or you can filter by extension.
