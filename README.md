# Adminer-Dump-Blob-To-Hex
=================================
Plugin for [Adminer](http://http://www.adminer.org/ "www.adminer.com").

This plug-in that convert data in blob format to hex format when export a sql file.

```
require_once $_SERVER['DOCUMENT_ROOT']."/adm/_adminer/plugins/dump-blobToHex.php";

$plugins = array(
  new AdminerDumpBlobToHex()
  );

  return new AdminerPlugin($plugins);
```
