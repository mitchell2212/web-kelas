<?php
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

// Cek data kelas terpilih
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

// 3. Simpan Presensi Harian
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

// 4. Simpan Nilai Tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_tugas'])) {
    $nama_tugas = trim($_POST['nama_tugas']);
    $k_id = (int)$_POST['kelas_id'];
    if (!empty($nama_tugas) && !empty($_POST['skor'])) {
        foreach ($_POST['skor'] as $siswa_id => $skor) {
            $pdo->prepare("INSERT INTO nilai_tugas (siswa_id, nama_tugas, skor) VALUES (?, ?, ?)")->execute([$siswa_id, $nama_tugas, (float)$skor]);
        }
    }
    header("Location: coba.php?kelas_id=$k_id&tab=tugas&success=1");
    exit;
}

// 5. Simpan Ujian (UTS / UAS)
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
    <title>Classroom Hub - System Pengelolaan Kelas</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --bg-canvas: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-canvas);
            color: #1e293b;
        }

        /* Top Header Navbar */
        .navbar-main {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Glass / Elevation Cards */
        .card-custom { 
            border: 1px solid var(--card-border);
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
        }

        .card-custom:hover {
            box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.06);
        }

        /* Interactive Class Cards */
        .class-card {
            border: 2px solid transparent;
            border-radius: 18px;
            background: #ffffff;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .class-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.15);
        }

        .class-card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 700;
        }

        /* Nav Pills Styling */
        .nav-pills-custom {
            background: #ffffff;
            padding: 6px;
            border-radius: 16px;
            border: 1px solid var(--card-border);
        }

        .nav-pills-custom .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link:hover {
            color: var(--primary-color);
            background: #f1f5f9;
        }

        .nav-pills-custom .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, #6366f1 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        /* Custom Inputs & Buttons */
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, #6366f1 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .btn-primary-gradient:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #4f46e5 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
            color: #fff;
        }

        /* Table Styling */
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-custom td {
            padding: 1rem;
            vertical-align: middle;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-main py-3 mb-4">
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
                    <a href="coba.php" class="btn btn-outline-light btn-sm rounded-pill px-3 ms-2">
                        <i class="bi bi-arrow-left-right me-1"></i> Ganti Kelas
                    </a>
                <?php else: ?>
                    <span class="badge bg-white text-dark px-3 py-2 rounded-pill border fw-semibold">
                        <i class="bi bi-person-badge text-primary me-1"></i> Mode Guru
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <!-- TAMPILAN 1: PILIH / TAMBAH KELAS (JIKA BELUM MEMILIH KELAS) -->
        <?php if (!$kelas_id): ?>

            <!-- Hero Welcome Section -->
            <div class="card card-custom p-4 p-md-5 mb-4 border-0 text-white position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #312e81 0%, #4f46e5 100%);">
                <div class="row align-items-center position-relative" style="z-index: 2;">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold">
                            <i class="bi bi-stars me-1"></i> Selamat Datang Kembali
                        </span>
                        <h2 class="fw-extrabold text-white display-6 mb-2">Sistem Pengelolaan Kelas</h2>
                        <p class="text-white-50 mb-0 fs-6">Pilih kelas yang ingin di-input nilainya atau buat ruangan kelas baru untuk memulai rekapitulasi.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Form Buat Kelas Baru (Kiri) -->
                <div class="col-lg-4">
                    <div class="card card-custom p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-plus-square-fill text-primary fs-4"></i>
                            <h5 class="fw-bold mb-0">Buat Kelas Baru</h5>
                        </div>
                        
                        <form action="coba.php" method="POST">
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Nama / Kode Kelas</label>
                                <input type="text" name="nama_kelas" class="form-control form-control-lg" placeholder="Contoh: X IPA 1, XI IPS 2" required>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Nama kelas akan digunakan untuk mengelompokkan data siswa.
                                </small>
                            </div>
                            <button type="submit" name="tambah_kelas" class="btn btn-primary-gradient w-100 py-3 text-white fw-bold shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"></i> Simpan & Buka Kelas
                            </button>
                        </form>
                    </div>
                </div>

                <!-- List Daftar Kelas (Kanan) -->
                <div class="col-lg-8">
                    <div class="card card-custom p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grid-fill text-primary fs-4"></i>
                                <h5 class="fw-bold mb-0">Pilih Kelas Yang Ingin Di-Input</h5>
                            </div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">
                                Total: <?= count($semua_kelas) ?> Kelas
                            </span>
                        </div>

                        <?php if (empty($semua_kelas)): ?>
                            <div class="text-center py-5">
                                <div class="avatar-circle mx-auto mb-3 bg-light text-muted" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    <i class="bi bi-folder-plus"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Belum ada kelas terdaftar</h6>
                                <p class="text-muted small">Gunakan form di sebelah kiri untuk membuat kelas pertama Anda.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($semua_kelas as $k): ?>
                                    <div class="col-md-6">
                                        <div class="class-card p-3 border" onclick="window.location.href='coba.php?kelas_id=<?= $k['id'] ?>&tab=rapot'">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="class-card-icon">
                                                        <i class="bi bi-door-closed"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($k['nama_kelas']) ?></h6>
                                                        <span class="badge bg-light text-secondary border rounded-pill">
                                                            <i class="bi bi-people-fill text-primary me-1"></i><?= $k['total_siswa'] ?> Siswa
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="btn btn-sm btn-light border rounded-circle p-2 text-primary">
                                                    <i class="bi bi-chevron-right fs-6"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- TAMPILAN 2: MENU UTAMA SETELAH KELAS DIPILIH -->
        <?php else: ?>

            <!-- Navigation Tabs Bar -->
            <ul class="nav nav-pills nav-pills-custom mb-4 flex-column flex-sm-row gap-1 shadow-sm">
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'rapot' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=rapot">
                        <i class="bi bi-award me-1"></i> Rekap Rapot
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'presensi' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=presensi">
                        <i class="bi bi-calendar-check me-1"></i> Input Presensi Harian
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'tugas' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=tugas">
                        <i class="bi bi-pencil-square me-1"></i> Input Tugas Harian
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'ujian' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=ujian">
                        <i class="bi bi-file-earmark-text me-1"></i> Input UTS / UAS
                    </a>
                </li>
                <li class="nav-item ms-sm-auto">
                    <a class="nav-link <?= $tab === 'siswa' ? 'active' : '' ?>" href="coba.php?kelas_id=<?= $kelas_id ?>&tab=siswa">
                        <i class="bi bi-person-gear me-1"></i> Kelola Siswa
                    </a>
                </li>
            </ul>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> <strong>Berhasil!</strong> Data telah sukses tersimpan ke database.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- TAB 1: REKAP RAPOT -->
            <?php if ($tab === 'rapot'): ?>
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i>Rekapitulasi Nilai & Kehadiran</h5>
                            <p class="text-muted small mb-0">Kelas: <strong><?= htmlspecialchars($current_kelas['nama_kelas']) ?></strong></p>
                        </div>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?= count($siswa_list) ?> Siswa Terdaftar</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th class="text-center">Presensi (H/I/S/A)</th>
                                    <th class="text-center">Tidak Hadir</th>
                                    <th class="text-center">Rata Tugas (30%)</th>
                                    <th class="text-center">UTS (30%)</th>
                                    <th class="text-center">UAS (40%)</th>
                                    <th class="text-center">Nilai Rapot</th>
                                    <th class="text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($siswa_list)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2 text-light-emphasis"></i>
                                            Belum ada siswa di kelas ini. Buka tab <strong>Kelola Siswa</strong> untuk menambah data siswa.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($siswa_list as $s): ?>
                                        <?php
                                            // Query Presensi
                                            $p = $pdo->prepare("SELECT 
                                                SUM(status='Hadir') as hadir,
                                                SUM(status='Izin') as izin,
                                                SUM(status='Sakit') as sakit,
                                                SUM(status='Alpa') as alpa
                                                FROM presensi_harian WHERE siswa_id = ?");
                                            $p->execute([$s['id']]);
                                            $pres = $p->fetch(PDO::FETCH_ASSOC);

                                            $hadir = $pres['hadir'] ?? 0;
                                            $izin = $pres['izin'] ?? 0;
                                            $sakit = $pres['sakit'] ?? 0;
                                            $alpa = $pres['alpa'] ?? 0;
                                            $total_absen = $izin + $sakit + $alpa;

                                            // Query Rata Tugas
                                            $t = $pdo->prepare("SELECT AVG(skor) as avg_tugas FROM nilai_tugas WHERE siswa_id = ?");
                                            $t->execute([$s['id']]);
                                            $avg_tugas = $t->fetch(PDO::FETCH_ASSOC)['avg_tugas'] ?? 0;

                                            // Query Ujian
                                            $u = $pdo->prepare("SELECT jenis, skor FROM nilai_ujian WHERE siswa_id = ?");
                                            $u->execute([$s['id']]);
                                            $ujian = $u->fetchAll(PDO::FETCH_KEY_PAIR);
                                            $uts = $ujian['UTS'] ?? 0;
                                            $uas = $ujian['UAS'] ?? 0;

                                            $nilai_rapot = ($avg_tugas * 0.30) + ($uts * 0.30) + ($uas * 0.40);
                                            list($grade, $badge_class) = hitungGrade($nilai_rapot);
                                            $initial = strtoupper(substr($s['nama'], 0, 1));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-circle"><?= $initial ?></div>
                                                    <div>
                                                        <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($s['nama']) ?></div>
                                                        <small class="text-muted">NISN: <?= htmlspecialchars($s['nisn']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><?= $hadir ?> H</span>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><?= $izin ?> I</span>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><?= $sakit ?> S</span>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><?= $alpa ?> A</span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($total_absen > 5): ?>
                                                    <span class="badge bg-danger text-white rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i><?= $total_absen ?>x (Terlampaui)</span>
                                                <?php else: ?>
                                                    <span class="fw-semibold text-secondary"><?= $total_absen ?>x</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-semibold"><?= number_format($avg_tugas, 1) ?></td>
                                            <td class="text-center fw-semibold"><?= number_format($uts, 1) ?></td>
                                            <td class="text-center fw-semibold"><?= number_format($uas, 1) ?></td>
                                            <td class="text-center fw-bold text-primary fs-6"><?= number_format($nilai_rapot, 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $badge_class ?> rounded-circle p-2 fw-bold" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <?= $grade ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- TAB 2: INPUT PRESENSI HARIAN -->
            <?php elseif ($tab === 'presensi'): ?>
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-calendar-event text-primary me-2"></i>Input Presensi Hari Ini</h5>
                    <?php if (empty($siswa_list)): ?>
                        <p class="text-muted">Belum ada siswa di kelas ini.</p>
                    <?php else: ?>
                        <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=presensi" method="POST">
                            <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Tanggal Pertemuan</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th class="text-center">Status Kehadiran Hari Ini</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswa_list as $s): ?>
                                            <tr>
                                                <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="h_<?= $s['id'] ?>" value="Hadir" checked>
                                                        <label class="btn btn-outline-success btn-sm px-3" for="h_<?= $s['id'] ?>">Hadir</label>

                                                        <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="i_<?= $s['id'] ?>" value="Izin">
                                                        <label class="btn btn-outline-warning btn-sm px-3" for="i_<?= $s['id'] ?>">Izin</label>

                                                        <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="s_<?= $s['id'] ?>" value="Sakit">
                                                        <label class="btn btn-outline-info btn-sm px-3" for="s_<?= $s['id'] ?>">Sakit</label>

                                                        <input type="radio" class="btn-check" name="status[<?= $s['id'] ?>]" id="a_<?= $s['id'] ?>" value="Alpa">
                                                        <label class="btn btn-outline-danger btn-sm px-3" for="a_<?= $s['id'] ?>">Alpa</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="simpan_presensi" class="btn btn-primary-gradient px-4 py-2 mt-3 fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Presensi Tanggal Ini
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            <!-- TAB 3: INPUT TUGAS HARIAN -->
            <?php elseif ($tab === 'tugas'): ?>
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-check text-primary me-2"></i>Input Nilai Tugas</h5>
                    <?php if (empty($siswa_list)): ?>
                        <p class="text-muted">Belum ada siswa di kelas ini.</p>
                    <?php else: ?>
                        <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=tugas" method="POST">
                            <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                            <div class="col-md-5 mb-4">
                                <label class="form-label fw-semibold">Nama / Judul Tugas</label>
                                <input type="text" name="nama_tugas" class="form-control" placeholder="Contoh: Tugas 1 - Algoritma" required>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th style="width: 220px;">Nilai Tugas (0-100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswa_list as $s): ?>
                                            <tr>
                                                <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                <td><input type="number" step="0.1" name="skor[<?= $s['id'] ?>]" class="form-control" value="0" min="0" max="100"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="simpan_tugas" class="btn btn-primary-gradient px-4 py-2 mt-3 fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Nilai Tugas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            <!-- TAB 4: INPUT UTS / UAS -->
            <?php elseif ($tab === 'ujian'): ?>
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text text-primary me-2"></i>Input Ujian (UTS / UAS)</h5>
                    <?php if (empty($siswa_list)): ?>
                        <p class="text-muted">Belum ada siswa di kelas ini.</p>
                    <?php else: ?>
                        <form action="coba.php?kelas_id=<?= $kelas_id ?>&tab=ujian" method="POST">
                            <input type="hidden" name="kelas_id" value="<?= $kelas_id ?>">
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Pilih Jenis Ujian</label>
                                <select name="jenis" class="form-select" required>
                                    <option value="UTS">UTS (Ujian Tengah Semester)</option>
                                    <option value="UAS">UAS (Ujian Akhir Semester)</option>
                                </select>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th style="width: 220px;">Nilai Ujian (0-100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswa_list as $s): ?>
                                            <tr>
                                                <td><span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span></td>
                                                <td><input type="number" step="0.1" name="skor[<?= $s['id'] ?>]" class="form-control" value="0" min="0" max="100"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="simpan_ujian" class="btn btn-primary-gradient px-4 py-2 mt-3 fw-bold">
                                <i class="bi bi-save me-1"></i> Simpan Nilai Ujian
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            <!-- TAB 5: KELOLA SISWA -->
            <?php elseif ($tab === 'siswa'): ?>
                <div class="card card-custom p-4">
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
                            <button type="submit" name="tambah_siswa" class="btn btn-success w-100 fw-bold py-2">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Siswa
                            </button>
                        </div>
                    </form>

                    <h6 class="fw-bold border-top pt-3 mb-3">Daftar Siswa Terdaftar di Kelas Ini</h6>
                    <?php if (empty($siswa_list)): ?>
                        <p class="text-muted">Belum ada siswa terdaftar di kelas ini.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush border rounded-3">
                            <?php foreach ($siswa_list as $s): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($s['nama']) ?></strong>
                                            <span class="text-muted ms-2 small">(NISN: <?= htmlspecialchars($s['nisn']) ?>)</span>
                                        </div>
                                    </div>
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