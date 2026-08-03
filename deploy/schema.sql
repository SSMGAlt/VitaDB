-- VitaDB schema — reconstructed, not original.
--
-- The upstream repo doesn't ship a .sql file, so this was built by tracing
-- every mysqli_prepare()/mysqli_query() call in the PHP code (see the
-- "Schema note" section of the deploy guide for the full reasoning). It
-- covers every table and column the app actually reads or writes. If you
-- ever get a real dump from an existing VitaDB install, use that instead —
-- this is a fresh-install fallback, not a restoration of anyone's data.
--
-- Column types/lengths are reasonable inferences (e.g. `roles` is a
-- semicolon-delimited string like "1;2;3" - role numbers seen in the code:
-- 1/2/3 = staff/admin-ish, 5 = default on registration, 6 = supporter).
--
-- Usage:
--   docker exec -i vitadb-db mysql -u vitadb -p vitadb < deploy/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Homebrews, plugins, and tools all live in one table, distinguished by
-- `type` (8 = plugin, 9 = tool, <8 = homebrew categories).
CREATE TABLE IF NOT EXISTS `vitadb` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)  NOT NULL,
  `icon`              VARCHAR(500)  NOT NULL DEFAULT '',
  `version`           VARCHAR(64)   NOT NULL DEFAULT '',
  `author`            VARCHAR(255)  NOT NULL DEFAULT '',
  `url`               TEXT          NOT NULL,
  `type`              TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `description`       TEXT,
  `data`              TEXT,
  `date`              VARCHAR(32)   NOT NULL DEFAULT '',
  `titleid`           VARCHAR(32)   NOT NULL DEFAULT '',
  `long_description`  MEDIUMTEXT,
  `screenshots`       TEXT,
  `source`            TEXT,
  `release_page`      TEXT,
  `trailer`           TEXT,
  `size`              BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `data_size`         BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `downloads`         BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_vitadb_type` (`type`),
  KEY `idx_vitadb_titleid` (`titleid`),
  KEY `idx_vitadb_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vitadb_users` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`            VARCHAR(255) NOT NULL,
  `password`         CHAR(64)     NOT NULL, -- sha256 hex digest, unsalted (app-level, not changed here)
  `roles`            VARCHAR(64)  NOT NULL DEFAULT '5',
  `avatar`           VARCHAR(255) NOT NULL DEFAULT '',
  `twitter`          VARCHAR(255) NOT NULL DEFAULT '',
  `website`          VARCHAR(255) NOT NULL DEFAULT '',
  `github`           VARCHAR(255) NOT NULL DEFAULT '',
  `name`             VARCHAR(255) NOT NULL,
  `hidden_mail`      TINYINT(1)   NOT NULL DEFAULT 0,
  `supporter_date`   VARCHAR(64)  NOT NULL DEFAULT '',
  `paypal`           VARCHAR(255) NOT NULL DEFAULT '',
  `bitcoin`          VARCHAR(255) NOT NULL DEFAULT '',
  `patreon`          VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vitadb_users_email` (`email`),
  UNIQUE KEY `uq_vitadb_users_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per client IP; app does a manual check-then-insert/update.
CREATE TABLE IF NOT EXISTS `vitadb_csrf` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`     VARCHAR(45) NOT NULL, -- string form (supports IPv6), not ip2long'd
  `token`  CHAR(64)    NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vitadb_csrf_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit trail written on every add/update to `vitadb`.
CREATE TABLE IF NOT EXISTS `vitadb_log` (
  `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `author`  VARCHAR(255) NOT NULL,
  `object`  VARCHAR(64)  NOT NULL, -- e.g. "added" / "updated"
  `hb`      VARCHAR(255) NOT NULL, -- name of the affected entry
  `date`    DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vitadb_log_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-IP daily download rate limiting. `ip` is ip2long()'d (IPv4 only, as
-- written) - unlike vitadb_csrf.ip, which is stored as a string.
CREATE TABLE IF NOT EXISTS `vitadb_ips` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip`         INT UNSIGNED NOT NULL,
  `timestamp`  INT UNSIGNED NOT NULL DEFAULT 0,
  `counter`    INT UNSIGNED NOT NULL DEFAULT 0,
  `total`      INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vitadb_ips_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
