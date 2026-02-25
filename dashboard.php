<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

// Hitung statistik
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Total barang
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
$total_barang = mysqli_fetch_assoc($result)['total'];

// Total stok - GANTI: stok_barang bukan stok
$result = mysqli_query($conn, "SELECT SUM(stok_barang) as total FROM barang");
$total_stok = mysqli_fetch_assoc($result)['total'] ?? 0;

// Barang dengan stok rendah - GANTI: stok_barang < 10
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE stok_barang < 10");
$stok_rendah = mysqli_fetch_assoc($result)['total'];

// Total transaksi hari ini
$today = date('Y-m-d');
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal_transaksi) = '$today'");
$transaksi_hari_ini = mysqli_fetch_assoc($result)['total'];

mysqli_close($conn);

// Barang stok rendah
$barang_stok_rendah = getAllBarang();
$barang_stok_rendah = array_filter($barang_stok_rendah, function($item) {
    return $item['stok'] < 10; // stok sudah alias dari stok_barang di fungsi getAllBarang
});

$page_title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventaris</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .nav-link {
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .nav-link i {
            margin-right: 8px;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-box-seam"></i> Sistem Inventaris
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama_lengkap']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar d-md-block">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="barang/index.php">
                                <i class="bi bi-box"></i> Data Barang
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="transaksi/index.php">
                                <i class="bi bi-arrow-left-right"></i> Transaksi
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Statistik Cards -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Barang</h6>
                                        <h2 class="mb-0"><?php echo $total_barang; ?></h2>
                                    </div>
                                    <i class="bi bi-box" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Stok</h6>
                                        <h2 class="mb-0"><?php echo $total_stok; ?></h2>
                                    </div>
                                    <i class="bi bi-stack" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Stok Rendah</h6>
                                        <h2 class="mb-0"><?php echo $stok_rendah; ?></h2>
                                    </div>
                                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Transaksi Hari Ini</h6>
                                        <h2 class="mb-0"><?php echo $transaksi_hari_ini; ?></h2>
                                    </div>
                                    <i class="bi bi-arrow-left-right" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barang dengan Stok Rendah -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-exclamation-triangle text-warning"></i> Barang dengan Stok Rendah
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($barang_stok_rendah) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode Barang</th>
                                                <th>Nama Barang</th>
                                                <th>Varian</th>
                                                <th>Stok</th>
                                                <th>Keterangan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($barang_stok_rendah as $item): ?>
                                            <tr>
                                                 <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                                                
                                                 <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>

                                                 <td><?php echo htmlspecialchars($item['varian_barang']); ?></td>
                                                
                                                <td>
                                                     <span class="badge bg-warning text-dark">
                                                       <?php echo $item['stok']; ?>
                                                     </span>
                                                </td>
                                                
                                                <td><?php echo htmlspecialchars($item['keterangan']); ?></td>
                                                


                                                <td>
                                                    <a href="barang/edit.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> Tidak ada barang dengan stok rendah.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>