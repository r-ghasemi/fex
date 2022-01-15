# Fex Platform
with Fex you can design your web document, easyly.
read 'fex manual.pdf' for additional help.

# Hello world
1. in your project folder create a file named main.fex
```
\include{commands.fex}
\document {
  Hello World!
}
```
2. now you can render main.fex in ypur index.php file like this.
```
<?php
  $file= 'main.fex';
  include_once(__DIR__ . 'fex2html.php');

  $c=file_get_contents($file);
  $o=new fex($c);
  $code=$o->parse(FEX_DOC);
  eval("?>" .  $code);
  die();
?>
```
