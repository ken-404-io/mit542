<?php

/* Database connection for the admin area. Uses the shared PostgreSQL/PDO
   layer (with a mysqli compatibility shim) so the admin pages keep working
   unchanged. Also pulls in the media/image helpers used by the product
   screens. */
require_once __DIR__ . "/../../functions/db.php";
require_once __DIR__ . "/../../functions/media.php";

?>
