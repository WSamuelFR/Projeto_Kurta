-- Script de estrutura robusto para TiDB / MySQL (Versão com Plural 'users')
CREATE DATABASE IF NOT EXISTS `kurta`;
USE `kurta`;

-- Desativa verificações para evitar erros de ordem de criação
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `users` (
    `user_id` INT NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(191) NOT NULL,
    `last_name` VARCHAR(191) NULL,
    `phone` BIGINT NULL,
    `email` VARCHAR(191) NOT NULL,
    `birthdate` DATETIME NULL,
    `profile_pic` VARCHAR(191) NULL DEFAULT 'assets/files/default_avatar.png',
    `wallpaper_pic` VARCHAR(191) NULL DEFAULT 'assets/files/default_wallpaper.png',
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE INDEX `users_email_key` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login` (
    `login_id` INT NOT NULL AUTO_INCREMENT,
    `user` INT NOT NULL,
    `password` VARCHAR(191) NOT NULL,
    `level_acess` VARCHAR(191) NULL DEFAULT 'user',
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`login_id`),
    INDEX `login_user_idx` (`user`),
    CONSTRAINT `login_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clan` (
    `clan_id` INT NOT NULL AUTO_INCREMENT,
    `name_clan` VARCHAR(191) NOT NULL,
    `description` TEXT NULL,
    `clan_pic` VARCHAR(191) NULL DEFAULT 'assets/files/default_clan.png',
    `visibility` VARCHAR(191) NULL DEFAULT 'public',
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`clan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clan_member` (
    `member_id` INT NOT NULL AUTO_INCREMENT,
    `clan_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` VARCHAR(191) NULL DEFAULT 'aldeao',
    `joined_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`member_id`),
    UNIQUE INDEX `clan_member_unique` (`clan_id`, `user_id`),
    INDEX `clan_member_user_idx` (`user_id`),
    CONSTRAINT `clan_member_user_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `clan_member_clan_fkey` FOREIGN KEY (`clan_id`) REFERENCES `clan` (`clan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feeling` (
    `feeling_id` INT NOT NULL AUTO_INCREMENT,
    `feeling` TEXT NOT NULL,
    `user` INT NOT NULL,
    `visibility` VARCHAR(191) NULL DEFAULT 'public',
    `cla_id` INT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`feeling_id`),
    INDEX `feeling_user_idx` (`user`),
    INDEX `feeling_clan_idx` (`cla_id`),
    CONSTRAINT `feeling_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `feeling_clan_fkey` FOREIGN KEY (`cla_id`) REFERENCES `clan` (`clan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coments` (
    `coment_id` INT NOT NULL AUTO_INCREMENT,
    `coment` TEXT NULL,
    `user` INT NOT NULL,
    `feeling` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    `parent_id` INT NULL,
    PRIMARY KEY (`coment_id`),
    INDEX `coments_user_idx` (`user`),
    INDEX `coments_feeling_idx` (`feeling`),
    INDEX `coments_parent_idx` (`parent_id`),
    CONSTRAINT `coments_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `coments_feeling_fkey` FOREIGN KEY (`feeling`) REFERENCES `feeling` (`feeling_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `coments_parent_fkey` FOREIGN KEY (`parent_id`) REFERENCES `coments` (`coment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `likes` (
    `like_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `feeling_id` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`like_id`),
    UNIQUE INDEX `likes_unique` (`user_id`, `feeling_id`),
    INDEX `likes_feeling_idx` (`feeling_id`),
    CONSTRAINT `likes_user_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `likes_feeling_fkey` FOREIGN KEY (`feeling_id`) REFERENCES `feeling` (`feeling_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification` (
    `notif_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `notif_type` VARCHAR(191) NOT NULL,
    `reference_id` INT NULL,
    `is_read` BOOLEAN NULL DEFAULT false,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`notif_id`),
    INDEX `notif_user_idx` (`user_id`),
    INDEX `notif_sender_idx` (`sender_id`),
    CONSTRAINT `notif_user_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `notif_sender_fkey` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `share` (
    `share_id` INT NOT NULL AUTO_INCREMENT,
    `share` TEXT NULL,
    `user` INT NOT NULL,
    `feeling` INT NOT NULL,
    `clan` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`share_id`),
    INDEX `share_user_idx` (`user`),
    INDEX `share_feeling_idx` (`feeling`),
    INDEX `share_clan_idx` (`clan`),
    CONSTRAINT `share_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `share_feeling_fkey` FOREIGN KEY (`feeling`) REFERENCES `feeling` (`feeling_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `share_clan_fkey` FOREIGN KEY (`clan`) REFERENCES `clan` (`clan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `friendship` (
    `friendship_id` INT NOT NULL AUTO_INCREMENT,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `status` VARCHAR(191) NULL DEFAULT 'pending',
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`friendship_id`),
    UNIQUE INDEX `friendship_unique` (`sender_id`, `receiver_id`),
    INDEX `friendship_receiver_idx` (`receiver_id`),
    CONSTRAINT `friendship_sender_fkey` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `friendship_receiver_fkey` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clan_feeling` (
    `feeling_id` INT NOT NULL AUTO_INCREMENT,
    `feeling` TEXT NOT NULL,
    `user` INT NOT NULL,
    `visibility` VARCHAR(191) NULL DEFAULT 'public',
    `cla_id` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`feeling_id`),
    INDEX `clan_feeling_user_idx` (`user`),
    INDEX `clan_feeling_clan_idx` (`cla_id`),
    CONSTRAINT `clan_feeling_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `clan_feeling_clan_fkey` FOREIGN KEY (`cla_id`) REFERENCES `clan` (`clan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clan_likes` (
    `like_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `feeling_id` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`like_id`),
    UNIQUE INDEX `clan_likes_unique` (`user_id`, `feeling_id`),
    INDEX `clan_likes_feeling_idx` (`feeling_id`),
    CONSTRAINT `clan_likes_user_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `clan_likes_feeling_fkey` FOREIGN KEY (`feeling_id`) REFERENCES `clan_feeling` (`feeling_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clan_coments` (
    `coment_id` INT NOT NULL AUTO_INCREMENT,
    `coment` TEXT NULL,
    `user` INT NOT NULL,
    `feeling` INT NOT NULL,
    `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    `parent_id` INT NULL,
    PRIMARY KEY (`coment_id`),
    INDEX `clan_coments_user_idx` (`user`),
    INDEX `clan_coments_feeling_idx` (`feeling`),
    INDEX `clan_coments_parent_idx` (`parent_id`),
    CONSTRAINT `clan_coments_user_fkey` FOREIGN KEY (`user`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `clan_coments_feeling_fkey` FOREIGN KEY (`feeling`) REFERENCES `clan_feeling` (`feeling_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `clan_coments_parent_fkey` FOREIGN KEY (`parent_id`) REFERENCES `clan_coments` (`coment_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reativa verificações
SET FOREIGN_KEY_CHECKS = 1;
