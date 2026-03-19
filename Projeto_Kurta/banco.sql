CREATE DATABASE kurta;

use kurta;

CREATE TABLE user (
 user_id INT AUTO_INCREMENT PRIMARY KEY,
 first_name VARCHAR(50) NOT NULL,
 last_name VARCHAR(50),
 phone BIGINT,
 email VARCHAR(150) NOT NULL,
 birthdate DATE,
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
FOREIGN KEY (user) REFERENCES user(user_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coments (
coment_id INT AUTO_INCREMENT PRIMARY KEY,
coment TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clan (
clan_id INT AUTO_INCREMENT PRIMARY KEY,
name_clan VARCHAR(200) NOT NULL,
description TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE share (
share_id INT AUTO_INCREMENT PRIMARY KEY,
share TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
clan INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
FOREIGN KEY (clan) REFERENCES clan(clan_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

DESCRIBE login;

INSERT user (first_name, last_name, phone, email, birthdate) VALUES ('wesley', 'rodrigues', '83996312020', 'wesley@gmail.com', '2001/02/07');

select * from login JOIN user WHERE l = login AND u = user;

ALTER TABLE user MODIFY COLUMN phone BIGINT;

INSERT login (user, password, level_acess) VALUES ('1', 'wsfr9026', 'user');

SELECT user.email, login.password
FROM user, login
WHERE user.user_id = login.login_id;

