<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {

    $getprofile = $API->comm("/ppp/profile/print");
    // $getbridge = $API->comm("/bridge/bridge/print");
    date_default_timezone_set('Asia/Jakarta');
    $hariini = strtolower(date('M/d/Y H:i:s'));


    
if (isset($_POST['name'])) {
    $name = preg_replace('/\s+/', '-', trim($_POST['name']));
    $password = $_POST['password'];
    $service = $_POST['service'];
    $callerid = $_POST['callerid'];
    $profile = $_POST['profile'];
    $interval = trim($_POST['interval']);

    $timezone = new DateTimeZone('Asia/Jakarta');

    // Periksa Interval sebelum membuat PPP Secret.
    $durationSeconds = 0;

    if ($interval !== "") {
        if (!preg_match('/^(?:[0-9]+[wdhms])+$/', $interval)) {
            echo "<script>alert('Format Interval tidak valid! Contoh: 30d, 5m, 12h');</script>";
            return;
        }

        preg_match_all('/([0-9]+)([wdhms])/', $interval, $matches, PREG_SET_ORDER);

        $unitSeconds = array(
            'w' => 604800,
            'd' => 86400,
            'h' => 3600,
            'm' => 60,
            's' => 1
        );

        foreach ($matches as $match) {
            $durationSeconds += (int)$match[1] * $unitSeconds[$match[2]];
        }

        if ($durationSeconds <= 0) {
            echo "<script>alert('Interval harus lebih besar dari nol!');</script>";
            return;
        }
    }

    // Buat PPP Secret dalam keadaan disabled terlebih dahulu.
    $resultSecret = $API->comm("/ppp/secret/add", array(
        "name" => $name,
        "password" => $password,
        "service" => $service,
        "caller-id" => $callerid,
        "profile" => $profile,
        "disabled" => "yes",
    ));

    // Periksa apakah pembuatan PPP Secret gagal.
    if (isset($resultSecret['!trap']) || isset($resultSecret['!fatal'])) {
        echo "<script>alert('Gagal membuat PPP Secret di MikroTik.');</script>";
        return;
    }

    // Jika Interval kosong, user tetap disabled tanpa Scheduler.
    if ($interval !== "") {

        // Hitung waktu kedaluwarsa sejak user disimpan.
        $expiry = new DateTimeImmutable('now', $timezone);
        $expiry = $expiry->modify('+' . $durationSeconds . ' seconds');

        $start_date = strtolower($expiry->format('M/d/Y'));
        $start_time = $expiry->format('H:i:s');

        // Amankan nama untuk perintah RouterOS.
        $safeName = str_replace(
            array("\\", "\"", "$"),
            array("\\\\", "\\\"", "\\$"),
            $name
        );

        // Event kedaluwarsa:
        // 1. Nonaktifkan PPP Secret.
        // 2. Putuskan koneksi PPPoE aktif.
        // 3. Nonaktifkan Scheduler.
        $on_event = '/ppp secret set disabled=yes [find where name="' . $safeName . '"]; ' .
            '/ppp active remove [find where name="' . $safeName . '"]; ' .
            '/system scheduler disable [find where name="' . $safeName . '"]';

        // Buat Scheduler satu kali pada waktu kedaluwarsa.
        $resultScheduler = $API->comm("/system/scheduler/add", array(
            "name" => $name,
            "start-date" => $start_date,
            "start-time" => $start_time,
            "interval" => "0s",
            "on-event" => $on_event,
        ));

        // Jika Scheduler gagal dibuat, hapus PPP Secret agar
        // tidak meninggalkan user yang tidak memiliki jadwal.
        if (isset($resultScheduler['!trap']) || isset($resultScheduler['!fatal'])) {
            $API->comm("/ppp/secret/remove", array(
                "numbers" => $name
            ));

            echo "<script>alert('Gagal membuat Scheduler. PPP Secret tidak diaktifkan.');</script>";
            return;
        }

        // Aktifkan PPP Secret setelah Scheduler berhasil dibuat.
        $resultEnable = $API->comm("/ppp/secret/set", array(
            "numbers" => $name,
            "disabled" => "no"
        ));

        if (isset($resultEnable['!trap']) || isset($resultEnable['!fatal'])) {
            // Jika gagal diaktifkan, biarkan user disabled
            // dan nonaktifkan Scheduler.
            $API->comm("/system/scheduler/disable", array(
                "numbers" => $name
            ));

            echo "<script>alert('Scheduler dibuat, tetapi PPP Secret gagal diaktifkan.');</script>";
            return;
        }
    }

    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
    }


}
?>
<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-plus"></i> Add PPP Secrets <small id="loader" style="display: none;"><i><i
                                class='fa fa-circle-o-notch fa-spin'></i> Processing... </i></small></h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <div>
                        <a class="btn bg-warning" href="./?ppp=secrets&session=<?= $session; ?>"> <i
                                class="fa fa-close btn-mrg"></i> <?= $_close ?></a>
                        <button type="submit" name="save" class="btn bg-primary btn-mrg"><i
                                class="fa fa-save btn-mrg"></i> <?= $_save ?></button>
                    </div>
                    <table class="table">
                        <tr>
                            <td class="align-middle"><?= $_name ?></td>
                            <td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off"
                                    name="name" value="" required="1" autofocus></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Password</td>
                            <td><input class="form-control" type="text" size="4" autocomplete="off" name="password">
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Service</td>
                            <td>
                                <select class="form-control" name="service" required="1">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe">pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Caller ID</td>
                            <td><input class="form-control" type="text" size="4" autocomplete="off" name="callerid">
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Profile</td>
                            <td>
                                <select class="form-control" name="profile" required="1">
                                    <?php
                                    $TotalReg = count($getprofile);
                                    for ($i = 0; $i < $TotalReg; $i++) {
                                        echo "<option>" . $getprofile[$i]['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <hr />
                        <tr>
                            <td class="align-middle">Interval</td>
                            <td><input class="form-control" placeholder="example : 30d" type="text" size="4"
                                    autocomplete="off" name="interval"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function remSpace() {
    var upName = document.getElementsByName("name")[0];
    var newUpName = upName.value.replace(/\s/g, "-");
    //alert("<?php if ($currency == in_array($currency, $cekindo['indo'])) {
    echo "Nama Profile tidak boleh berisi spasi";
} else {
    echo "Profile name can't containing white space!";
} ?>
");
upName.value = newUpName;
upName.focus();
}
</script>