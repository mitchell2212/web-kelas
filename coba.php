<?php
// Mulai sesi PHP untuk menyimpan data login
session_start();

// ============================================================================
// --- KONFIGURASI LOGIN ADMIN ---
// Silakan ganti username dan password di bawah ini sesuai keinginan Anda
// ============================================================================
$admin_username = "admin";
$admin_password = "bajuin123"; 

// --- PROSES LOGOUT ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy(); // Hapus sesi
    header("Location: coba.php");
    exit;
}

// --- PROSES LOGIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_login'])) {
    $in_user = $_POST['username'];
    $in_pass = $_POST['password'];
    
    if ($in_user === $admin_username && $in_pass === $admin_password) {
        // Jika benar, buat penanda sesi bahwa admin sudah masuk
        $_SESSION['admin_logged_in'] = true;
        header("Location: coba.php");
        exit;
    } else {
        $error_msg = "Username atau password salah!";
    }
}

// --- CEK APAKAH SUDAH LOGIN? ---
// Jika belum login, tampilkan form login dan cegah akses ke dashboard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SMA Negeri 1 Bajuin</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .login-bg { background: linear-gradient(-45deg, #4f46e5, #0ea5e9, #3b82f6, #8b5cf6); background-size: 400% 400%; animation: gradientBG 12s ease infinite; min-height: 100vh; }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 420px; padding: 3rem; }
        .logo-container img { width: 90px; height: auto; filter: drop-shadow(0 8px 12px rgba(0,0,0,0.15)); transition: transform 0.3s ease; }
        .logo-container img:hover { transform: scale(1.05); }
        .form-control { background-color: rgba(255, 255, 255, 0.9); border: 1px solid #cbd5e1; padding: 0.75rem 1rem; border-radius: 12px; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15); border-color: #4f46e5; }
        .input-group-text { background-color: rgba(255, 255, 255, 0.9); border-radius: 12px; }
        .btn-login { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border: none; color: #ffffff; border-radius: 12px; transition: all 0.3s ease; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3); color: #ffffff; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center login-bg">
    <div class="glass-card">
        <div class="text-center mb-4 logo-container">
            <img src="bajuin.jpg" alt="Logo SMA 1 Bajuin" class="mb-3">
            <h3 class="fw-extrabold text-dark mb-1">Login Admin</h3>
            <p class="text-secondary small fw-semibold">SMA Negeri 1 Bajuin</p>
        </div>
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger py-2 small fw-bold text-center rounded-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626;">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>
        <form action="coba.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary mb-1">Username</label>
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text border-end-0"><i class="bi bi-person-fill text-primary"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" required placeholder="Masukkan username">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary mb-1">Password</label>
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text border-end-0"><i class="bi bi-shield-lock-fill text-primary"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" required placeholder="Masukkan password">
                </div>
            </div>
            <button type="submit" name="btn_login" class="btn btn-login w-100 py-3 fw-bold fs-6">
                Masuk ke Dashboard <i class="bi bi-arrow-right-circle-fill ms-1"></i>
            </button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}
// ============================================================================
// JIKA KODE MENCAPAI TITIK INI, ARTINYA ADMIN SUDAH BERHASIL LOGIN
// ============================================================================

// --- KONFIGURASI DATABASE ---
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_kelas";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

$kelas_id = $_GET['kelas_id'] ?? null;
$tab = $_GET['tab'] ?? 'rapot';
$current_kelas = null;

if ($kelas_id) {
    $stmt_k = $pdo->prepare("SELECT * FROM kelas WHERE id = ?");
    $stmt_k->execute([$kelas_id]);
    $current_kelas = $stmt_k->fetch(PDO::FETCH_ASSOC);
    if (!$current_kelas) {
        header("Location: coba.php");
        exit;
    }
}

// --- PROSES FORM ---

// 1. Tambah Kelas Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kelas'])) {
    $nama_kelas = trim($_POST['nama_kelas']);
    if (!empty($nama_kelas)) {
        $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
        $stmt->execute([$nama_kelas]);
        $new_id = $pdo->lastInsertId();
        header("Location: coba.php?kelas_id=$new_id&tab=rapot");
        exit;
    }
}

// 2. Tambah Siswa Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_siswa'])) {
    $nisn = trim($_POST['nisn']);
    $nama = trim($_POST['nama']);
    $k_id = (int)$_POST['kelas_id'];
    if (!empty($nisn) && !empty($nama) && $k_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO siswa (kelas_id, nisn, nama) VALUES (?, ?, ?)");
        $stmt->execute([$k_id, $nisn, $nama]);
    }
    header("Location: coba.php?kelas_id=$k_id&tab=siswa");
    exit;
}

// 3. Simpan / Edit Presensi Harian (Menggunakan DELETE lalu INSERT agar berfungsi ganda sebagai UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_presensi'])) {
    $tanggal = $_POST['tanggal'];
    $k_id = (int)$_POST['kelas_id'];
    if (!empty($_POST['status']) && is_array($_POST['status'])) {
        foreach ($_POST['status'] as $siswa_id => $status) {
            $pdo->prepare("DELETE FROM presensi_harian WHERE siswa_id = ? AND tanggal = ?")->execute([$siswa_id, $tanggal]);
            $pdo->prepare("INSERT INTO presensi_harian (siswa_id, tanggal, status) VALUES (?, ?, ?)")->execute([$siswa_id, $tanggal, $status]);
        }
    }
    header("Location: coba.php?kelas_id=$k_id&tab=presensi&success=1");
    exit;
}

// 4. Simpan / Edit Nilai Tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_tugas'])) {
    $nama_tugas = trim($_POST['nama_tugas']);
    $old_nama_tugas = trim($_POST['old_nama_tugas'] ?? ''); // ID penanda saat melakukan edit
    $k_id = (int)$_POST['kelas_id'];
    
    if (!empty($nama_tugas) && !empty($_POST['skor'])) {
        foreach ($_POST['skor'] as $siswa_id => $skor) {
            // Hapus data tugas lama berdasarkan nama_tugas (jika sedang edit) atau nama_tugas saat ini
            $tugas_hapus = !empty($old_nama_tugas) ? $old_nama_tugas : $nama_tugas;
            $pdo->prepare("DELETE FROM nilai_tugas WHERE siswa_id = ? AND nama_tugas = ?")->execute([$siswa_id, $tugas_hapus]);
            // Masukkan data baru
            $pdo->prepare("INSERT INTO nilai_tugas (siswa_id, nama_tugas, skor) VALUES (?, ?, ?)")->execute([$siswa_id, $nama_tugas, (float)$skor]);
        }
    }
    header("Location: coba.php?kelas_id=$k_id&tab=tugas&success=1");
    exit;
}

// 5. Simpan / Edit Ujian (UTS / UAS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_ujian'])) {
    $jenis = $_POST['jenis'];
    $k_id = (int)$_POST['kelas_id'];
    if (!empty($_POST['skor'])) {
        foreach ($_POST['skor'] as $siswa_id => $skor) {
            $pdo->prepare("DELETE FROM nilai_ujian WHERE siswa_id = ? AND jenis = ?")->execute([$siswa_id, $jenis]);
            $pdo->prepare("INSERT INTO nilai_ujian (siswa_id, jenis, skor) VALUES (?, ?, ?)")->execute([$siswa_id, $jenis, (float)$skor]);
        }
    }
    header("Location: coba.php?kelas_id=$k_id&tab=ujian&success=1");
    exit;
}

// 6. Hapus Siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_siswa'])) {
    $s_id = (int)$_POST['siswa_id'];
    $k_id = (int)$_POST['kelas_id'];
    $pdo->prepare("DELETE FROM presensi_harian WHERE siswa_id = ?")->execute([$s_id]);
    $pdo->prepare("DELETE FROM nilai_tugas WHERE siswa_id = ?")->execute([$s_id]);
    $pdo->prepare("DELETE FROM nilai_ujian WHERE siswa_id = ?")->execute([$s_id]);
    $pdo->prepare("DELETE FROM siswa WHERE id = ?")->execute([$s_id]);
    header("Location: coba.php?kelas_id=$k_id&tab=siswa&success=1");
    exit;
}

// 7. Hapus Kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_kelas'])) {
    $k_id = (int)$_POST['kelas_id'];
    $stmt_s = $pdo->prepare("SELECT id FROM siswa WHERE kelas_id = ?");
    $stmt_s->execute([$k_id]);
    $siswas = $stmt_s->fetchAll(PDO::FETCH_COLUMN);
    foreach ($siswas as $s_id) {
        $pdo->prepare("DELETE FROM presensi_harian WHERE siswa_id = ?")->execute([$s_id]);
        $pdo->prepare("DELETE FROM nilai_tugas WHERE siswa_id = ?")->execute([$s_id]);
        $pdo->prepare("DELETE FROM nilai_ujian WHERE siswa_id = ?")->execute([$s_id]);
    }
    $pdo->prepare("DELETE FROM siswa WHERE kelas_id = ?")->execute([$k_id]);
    $pdo->prepare("DELETE FROM kelas WHERE id = ?")->execute([$k_id]);
    header("Location: coba.php?success=1");
    exit;
}

// Ambil Seluruh Siswa pada kelas aktif
$siswa_list = [];
if ($kelas_id) {
    $stmt_siswa = $pdo->prepare("SELECT * FROM siswa WHERE kelas_id = ? ORDER BY nama ASC");
    $stmt_siswa->execute([$kelas_id]);
    $siswa_list = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil Daftar Semua Kelas
$semua_kelas = $pdo->query("
    SELECT k.*, COUNT(s.id) as total_siswa 
    FROM kelas k 
    LEFT JOIN siswa s ON k.id = s.kelas_id 
    GROUP BY k.id 
    ORDER BY k.nama_kelas ASC
")->fetchAll(PDO::FETCH_ASSOC);

function hitungGrade($nilai) {
    if ($nilai >= 85) return ['A', 'bg-success text-white'];
    if ($nilai >= 75) return ['B', 'bg-primary text-white'];
    if ($nilai >= 60) return ['C', 'bg-warning text-dark'];
    if ($nilai >= 50) return ['D', 'bg-warning text-dark'];
    return ['E', 'bg-danger text-white'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Pengelolaan Kelas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-main py-3 mb-4" style="background-color: #4f46e5;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="coba.php">
                <div class="bg-primary text-white p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-mortarboard-fill fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold fs-5 tracking-tight d-block text-white mb-0">SMA 1 BAJUIN</span>
                    <small class="text-white-50" style="font-size: 0.75rem;">Sistem Kelola Akademik & Rapot</small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2">
                <?php if ($current_kelas): ?>
                    <span class="badge bg-indigo-subtle text-white border border-light-subtle fs-6 px-3 py-2 rounded-pill d-flex align-items-center gap-2">
                        <i class="bi bi-door-open-fill text-warning"></i>
                        <span>Kelas: <strong><?= htmlspecialchars($current_kelas['nama_kelas']) ?></strong></span>
                    </span>
                    <a href="coba.php" class="btn btn-outline-light btn-sm rounded-pill px-3 ms-2"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                <?php else: ?>
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill border fw-semibold"><i class="bi bi-person-badge text-primary me-1"></i> Mode Guru</span>
                <?php endif; ?>
                <a href="coba.php?action=logout" class="btn btn-sm btn-danger rounded-pill px-3 ms-2 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if (!$kelas_id): ?>
            <!-- Hero Welcome Section -->
            <div class="card card-custom p-4 p-md-5 mb-4 border-0 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #312e81 0%, #4f46e5 100%);">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold"><i class="bi bi-stars me-1"></i> Selamat Datang Kembali, Admin!</span>
                        <h2 class="fw-extrabold text-white display-6 mb-2">Sistem Pengelolaan Kelas</h2>
                        <p class="text-white-50 mb-0 fs-6">Pilih kelas yang ingin di-input nilainya atau buat ruangan kelas baru.</p>
                    </div>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> <strong>Berhasil!</strong> Aksi telah sukses dilakukan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card card-custom p-4 h-100 shadow-sm border-0">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-plus-square-fill text-primary fs-4"></i>
                            <h5 class="fw-bold mb-0">Buat Kelas Baru</h5>
                        </div>
                        <form action="coba.php" method="POST">
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Nama / Kode Kelas</label>
                                <input type="text" name="nama_kelas" class="form-control form-control-lg" placeholder="Contoh: X IPA 1, XI IPS 2" required>
                            </div>
                            <button type="submit" name="tambah_kelas" class="btn btn-primary w-100 py-3 text-white fw-bold shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"></i> Simpan & Buka Kelas
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card card-custom p-4 h-100 shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grid-fill text-primary fs-4"></i>
                                <h5 class="fw-bold mb-0">Pilih Kelas Yang Ingin Di-Input</h5>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">Total: <?= count($semua_kelas) ?> Kelas</span>
                        </div>

                        <?php if (empty($semua_kelas)): ?>
                            <div class="text-center py-5">
                                <h6 class="fw-bold text-dark">Belum ada kelas terdaftar</h6>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($semua_kelas as $k): ?>
                                    <div class="col-md-6">
                                        <div class="card p-3 border shadow-sm rounded-3" style="cursor: pointer;" onclick="window.location.href='coba.php?kelas_id=<?= $k['id'] ?>&tab=rapot'">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <i class="bi bi-door-closed fs-3 text-secondary"></i>
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($k['nama_kelas']) ?></h6>
                                                        <span class="badge bg-light text-secondary border rounded-pill"><i class="bi bi-people-fill text-primary me-1"></i><?= $k['total_siswa'] ?> Siswa</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <form action="coba.php" method="POST" class="m-0" onclick="event.stopPropagation();" onsubmit="return confirm('PENTING!\nApakah Anda yakin ingin menghapus kelas <?= htmlspecialchars($k['nama_kelas']) ?>?');">
                                                        <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">
                                                        <button type="submit" name="hapus_kelas" class="btn btn-sm btn-light border rounded-circle p-2 text-danger"><i class="bi bi-trash3-fill fs-6"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex align-items-center mb-4">
                <a href="coba.php" class="btn btn-white bg-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;"><i class="bi bi-arrow-left fs-5 text-dark"></i></a>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Ruang Kelas <?= htmlspecialchars($current_kelas['nama_kelas']) ?></h4>
                    <p class="text-muted small mb-0">Kelola nilai, presensi, dan data siswa di kelas ini.</p>
                </div>
            </div>

            <ul class="nav nav-pills nav-pills-custom mb-4 flex-column flex-sm-row gap-1 shadow-sm bg-white p-2 rounded-3">
                <li class="nav-item"><a class="nav-link <?= $tab === 'rapot' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=rapot"><i class="bi bi-award me-1"></i> Rekap Rapot</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab === 'presensi' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=presensi"><i class="bi bi-calendar-check me-1"></i> Presensi</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab === 'tugas' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=tugas"><i class="bi bi-pencil-square me-1"></i> Tugas</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab === 'ujian' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=ujian"><i class="bi bi-file-earmark-text me-1"></i> UTS/UAS</a></li>
                <li class="nav-item ms-sm-auto"><a class="nav-link <?= $tab === 'siswa' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=siswa"><i class="bi bi-person-gear me-1"></i> Kelola Siswa</a></li>
            </ul>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> <strong>Berhasil!</strong> Data telah sukses tersimpan/diperbarui.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- TAB 1: RAPOT -->
            <?php if ($tab === 'rapot'): ?>
                <div class="card card-custom p-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Rekapitulasi Nilai</h5>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?= count($siswa_list) ?> Siswa</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border">
                            <thead class="table-light">
                                <tr>
                                    <th>Siswa</th>
                                    <th class="text-center">Presensi (H/I/S/A)</th>
                                    <th class="text-center">Rata Tugas (30%)</th>
                                    <th class="text-center">UTS (30%)</th>
                                    <th class="text-center">UAS (40%)</th>
                                    <th class="text-center">Nilai Rapot</th>
                                    <th class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($siswa_list)): ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada siswa.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($siswa_list as $s): ?>
                                        <?php
                                            $p = $pdo->prepare("SELECT SUM(status='Hadir') as hadir, SUM(status='Izin') as izin, SUM(status='Sakit') as sakit, SUM(status='Alpa') as alpa FROM presensi_harian WHERE siswa_id = ?");
                                            $p->execute([$s['id']]);
                                            $pres = $p->fetch(PDO::FETCH_ASSOC);
                                            $t = $pdo->prepare("SELECT AVG(skor) as avg_tugas FROM nilai_tugas WHERE siswa_id = ?");
                                            $t->execute([$s['id']]);
                                            $avg_tugas = $t->fetch(PDO::FETCH_ASSOC)['avg_tugas'] ?? 0;
                                            $u = $pdo->prepare("SELECT jenis, skor FROM nilai_ujian WHERE siswa_id = ?");
                                            $u->execute([$s['id']]);
                                            $ujian = $u->fetchAll(PDO::FETCH_KEY_PAIR);
                                            $uts = $ujian['UTS'] ?? 0; $uas = $ujian['UAS'] ?? 0;
                                            $nilai_rapot = ($avg_tugas * 0.30) + ($uts * 0.30) + ($uas * 0.40);
                                            list($grade, $badge_class) = hitungGrade($nilai_rapot);
                                        ?>
                                        <tr>
                                            <td><div class="fw-bold"><?= htmlspecialchars($s['nama']) ?></div><small class="text-muted"><?= htmlspecialchars($s['nisn']) ?></small></td>
                                            <td class="text-center">
                                                <span class="text-success fw-bold"><?= $pres['hadir'] ?? 0 ?> H</span> |
                                                <span class="text-warning fw-bold"><?= $pres['izin'] ?? 0 ?> I</span> |
                                                <span class="text-info fw-bold"><?= $pres['sakit'] ?? 0 ?> S</span> |
                                                <span class="text-danger fw-bold"><?= $pres['alpa'] ?? 0 ?> A</span>
                                            </td>
                                            <td class="text-center fw-semibold"><?= number_format($avg_tugas, 1) ?></td>
                                            <td class="text-center fw-semibold"><?= number_format($uts, 1) ?></td>
                                            <td class="text-center fw-semibold"><?= number_format($uas, 1) ?></td>
                                            <td class="text-center fw-bold text-primary"><?= number_format($nilai_rapot, 2) ?></td>
                                            <td class="text-center"><span class="badge <?= $badge_class ?> p-2 px-3 fw-bold"><?= $grade ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- TAB 2: PRESENSI -->
            <?php elseif ($tab === 'presensi'): ?>
                <?php 
                    // Logika Tarik Data Edit
                    $edit_tgl = $_GET['edit_tgl'] ?? date('Y-m-d');
                    $data_edit_presensi = [];
                    if (isset($_GET['edit_tgl'])) {
                        $stmt = $pdo->prepare("SELECT siswa_id, status FROM presensi_harian WHERE tanggal = ? AND siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?)");
                        $stmt->execute([$edit_tgl, $kelas_id]);
                        $data_edit_presensi = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    }
                    
                    // Logika Riwayat Presensi
                    $riwayat_presensi = $pdo->prepare("SELECT tanggal, COUNT(siswa_id) as total FROM presensi_harian WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?) GROUP BY tanggal ORDER BY tanggal DESC");
                    $riwayat_presensi->execute([$kelas_id]);
                    $list_presensi = $riwayat_presensi->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4 shadow-sm border-0">
                            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-event text-primary me-2"></i>Form Input / Edit Presensi</h5>
                            <?php if (empty($siswa_list)): ?>
                                <p class="text-muted">Belum ada siswa.</p>
                            <?php else: ?>
                                <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=presensi" method="POST">
                                    <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                                    <div class="col-md-5 mb-4">
                                        <label class="form-label fw-semibold">Tanggal Pertemuan</label>
                                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($edit_tgl) ?>" required>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle border">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Siswa</th>
                                                    <th class="text-center">Status Kehadiran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($siswa_list as $s): ?>
                                                    <?php $status_siswa = $data_edit_presensi[$s['id']] ?? 'Hadir'; ?>
                                                    <tr>
                                                        <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                        <td class="text-center">
                                                            <div class="btn-group" role="group">
                                                                <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="h_<?= $s['id'] ?>" value="Hadir" <?= $status_siswa == 'Hadir' ? 'checked' : '' ?>>
                                                                <label class="btn btn-outline-success btn-sm px-3" for="h_<?= $s['id'] ?>">Hadir</label>
                                                                <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="i_<?= $s['id'] ?>" value="Izin" <?= $status_siswa == 'Izin' ? 'checked' : '' ?>>
                                                                <label class="btn btn-outline-warning btn-sm px-3" for="i_<?= $s['id'] ?>">Izin</label>
                                                                <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="s_<?= $s['id'] ?>" value="Sakit" <?= $status_siswa == 'Sakit' ? 'checked' : '' ?>>
                                                                <label class="btn btn-outline-info btn-sm px-3" for="s_<?= $s['id'] ?>">Sakit</label>
                                                                <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="a_<?= $s['id'] ?>" value="Alpa" <?= $status_siswa == 'Alpa' ? 'checked' : '' ?>>
                                                                <label class="btn btn-outline-danger btn-sm px-3" for="a_<?= $s['id'] ?>">Alpa</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" name="simpan_presensi" class="btn btn-primary px-4 py-2 mt-3 fw-bold"><i class="bi bi-save me-1"></i> Simpan Data</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 shadow-sm border-0 bg-light">
                            <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-clock-history me-2"></i>Riwayat Presensi</h6>
                            <?php if(empty($list_presensi)): ?>
                                <p class="text-muted small">Belum ada data.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush border rounded-3">
                                    <?php foreach($list_presensi as $rp): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-calendar2-day text-secondary me-2"></i><?= date('d M Y', strtotime($rp['tanggal'])) ?></span>
                                            <a href="coba.php?kelas_id=<?= $kelas_id ?>&tab=presensi&edit_tgl=<?= $rp['tanggal'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <!-- TAB 3: TUGAS -->
            <?php elseif ($tab === 'tugas'): ?>
                <?php 
                    // Logika Tarik Data Edit
                    $edit_tugas = $_GET['edit_tugas'] ?? '';
                    $data_edit_tugas = [];
                    if (!empty($edit_tugas)) {
                        $stmt = $pdo->prepare("SELECT siswa_id, skor FROM nilai_tugas WHERE nama_tugas = ? AND siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?)");
                        $stmt->execute([$edit_tugas, $kelas_id]);
                        $data_edit_tugas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    }
                    
                    // Logika Riwayat Tugas
                    $riwayat_tugas = $pdo->prepare("SELECT nama_tugas, AVG(skor) as rata FROM nilai_tugas WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?) GROUP BY nama_tugas ORDER BY nama_tugas ASC");
                    $riwayat_tugas->execute([$kelas_id]);
                    $list_tugas = $riwayat_tugas->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4 shadow-sm border-0">
                            <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-check text-primary me-2"></i>Form Input / Edit Tugas</h5>
                            <?php if (empty($siswa_list)): ?>
                                <p class="text-muted">Belum ada siswa.</p>
                            <?php else: ?>
                                <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=tugas" method="POST">
                                    <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                                    <input type="hidden" name="old_nama_tugas" value="<?= htmlspecialchars($edit_tugas) ?>">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold">Nama / Judul Tugas</label>
                                        <input type="text" name="nama_tugas" class="form-control" placeholder="Contoh: Tugas 1 - Algoritma" value="<?= htmlspecialchars($edit_tugas) ?>" required>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle border">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Siswa</th>
                                                    <th style="width: 220px;">Nilai Tugas (0-100)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($siswa_list as $s): ?>
                                                    <?php $skor_siswa = $data_edit_tugas[$s['id']] ?? 0; ?>
                                                    <tr>
                                                        <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                        <td><input type="number" step="0.1" name="skor[<?= $s['id'] ?>]" class="form-control" value="<?= $skor_siswa ?>" min="0" max="100"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" name="simpan_tugas" class="btn btn-primary px-4 py-2 mt-3 fw-bold"><i class="bi bi-save me-1"></i> Simpan Data</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 shadow-sm border-0 bg-light">
                            <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-journal-album me-2"></i>Riwayat Tugas</h6>
                            <?php if(empty($list_tugas)): ?>
                                <p class="text-muted small">Belum ada data.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush border rounded-3">
                                    <?php foreach($list_tugas as $rt): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-dark text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($rt['nama_tugas']) ?>"><?= htmlspecialchars($rt['nama_tugas']) ?></span>
                                            <a href="coba.php?kelas_id=<?= $kelas_id ?>&tab=tugas&edit_tugas=<?= urlencode($rt['nama_tugas']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <!-- TAB 4: UJIAN -->
            <?php elseif ($tab === 'ujian'): ?>
                <?php 
                    // Logika Tarik Data Edit
                    $edit_ujian = $_GET['edit_ujian'] ?? 'UTS';
                    $data_edit_ujian = [];
                    if (isset($_GET['edit_ujian'])) {
                        $stmt = $pdo->prepare("SELECT siswa_id, skor FROM nilai_ujian WHERE jenis = ? AND siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?)");
                        $stmt->execute([$edit_ujian, $kelas_id]);
                        $data_edit_ujian = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    }
                    
                    // Logika Riwayat Ujian
                    $riwayat_ujian = $pdo->prepare("SELECT jenis FROM nilai_ujian WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas_id = ?) GROUP BY jenis");
                    $riwayat_ujian->execute([$kelas_id]);
                    $list_ujian = $riwayat_ujian->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4 shadow-sm border-0">
                            <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Form Input / Edit Ujian</h5>
                            <?php if (empty($siswa_list)): ?>
                                <p class="text-muted">Belum ada siswa.</p>
                            <?php else: ?>
                                <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=ujian" method="POST">
                                    <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                                    <div class="col-md-5 mb-4">
                                        <label class="form-label fw-semibold">Pilih Jenis Ujian</label>
                                        <select name="jenis" class="form-select" required>
                                            <option value="UTS" <?= $edit_ujian === 'UTS' ? 'selected' : '' ?>>UTS (Ujian Tengah Semester)</option>
                                            <option value="UAS" <?= $edit_ujian === 'UAS' ? 'selected' : '' ?>>UAS (Ujian Akhir Semester)</option>
                                        </select>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle border">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Siswa</th>
                                                    <th style="width: 220px;">Nilai Ujian (0-100)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($siswa_list as $s): ?>
                                                    <?php $skor_siswa = $data_edit_ujian[$s['id']] ?? 0; ?>
                                                    <tr>
                                                        <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                        <td><input type="number" step="0.1" name="skor[<?= $s['id'] ?>]" class="form-control" value="<?= $skor_siswa ?>" min="0" max="100"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" name="simpan_ujian" class="btn btn-primary px-4 py-2 mt-3 fw-bold"><i class="bi bi-save me-1"></i> Simpan Data</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 shadow-sm border-0 bg-light">
                            <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-check-fill me-2"></i>Data Ujian Tersimpan</h6>
                            <?php if(empty($list_ujian)): ?>
                                <p class="text-muted small">Belum ada data ujian yang disimpan.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush border rounded-3">
                                    <?php foreach($list_ujian as $ru): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($ru) ?></span>
                                            <a href="coba.php?kelas_id=<?= $kelas_id ?>&tab=ujian&edit_ujian=<?= $ru ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit Nilai</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <!-- TAB 5: SISWA -->
            <?php elseif ($tab === 'siswa'): ?>
                <div class="card card-custom p-4 shadow-sm border-0">
                    <h5 class="fw-bold mb-3"><i class="bi bi-person-plus text-primary me-2"></i>Tambah Siswa Baru</h5>
                    <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=siswa" method="POST" class="row g-3 mb-4">
                        <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                        <div class="col-md-4">
                            <input type="text" name="nisn" class="form-control" placeholder="NISN Siswa" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap Siswa" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="tambah_siswa" class="btn btn-success w-100 fw-bold py-2"><i class="bi bi-plus-circle me-1"></i> Tambah Siswa</button>
                        </div>
                    </form>

                    <h6 class="fw-bold border-top pt-4 mb-3">Daftar Siswa Terdaftar</h6>
                    <?php if (empty($siswa_list)): ?>
                        <p class="text-muted">Belum ada siswa terdaftar.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush border rounded-3">
                            <?php foreach ($siswa_list as $s): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                            <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($s['nama']) ?></strong>
                                            <span class="text-muted ms-2 small">(NISN: <?= htmlspecialchars($s['nisn']) ?>)</span>
                                        </div>
                                    </div>
                                    <form action="coba.php" method="POST" class="mb-0" onsubmit="return confirm('Yakin ingin menghapus siswa ini? Seluruh data nilainya akan ikut terhapus!');">
                                        <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                                        <input type="hidden" name="siswa_id" value="<?= $s['id'] ?>">
                                        <button type="submit" name="hapus_siswa" class="btn btn-sm btn-outline-danger rounded-circle p-2" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-trash-fill"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>