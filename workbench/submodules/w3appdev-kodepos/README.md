SQL SCRIPT (MYSQL)
<h3><strong>Daftar Kode Wilayah NKRI</strong> (+KODE POS)</h3>Sumber:<a href="https://www.nomor.net">https://www.nomor.net</a>

<br>Update per 18 Mei 2023:<br/>
<b>38</b> propinsi, <b>514</b> kota+kabupaten, <b>7.277</b> kecamatan/distrik, <b>83.763</b> desa/kelurahan.

<br><b>Contoh Verifikasi</b>

Jumlah Propinsi

<pre><code>SELECT COUNT(*) AS `Total Propinsi`<br>
FROM (SELECT kodewilayah<br>
      FROM kodewilayah2023<br>
      GROUP BY namapropinsi) AS kodewilayah
</code></pre>


Jumlah Kabupaten
<pre><code>SELECT COUNT(*) AS `Total Kabupaten`<br>
FROM (SELECT kodewilayah FROM kodewilayah2023<br>
      WHERE kodekabkota='Kabupaten'<br>
      GROUP BY LEFT(kodewilayah,5)) AS kodewilayah
</code></pre>


Jumlah Kota
<pre><code>SELECT COUNT(*) AS `Total Kota`<br>
FROM (SELECT kodewilayah FROM kodewilayah2023<br>
      WHERE kodekabkota='Kota'<br>
      GROUP BY LEFT(kodewilayah,5)) AS kodewilayah
</code></pre>      


Jumlah Kota+Kabupaten
<pre><code>SELECT COUNT(*) AS `Total Kota+Kabupaten`<br>
FROM (SELECT kodewilayah FROM kodewilayah2023<br>
      GROUP BY LEFT(kodewilayah,5)) AS kodewilayah
</code></pre>


Jumlah Kecamatan
<pre><code>SELECT COUNT(*) AS `Total Kecamatan`<br>
FROM (SELECT kodewilayah FROM kodewilayah2023<br>
      GROUP BY LEFT(kodewilayah,8)) AS kodewilayah
</code></pre>      


Jumlah Desa/Kelurahan
<pre><code>SELECT COUNT(*) AS `Total Desa/Kelurahan`<br>
FROM kodewilayah2023</code></pre>

<b>Contoh Rekap</b>:
<pre><code>SELECT (SELECT COUNT(*)
        FROM (SELECT NULL 
              FROM kodewilayah2023
              GROUP BY namapropinsi) Propinsi) AS 'Total Propinsi',
       (SELECT COUNT(*)
        FROM (SELECT NULL 
              FROM kodewilayah2023 
              GROUP BY LEFT(kodewilayah,5)) KotaKabupaten)  AS `Total Kota+Kabupaten`,
       (SELECT COUNT(*)
        FROM (SELECT NULL 
              FROM kodewilayah2023 
              GROUP BY LEFT(kodewilayah,8)) Kecamatan)  AS `Total Kecamatan`,
       (SELECT COUNT(*) 
        FROM kodewilayah2023)  AS `Total Desa/Kelurahan`
</code></pre>
