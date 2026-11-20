
<style>
	.qrcode {
		height: 100px;
		width: 100px;
	}
	/* Mengatur agar voucher selalu lurus ke bawah dan tidak menyamping */
	.voucher {
		width: 180px;
		border-collapse: collapse;
		display: block;
		margin: 0 auto 15px auto; /* Jarak antar voucher saat cetak banyak */
		padding-bottom: 10px;
		border-bottom: 1px dashed #000; /* Garis potong antar voucher */
		page-break-inside: avoid; /* Mencegah voucher terpotong di tengah halaman */
	}
	@media print {
		@page {
			margin: 0; /* Menghilangkan margin bawaan browser agar tidak geser */
		}
		body {
			margin: 0;
			padding: 0;
		}
	}
</style>

<table class="voucher">
  <tbody>
    <!-- Logo & Hotspotname -->
    <tr>
      <td style="text-align: center; font-size: 14px; font-weight: bold; width: 180px;">
      </td>
    </tr>
    <tr>
      <td style="text-align: center; font-size: 12px; font-weight: bold; border-bottom: 1px solid black; padding-bottom: 5px;">
        <img src="<?= $logo; ?>" alt="logo" style="height: 50px; border: 0;"><br>
      </td>
    </tr>

    <!-- Bagian Utama -->
    <tr>
      <td style="padding-top: 5px;">
        <table style="text-align: center; width: 100%; font-size: 12px; border-collapse: collapse;">
          <tbody>
            
            <!-- Mode Voucher (User = Pass) -->
            <?php if ($usermode == "vc") { ?>

            <tr>
              <td colspan="2" style="border: 1px solid black; font-weight: bold; font-size: 16px; padding: 3px;">
                <?= $username; ?>
              </td>
            </tr>

            <!-- Mode Member (User & Pass) -->
            <?php } elseif ($usermode == "up") { ?>
            <tr>
              <td style="width: 50%; padding-bottom: 2px;">Username</td>
              <td style="width: 50%; padding-bottom: 2px;">Password</td>
            </tr>
            <tr style="font-size: 14px;">
              <td style="border: 1px solid black; font-weight: bold; padding: 3px;"><?= $username; ?></td>
              <td style="border: 1px solid black; font-weight: bold; padding: 3px;"><?= $password; ?></td>
            </tr>
            <?php } ?>

            <!-- QR Code -->
            <?php if ($qr == "yes") { ?>
            <tr>
              <td colspan="2" style="padding-top: 5px;">
                <?= $qrcode ?>
              </td>
            </tr>
            <?php } ?>

            <!-- Harga & Durasi -->
            <tr>
              <td colspan="2" style="border-top: 1px solid black; font-weight: bold; font-size: 11px; padding-top: 5px; margin-top: 5px;">
                <?= $validity; ?> <?= $timelimit; ?> <?= $datalimit; ?> <?= $price; ?>
              </td>
            </tr>

            <!-- Catatan Login -->
            <tr>
              <td colspan="2" style="font-weight: bold; font-size: 9px; padding-top: 2px;">
                Login: http://<?= $dnsname; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        	        