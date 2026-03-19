CREATE TABLE user (
 user_id INT AUTO_INCREMENT NOT NULL,
 first_name VARCHAR(50) NOT NULL,
 last_name VARCHAR(50),
 phone INT,
 email VARCHAR(150) NOT NULL,
 birthdate DATE NOT NULL,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login (
login_id INT AUTO_INCREMENT NOT NULL,
user INT NOT NULL,
password VARCHAR(150) NOT NULL,
level_acess ENUM('admin', 'user') DEFAULT 'user',
FOREIGN KEY (user) REFERENCES user(user_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE feeling (
feeling_id INT AUTO_INCREMENT NOT NULL,
feeling TEXT NOT NULL,
user INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coments (
coment_id INT AUTO_INCREMENT NOT NULL,
coment TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clan (
clan_id INT AUTO_INCREMENT NOT NULL,
name_clan VARCHAR(200) NOT NULL,
description TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE share (
share_id INT AUTO_INCREMENT NOT NULL,
share TEXT,
user INT NOT NULL,
feeling INT NOT NULL,
clan INT NOT NULL,
FOREIGN KEY (user) REFERENCES user(user_id),
FOREIGN KEY (feeling) REFERENCES feeling(feeling_id),
FOREIGN KEY (clan) REFERENCES clan(clan_id),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
