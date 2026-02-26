<html>
    <head>
        <title>Data Laporan</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
    </head>
    <body>
        <div class="container">
            <br><br>
                <center>
                <h2 class="text-primary">DATA PELAPORAN LABKOM FASTIKOM</h2>
                </center>
            <br>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th><center>NIM</center></th>
                        <th><center>NAMA</center></th>
                        <th><center>KELAS</center></th>
                        <th><center>LAB</center></th>
                        <th><center>KELUHAN</center></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include "koneksi.php";
                    $batas = 5;
                    $halaman = isset($_GET['halaman'])?(int)$_GET['halaman'] : 1;
                    $halaman_awal = ($halaman>1) ? ($halaman * $batas) - $batas : 0;
                    $previous = $halaman - 1;
                    $next = $halaman + 1;
                    $data = mysqli_query($kon,"select * from datakendala");
                    $jumlah_data = mysqli_num_rows($data);
                    $total_halaman = ceil($jumlah_data / $batas);
                    $data_menu = mysqli_query($kon,"select * from datakendala limit
                    $halaman_awal, $batas");
                    $nomor = $halaman_awal+1;
                    while($d = mysqli_fetch_array($data_menu)){
                    ?>
                    <tr>
                    <td><center><?php echo $d['nim']; ?></center></td>
                    <td><center><?php echo $d['nama']; ?></center></td>
                    <td><center><?php echo $d['kelas']; ?></center></td>
                    <td><center><?php echo $d['lab']; ?></center></td>
                    <td><center><?php echo $d['keluhan']; ?></center></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                    <a class="page-link" <?php if($halaman> 1){ echo
                    "href='?halaman=$previous'"; } ?>>Previous</a>
                    </li>
                    <?php
                    for($x=1;$x<=$total_halaman;$x++){
                    ?>
                    <li class="page-item"><a class="page-link" href="?halaman=<?php
                    echo $x ?>"><?php echo $x; ?></a></li>
                    <?php
                    }
                    ?> 
                    <li class="page-item">
                    <a class="page-link" <?php if($halaman < $total_halaman) { echo
                    "href='?halaman=$next'"; } ?>>Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </body>
</html>