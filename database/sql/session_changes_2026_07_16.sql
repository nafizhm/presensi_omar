-- Perubahan database sesi pengembangan presensi_mobile
-- Target: MySQL 8 / MariaDB, dijalankan SATU KALI pada database yang belum menerima migration terkait.
-- Jangan jalankan file ini pada database lokal yang sudah menjalankan `php artisan migrate`.

-- 1. Master departemen.
CREATE TABLE `departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Relasi departemen pada karyawan.
ALTER TABLE `users`
    ADD COLUMN `department_id` BIGINT UNSIGNED NULL AFTER `shift_id`,
    ADD INDEX `users_department_id_foreign` (`department_id`),
    ADD CONSTRAINT `users_department_id_foreign`
        FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
        ON DELETE SET NULL;

-- 3. Izin bagi karyawan untuk menambahkan titik lokasi dari profil.
ALTER TABLE `users`
    ADD COLUMN `can_manage_location_points` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

-- 4. Data awal wajib untuk menampung karyawan lama yang belum memiliki departemen.
INSERT INTO `departments` (`name`, `description`, `created_at`, `updated_at`)
VALUES ('Umum', 'Departemen default untuk data karyawan lama', NOW(), NOW());

SET @default_department_id = LAST_INSERT_ID();

UPDATE `users`
SET `department_id` = @default_department_id
WHERE `role` = 'karyawan'
  AND `department_id` IS NULL;

-- 5. Opsional: contoh master departemen lain. Hapus tanda komentar bila diperlukan.
-- INSERT INTO `departments` (`name`, `description`, `created_at`, `updated_at`) VALUES
-- ('Human Resources', 'Pengelolaan sumber daya manusia', NOW(), NOW()),
-- ('Keuangan', 'Pengelolaan administrasi dan keuangan', NOW(), NOW()),
-- ('Teknologi Informasi', 'Pengembangan dan dukungan sistem', NOW(), NOW()),
-- ('Operasional', 'Pelaksanaan kegiatan operasional', NOW(), NOW());

-- 6. Contoh pemberian izin titik lokasi kepada karyawan tertentu.
-- Ganti KRY001 dengan kode karyawan yang sesuai.
-- UPDATE `users`
-- SET `can_manage_location_points` = 1
-- WHERE `employee_code` = 'KRY001'
--   AND `role` = 'karyawan';

-- 7. Hanya jika perubahan diterapkan lewat SQL ini (bukan `php artisan migrate`),
-- catat migration agar Laravel tidak mencoba menjalankannya kembali.
SET @migration_batch = (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_16_090000_create_departments_table_and_add_department_id_to_users_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_07_16_090000_create_departments_table_and_add_department_id_to_users_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_16_100000_add_can_manage_location_points_to_users_table', @migration_batch
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_07_16_100000_add_can_manage_location_points_to_users_table'
);

-- Query pemeriksaan setelah eksekusi.
SELECT `id`, `name`, `description` FROM `departments` ORDER BY `name`;

SELECT
    `id`, `employee_code`, `name`, `department_id`, `can_manage_location_points`
FROM `users`
WHERE `role` = 'karyawan'
ORDER BY `name`;
