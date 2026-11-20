
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

// Remove PPP Secret dan Scheduler terkait
if ($removesecr != "") {

    // Ambil data PPP Secret sebelum dihapus.
    $getSecret = $API->comm("/ppp/secret/print", array(
        "?.id" => $removesecr,
    ));

    if (isset($getSecret[0]['name'])) {

        // Ambil nama PPP Secret.
        $secretName = $getSecret[0]['name'];

        // Cari Scheduler dengan nama yang sama.
        $getScheduler = $API->comm("/system/scheduler/print", array(
            "?name" => $secretName,
        ));

        // Hapus Scheduler jika ditemukan.
        if (isset($getScheduler[0]['.id'])) {
            $API->comm("/system/scheduler/remove", array(
                ".id" => $getScheduler[0]['.id'],
            ));
        }
    }

    // Hapus PPP Secret.
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
