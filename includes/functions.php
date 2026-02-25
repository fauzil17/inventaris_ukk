<?php
require_once __DIR__ . '/../config/database.php';

function getAllBarang() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    // Tambah varian_barang
    $query = "SELECT id_barang as id, kode_barang, nama_barang, varian_barang, 
                     stok_barang as stok, keterangan, harga_satuan, harga_jual
              FROM barang ORDER BY id_barang DESC";
    $result = mysqli_query($conn, $query);
    
    $barang = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $barang[] = $row;
    }
    
    mysqli_close($conn);
    return $barang;
}

function getBarangById($id) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT id_barang as id, kode_barang, nama_barang, varian_barang, 
                     stok_barang as stok, keterangan, harga_satuan, harga_jual 
              FROM barang WHERE id_barang = '$id'";
    $result = mysqli_query($conn, $query);
    $barang = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    return $barang;
}

function addBarang($data) {
    $conn = getConnection();
    
    $kode_barang = mysqli_real_escape_string($conn, $data['kode_barang']);
    $nama_barang = mysqli_real_escape_string($conn, $data['nama_barang']);
    $varian_barang = mysqli_real_escape_string($conn, $data['varian_barang'] ?? '');
    $stok_barang = intval($data['stok']);
    $keterangan = mysqli_real_escape_string($conn, $data['keterangan']);
    $harga_satuan =intval($data['harga_satuan']);
 // hitung harga jual otomatis 50%
    $harga_jual = calculateHargaJual($harga_satuan, 50);
   
    $query = "INSERT INTO barang (kode_barang, nama_barang, varian_barang, stok_barang, keterangan) 
              VALUES ('$kode_barang', '$nama_barang', '$varian_barang', $stok_barang, '$keterangan')";
    
    $result = mysqli_query($conn, $query);
    $success = $result ? mysqli_insert_id($conn) : false;
    mysqli_close($conn);
    
    return $success;
}

function updateBarang($id, $data) {
    $conn = getConnection();
    
    $kode_barang = mysqli_real_escape_string($conn, $data['kode_barang']); 
    $nama_barang = mysqli_real_escape_string($conn, $data['nama_barang']);
    $varian_barang = mysqli_real_escape_string($conn, $data['varian_barang'] ?? '');
    $stok_barang = intval($data['stok']);
    $keterangan = mysqli_real_escape_string($conn, $data['keterangan']);
    $harga_satuan = intval($data['harga_satuan']);
    $harga_jual = intval($data['harga_jual']);
    
    $query = "UPDATE barang SET 
              nama_barang = '$nama_barang',
              varian_barang = '$varian_barang',
              stok_barang = $stok_barang,
              harga_satuan = $harga_satuan,
              harga_jual = $harga_jual,
              WHERE id_barang = '$id'";
    
    $result = mysqli_query($conn, $query);
    mysqli_close($conn);
    
    return $result;
}

function deleteBarang($id) {
    $conn = getConnection();
    $id = mysqli_real_escape_string($conn, $id);

    $query = "DELETE FROM barang WHERE id_barang = '$id'";
    $result = mysqli_query($conn, $query);
    mysqli_close($conn);

    return $result;
}

//fungsi untuk menghitung harga jual
function calculateHargaJual($hargasatuan, $persenMarkup = 50) {
    if (!is_numeric($hargasatuan) || $hargasatuan <= 0) {
        return 0;
    }

   $markup = ($hargasatuan * $persenMarkup) / 100;
   return round($hargasatuan + $markup);
}

// ========== FUNGSI TRANSAKSI ==========
function getAllTransaksi() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $query = "SELECT 
                t.*, 
                b.nama_barang
              FROM transaksi t
              JOIN barang b ON t.id_barang = b.id_barang
              ORDER BY t.tanggal_transaksi DESC";

    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_close($conn);
    return $data;
}


function addTransaksi($data) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    $barang_id = intval($data['barang_id']);
    $jenis = mysqli_real_escape_string($conn, $data['jenis']);
    $jumlah = intval($data['jumlah']);
    $keterangan = mysqli_real_escape_string($conn, $data['keterangan']);

    // Ambil data barang
    $queryBarang = "SELECT stok_barang FROM barang WHERE id_barang = $barang_id";
    $resultBarang = mysqli_query($conn, $queryBarang);
    $barang = mysqli_fetch_assoc($resultBarang);

    if (!$barang) {
        mysqli_close($conn);
        return false;
    }

    // Validasi stok keluar
    if ($jenis == 'keluar' && $barang['stok_barang'] < $jumlah) {
        mysqli_close($conn);
        return false;
    }

    // Insert transaksi
    $query = "INSERT INTO transaksi (id_barang, jenis, jumlah, keterangan, tanggal_transaksi) 
              VALUES ($barang_id, '$jenis', $jumlah, '$keterangan', CURDATE())";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        // Update stok barang
        $operator = ($jenis == 'masuk') ? '+' : '-';
        $update_query = "UPDATE barang SET stok_barang = stok_barang $operator $jumlah WHERE id_barang = $barang_id";
        mysqli_query($conn, $update_query);
    }
    
    mysqli_close($conn);
    return $result;
}

function getTransaksiById($id) {
    $conn = getConnection();
    $id = mysqli_real_escape_string($conn, $id);

    $query = "SELECT 
                t.*, 
                b.nama_barang,
                u.nama_user
              FROM transaksi t
              JOIN barang b ON t.barang_id = b.id_barang
              JOIN user u ON t.user_id = u.id_user
              WHERE t.id_transaksi = '$id'";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    mysqli_close($conn);
    return $data;
}

function deleteTransaksi($id) {
    $conn = getConnection();
    $id = mysqli_real_escape_string($conn, $id);

    $query = "DELETE FROM transaksi WHERE id_transaksi = '$id'";
    $result = mysqli_query($conn, $query);

    mysqli_close($conn);
    return $result;
}



// ========== FUNGSI LAPORAN ==========
function getLaporanHarian($tanggal) {
    $conn = getConnection();
    $tanggal = mysqli_real_escape_string($conn, $tanggal);

    $query = "SELECT 
                t.*, 
                b.nama_barang,
                u.nama_user
              FROM transaksi t
              JOIN barang b ON t.barang_id = b.id_barang
              JOIN user u ON t.user_id = u.id_user
              WHERE DATE(t.tanggal_transaksi) = '$tanggal'
              ORDER BY t.tanggal_transaksi DESC";

    $result = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_close($conn);
    return $data;
}


// ========== FUNGSI USER/AUTH ==========
function verifyLogin($nama_user, $password) {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $nama_user = mysqli_real_escape_string($conn, $nama_user);
    
    $query = "SELECT * FROM user WHERE nama_user = '$nama_user'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Password di database adalah MD5 hash
        $hashed_password = md5($password);
        
        if ($hashed_password === $user['password']) {
            mysqli_close($conn);
            return [
                'id' => $user['id_user'],
                'nama_user' => $user['nama_user'],
                'nama_lengkap' => $user['nama_user'],
                'role' => 'admin'
            ];
        }
    }
    
    mysqli_close($conn);
    return false;
}

// ========== FUNGSI HELPER ==========
function formatRupiah($angka) {
    if (empty($angka) || !is_numeric($angka)) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function showAlert($type, $message) {
    $class = '';
    switch($type) {
        case 'success': $class = 'alert-success'; break;
        case 'error': $class = 'alert-danger'; break;
        case 'warning': $class = 'alert-warning'; break;
        case 'info': $class = 'alert-info'; break;
    }
    
    return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

// Fungsi untuk mendapatkan statistik barang
function getBarangStatistik() {
    $conn = getConnection();
    
    $query = "SELECT 
                COUNT(*) as total_barang,
                SUM(stok_barang) as total_stok,
                SUM(harga_satuan * stok_barang) as nilai_persediaan,
                SUM((harga_jual - harga_satuan) * stok_barang) as potensi_laba
              FROM barang";
    
    $result = mysqli_query($conn, $query);
    $statistik = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    
    return $statistik;
}
?>