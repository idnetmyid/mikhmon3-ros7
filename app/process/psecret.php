
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

// Remove PPP Secret
if ($removesecr != "") {

    $API->comm("/ppp/secret/remove", array(
        ".id" => $removesecr,
    ));

    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}

// Enable PPP Secret
elseif ($enablesecr != "") {

    $API->comm("/ppp/secret/set", array(
        ".id" => $enablesecr,
        "disabled" => "no",
    ));

    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}

// Disable PPP Secret
elseif ($disablesecr != "") {

    $API->comm("/ppp/secret/set", array(
        ".id" => $disablesecr,
        "disabled" => "yes",
    ));

    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}
?>
