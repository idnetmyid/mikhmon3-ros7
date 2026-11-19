
<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 */

session_start();
error_reporting(0);

// Remove PPP Profile
if ($removepprofile != "") {

    $API->comm("/ppp/profile/remove", array(
        ".id" => $removepprofile,
    ));

    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
}
?>
