CREATE DATABASE kurta;

use kurta;

CREATE TABLE user (
 user_id INT AUTO_INCREMENT PRIMARY KEY,
 first_name VARCHAR(50) NOT NULL,
 last_name VARCHAR(50),
 phone BIGINT,
 email VARCHAR(150) NOT NULL,
 birthdate DATE,
 profile_pic VARCHAR(255) DEFAULT 'assets/files/default_avatar.png',
 wallpaper_pic VARCHAR(255) DEFAULT 'assets/files/default_wallpaper.png',
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login (
login_id INT AUTO_INCREMENT PRIMARY KEY,
user INT NOT NULL,
password VARCHAR(150) NOT NULL,
level_acess ENUM('admin', 'user') DEFAULT 'user',
FOREIGN KEY (user) REFERENCES user(user_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE feeling (
feeling_id INT AUTO_INCREMENT PRIMARY KEY,
feeling TEXT NOT NULL,
user INT NOT NULL,
visibility ENUM('public', 'friends', 'private') DEFAULT 'public',
cla_id INT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- UPDATE: ALTER TABLE adicionado via Engine para evitar conflitos circulares caso o BD rode limpo.
-- ALTER TABLE feeling ADD FOREIGN KEY (cla_id) REFERENCES clan(clan_id);

CREATE TABLE coments (
coment_id INT AUTO_INCREMENT PRIMARY KEY,
coment TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id) ON DELETE CASCADE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clan (
clan_id INT AUTO_INCREMENT PRIMARY KEY,
name_clan VARCHAR(200) NOT NULL,
description TEXT,
clan_pic VARCHAR(255) DEFAULT 'assets/files/default_clan.png',
visibility ENUM('public', 'private') DEFAULT 'public',
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clan_member (
member_id INT AUTO_INCREMENT PRIMARY KEY,
clan_id INT NOT NULL,
user_id INT NOT NULL,
role ENUM('rei', 'lider', 'aldeao') DEFAULT 'aldeao',
joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (clan_id) REFERENCES clan(clan_id) ON DELETE CASCADE,
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
UNIQUE KEY unique_member (clan_id, user_id)
);

CREATE TABLE share (
share_id INT AUTO_INCREMENT PRIMARY KEY,
share TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
clan INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id) ON DELETE CASCADE,
FOREIGN KEY (clan) REFERENCES clan(clan_id) ON DELETE CASCADE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE friendship (
friendship_id INT AUTO_INCREMENT PRIMARY KEY,
sender_id INT NOT NULL,
receiver_id INT NOT NULL,
status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (sender_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (receiver_id) REFERENCES user(user_id) ON DELETE CASCADE,
UNIQUE KEY unique_friendship (sender_id, receiver_id)
);

CREATE TABLE likes (
like_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
feeling_id INT NOT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (feeling_id) REFERENCES feeling(feeling_id) ON DELETE CASCADE,
UNIQUE KEY unique_like (user_id, feeling_id)
);

CREATE TABLE notification (
notif_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
sender_id INT NOT NULL,
notif_type VARCHAR(50) NOT NULL,
reference_id INT,
is_read BOOLEAN DEFAULT FALSE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (sender_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- UPDATE: Injetado via Engine (Prevenção de Referência Circular)
ALTER TABLE feeling ADD FOREIGN KEY (cla_id) REFERENCES clan(clan_id) ON DELETE CASCADE;