-- Migration 001: Create Initial Schema for SQLite

CREATE TABLE user (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    login_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user INTEGER NOT NULL,
    password VARCHAR(150) NOT NULL,
    level_acess TEXT DEFAULT 'user', -- SQLite does not have ENUM, using TEXT
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user) REFERENCES user(user_id)
);

CREATE TABLE clan (
    clan_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name_clan VARCHAR(200) NOT NULL,
    description TEXT,
    clan_pic VARCHAR(255) DEFAULT 'assets/files/default_clan.png',
    visibility TEXT DEFAULT 'public', -- SQLite does not have ENUM
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE feeling (
    feeling_id INTEGER PRIMARY KEY AUTOINCREMENT,
    feeling TEXT NOT NULL,
    user INTEGER NOT NULL,
    visibility TEXT DEFAULT 'public', -- SQLite does not have ENUM
    cla_id INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user) REFERENCES user(user_id),
    FOREIGN KEY (cla_id) REFERENCES clan(clan_id) ON DELETE CASCADE
);

CREATE TABLE coments (
    coment_id INTEGER PRIMARY KEY AUTOINCREMENT,
    coment TEXT,
    user INTEGER NOT NULL,
    feeling INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user) REFERENCES user(user_id),
    FOREIGN KEY (feeling) REFERENCES feeling(feeling_id) ON DELETE CASCADE
);

CREATE TABLE clan_member (
    member_id INTEGER PRIMARY KEY AUTOINCREMENT,
    clan_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role TEXT DEFAULT 'aldeao', -- SQLite does not have ENUM
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clan_id) REFERENCES clan(clan_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    UNIQUE (clan_id, user_id)
);

CREATE TABLE share (
    share_id INTEGER PRIMARY KEY AUTOINCREMENT,
    share TEXT,
    user INTEGER NOT NULL,
    feeling INTEGER NOT NULL,
    clan INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user) REFERENCES user(user_id),
    FOREIGN KEY (feeling) REFERENCES feeling(feeling_id) ON DELETE CASCADE,
    FOREIGN KEY (clan) REFERENCES clan(clan_id) ON DELETE CASCADE
);

CREATE TABLE friendship (
    friendship_id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    status TEXT DEFAULT 'pending', -- SQLite does not have ENUM
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES user(user_id) ON DELETE CASCADE,
    UNIQUE (sender_id, receiver_id)
);

CREATE TABLE likes (
    like_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    feeling_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (feeling_id) REFERENCES feeling(feeling_id) ON DELETE CASCADE,
    UNIQUE (user_id, feeling_id)
);

CREATE TABLE notification (
    notif_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    notif_type VARCHAR(50) NOT NULL,
    reference_id INTEGER,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES user(user_id) ON DELETE CASCADE
);
