<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
} else {

    $getprofile = $API->comm("/ppp/profile/print");

    /*
     * Ambil PPP Secret yang akan diedit.
     */
    if (substr($secretbyname, 0, 1) == "*") {
        $secretbyname = $secretbyname;
    } elseif (substr($secretbyname, 0, 1) != "") {
        $getsecret = $API->comm("/ppp/secret/print", array(
            "?name" => "$secretbyname"
        ));

        $secretbyname = $getsecret[0]['.id'] ?? '';

        if ($secretbyname == "") {
            echo "<b>Secrets not found</b>";
            exit;
        }
    } else {
        $secretbyname = substr($secretbyname, 7);
    }

    $getsecret = $API->comm("/ppp/secret/print", array(
        "?.id" => "$secretbyname"
    ));

    $secretdetail = $getsecret[0] ?? array();

    $sid = $secretdetail['.id'] ?? '';
    $sname = $secretdetail['name'] ?? '';
    $password = $secretdetail['password'] ?? '';
    $service = $secretdetail['service'] ?? 'any';
    $callerid = $secretdetail['caller-id'] ?? '';
    $profile = $secretdetail['profile'] ?? '';

    if ($sid == '') {
        echo "<b>PPP Secret not found</b>";
        exit;
    }

    /*
     * Cari Scheduler berdasarkan nama lama PPP Secret.
     * Jika variabel schedulerbyname tidak tersedia,
     * gunakan nama PPP Secret saat ini.
     */
    $oldSchedulerName = $schedulerbyname ?? '';

    if ($oldSchedulerName == '') {
        $oldSchedulerName = $sname;
    }

    $getsch = $API->comm("/system/scheduler/print", array(
        "?name" => "$oldSchedulerName"
    ));

    $sechdulerdetail = $getsch[0] ?? array();

    $schid = $sechdulerdetail['.id'] ?? '';
    $schname = $sechdulerdetail['name'] ?? '';
    $schinterval = $sechdulerdetail['interval'] ?? '';
    $schon = $sechdulerdetail['on-event'] ?? '';

    /*
     * Fungsi untuk memeriksa respons error dari RouterOS API.
     */
    $routerCommandOK = function ($result) {
        if (isset($result['!trap']) || isset($result['!fatal'])) {
            return false;
        }

        return true;
    };

    /*
     * Proses penyimpanan form.
     */
    $saveMessage = '';

    if (isset($_POST['name'])) {

        $name = trim(preg_replace('/\s+/', '-', $_POST['name']));
        $password = $_POST['password'] ?? '';
        $service = $_POST['service'] ?? 'any';
        $callerid = $_POST['callerid'] ?? '';
        $profile = $_POST['profile'] ?? '';
        $interval = trim($_POST['interval'] ?? '');

        /*
         * Validasi nama PPP Secret.
         */
        if ($name == '') {
            $saveMessage = "Nama PPP Secret tidak boleh kosong.";
        }

        /*
         * Konversi durasi RouterOS menjadi detik.
         * Format yang didukung:
         * 30d
         * 7d
         * 12h
         * 1w
         * 1w2d3h
         * 2d12h30m
         */
        $durationSeconds = 0;

        if ($saveMessage == '' && $interval != '') {

            $duration = strtolower($interval);

            if (!preg_match('/^(?:[0-9]+[wdhms])+$/', $duration)) {
                $saveMessage = "Format Interval tidak valid. Contoh: 30d, 7d, 12h, 1w2d.";
            } else {

                preg_match_all('/([0-9]+)([wdhms])/', $duration, $matches, PREG_SET_ORDER);

                $unitSeconds = array(
                    'w' => 604800,
                    'd' => 86400,
                    'h' => 3600,
                    'm' => 60,
                    's' => 1
                );

                $unitOrder = array(
                    'w' => 1,
                    'd' => 2,
                    'h' => 3,
                    'm' => 4,
                    's' => 5
                );

                $lastOrder = 0;
                $usedUnits = array();

                foreach ($matches as $part) {

                    $value = (int)$part[1];
                    $unit = $part[2];
                    $order = $unitOrder[$unit];

                    if ($value <= 0) {
                        $saveMessage = "Nilai Interval harus lebih besar dari nol.";
                        break;
                    }

                    if (isset($usedUnits[$unit]) || $order <= $lastOrder) {
                        $saveMessage = "Urutan atau satuan Interval tidak valid.";
                        break;
                    }

                    $durationSeconds += $value * $unitSeconds[$unit];
                    $usedUnits[$unit] = true;
                    $lastOrder = $order;
                }

                if ($durationSeconds <= 0 && $saveMessage == '') {
                    $saveMessage = "Interval harus lebih besar dari nol.";
                }
            }
        }

        /*
         * Susun skrip Scheduler.
         * Nama PPP Secret menggunakan tanda kutip agar pencarian nama
         * tidak mudah salah ketika nama mengandung karakter khusus.
         */
        $safeName = str_replace(
            array("\\", "\"", "$"),
            array("\\\\", "\\\"", "\\$"),
            $name
        );

        $on_event = '/ppp secret set disabled=yes [find where name="' . $safeName . '"]; ' .
            '/system scheduler disable [find where name="' . $safeName . '"]';

        /*
         * Jika Interval kosong:
         * - Nonaktifkan PPP Secret.
         * - Jika Scheduler tersedia, perbarui namanya bila perlu
         *   dan nonaktifkan Scheduler.
         * - Scheduler tidak dihapus.
         */
        if ($saveMessage == '' && $interval == '') {

            $secretResult = $API->comm("/ppp/secret/set", array(
                ".id" => "$sid",
                "name" => "$name",
                "password" => "$password",
                "service" => "$service",
                "caller-id" => "$callerid",
                "profile" => "$profile",
                "disabled" => "yes"
            ));

            if (!$routerCommandOK($secretResult)) {
                $saveMessage = "Gagal memperbarui PPP Secret di RouterOS.";
            } else {

                if ($schid != '') {

                    $schedulerResult = $API->comm("/system/scheduler/set", array(
                        ".id" => "$schid",
                        "name" => "$name",
                        "disabled" => "yes",
                        "on-event" => "$on_event"
                    ));

                    if (!$routerCommandOK($schedulerResult)) {
                        $saveMessage = "PPP Secret dinonaktifkan, tetapi Scheduler gagal diperbarui.";
                    }
                }

                if ($saveMessage == '') {
                    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
                    exit;
                }
            }
        }

        /*
         * Jika Interval diisi:
         * - Hitung waktu kedaluwarsa sejak tombol Save ditekan.
         * - Buat atau perbarui Scheduler.
         * - Scheduler dijalankan satu kali pada waktu kedaluwarsa.
         * - Aktifkan PPP Secret setelah Scheduler berhasil disiapkan.
         */
        if ($saveMessage == '' && $interval != '') {

            $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
            $expiry = $now->modify('+' . $durationSeconds . ' seconds');

            $start_date = $expiry->format('M/d/Y');
            $start_time = $expiry->format('H:i:s');

            if ($schid != '') {

                $schedulerResult = $API->comm("/system/scheduler/set", array(
                    ".id" => "$schid",
                    "name" => "$name",
                    "start-date" => "$start_date",
                    "start-time" => "$start_time",
                    "interval" => "0s",
                    "on-event" => "$on_event",
                    "disabled" => "no"
                ));

            } else {

                $schedulerResult = $API->comm("/system/scheduler/add", array(
                    "name" => "$name",
                    "start-date" => "$start_date",
                    "start-time" => "$start_time",
                    "interval" => "0s",
                    "on-event" => "$on_event",
                    "disabled" => "no"
                ));
            }

            if (!$routerCommandOK($schedulerResult)) {
                $saveMessage = "Gagal membuat atau memperbarui Scheduler. PPP Secret belum diaktifkan.";
            } else {

                $secretResult = $API->comm("/ppp/secret/set", array(
                    ".id" => "$sid",
                    "name" => "$name",
                    "password" => "$password",
                    "service" => "$service",
                    "caller-id" => "$callerid",
                    "profile" => "$profile",
                    "disabled" => "no"
                ));

                if (!$routerCommandOK($secretResult)) {

                    /*
                     * Jika PPP Secret gagal diperbarui,
                     * nonaktifkan Scheduler agar tidak ada jadwal
                     * kedaluwarsa yang berjalan untuk data yang gagal disimpan.
                     */
                    if ($schid != '') {
                        $API->comm("/system/scheduler/set", array(
                            ".id" => "$schid",
                            "disabled" => "yes"
                        ));
                    } else {
                        $newScheduler = $API->comm("/system/scheduler/print", array(
                            "?name" => "$name"
                        ));

                        if (isset($newScheduler[0]['.id'])) {
                            $API->comm("/system/scheduler/set", array(
                                ".id" => $newScheduler[0]['.id'],
                                "disabled" => "yes"
                            ));
                        }
                    }

                    $saveMessage = "Scheduler berhasil disiapkan, tetapi PPP Secret gagal diperbarui.";
                } else {
                    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
                    exit;
                }
            }
        }
    }
}
?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> Edit PPP Secret</h3>
            </div>

            <div class="card-body">

                <?php if ($saveMessage != '') { ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($saveMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php } ?>

                <form autocomplete="off" method="post" action="">

                    <div>
                        <a class="btn bg-warning" href="./?ppp=secrets&session=<?= $session; ?>">
                            <i class="fa fa-close"></i> <?= $_close ?>
                        </a>

                        <button type="submit" name="save" class="btn bg-primary">
                            <i class="fa fa-save"></i> <?= $_save ?>
                        </button>
                    </div>

                    <table class="table">

                        <tr>
                            <td class="align-middle"><?= $_name ?></td>
                            <td>
                                <input class="form-control"
                                    type="text"
                                    onchange="remSpace();"
                                    autocomplete="off"
                                    name="name"
                                    value="<?= htmlspecialchars($sname, ENT_QUOTES, 'UTF-8'); ?>"
                                    required="1"
                                    autofocus>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">Password</td>
                            <td>
                                <input class="form-control"
                                    type="text"
                                    size="4"
                                    autocomplete="off"
                                    name="password"
                                    value="<?= htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">Service</td>
                            <td>
                                <select class="form-control" name="service" required="1">
                                    <?php
                                    $services = array(
                                        'any',
                                        'async',
                                        'l2tp',
                                        'ovpn',
                                        'pppoe',
                                        'pptp',
                                        'sstp'
                                    );

                                    foreach ($services as $serviceOption) {
                                        $selected = ($service == $serviceOption) ? 'selected' : '';
                                        echo '<option value="' . $serviceOption . '" ' . $selected . '>' .
                                            $serviceOption . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">Caller ID</td>
                            <td>
                                <input class="form-control"
                                    type="text"
                                    size="4"
                                    autocomplete="off"
                                    name="callerid"
                                    value="<?= htmlspecialchars($callerid, ENT_QUOTES, 'UTF-8'); ?>">
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">Profile</td>
                            <td>
                                <select class="form-control" name="profile" required="1">
                                    <option value="<?= htmlspecialchars($profile, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($profile, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>

                                    <?php
                                    $TotalReg = count($getprofile);

                                    for ($i = 0; $i < $TotalReg; $i++) {
                                        $profileName = $getprofile[$i]['name'];

                                        if ($profileName != $profile) {
                                            echo "<option value='" .
                                                htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8') .
                                                "'>" .
                                                htmlspecialchars($profileName, ENT_QUOTES, 'UTF-8') .
                                                "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-middle">Interval</td>
                            <td>
                                <!-- Sengaja kosong saat edit.
                                     Durasi tidak diambil dari Scheduler lama. -->
                                <input class="form-control"
                                    value=""
                                    type="text"
                                    size="4"
                                    autocomplete="off"
                                    name="interval"
                                    placeholder="Contoh: 30d, 7d, 12h">
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
