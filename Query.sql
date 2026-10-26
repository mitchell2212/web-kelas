SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS nilai_ujian;
DROP TABLE IF EXISTS nilai_tugas;
DROP TABLE IF EXISTS presensi_harian;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS kelas;

SET FOREIGN_KEY_CHECKS = 1;

-- Tabel Kelas
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL UNIQUE
);

-- Tabel Siswa (Terhubung ke Kelas)
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas_id INT NOT NULL,
    nisn VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,db_kelas
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

-- Tabel Presensi Harian
CREATE TABLE presensi_harian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') DEFAULT 'Hadir',
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Tabel Nilai Tugas
CREATE TABLE nilai_tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    nama_tugas VARCHAR(100) NOT NULL,
    skor FLOAT DEFAULT 0,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Tabel Nilai Ujian
CREATE TABLE nilai_ujian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    jenis ENUM('UTS', 'UAS') NOT NULL,
    skor FLOAT DEFAULT 0,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

-- Contoh pembaruan data Presensi
UPDATE presensi SET status = '$status_baru' WHERE id_pertemuan = '$id_pertemuan' AND id_siswa = '$id_siswa';

-- Contoh pembaruan data Tugas
UPDATE nilai_tugas SET skor = '$skor_baru' WHERE id_tugas = '$id_tugas' AND id_siswa = '$id_siswa';

-- Contoh pembaruan data Ujian
UPDATE nilai_ujian SET nilai_uts = '$nilai_baru' WHERE id_ujian = '$id_ujian' AND id_siswa = '$id_siswa';