
--  ユーザとかがここ～ --
CREATE TABLE IF NOT EXISTS users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  email VARCHAR(191) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  icon_filename VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--  投稿のとこらへん --
CREATE TABLE IF NOT EXISTS bbs_entries (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  body TEXT NOT NULL,
  image_filename VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id)
);

--  写真系とか --
CREATE TABLE IF NOT EXISTS bbs_entry_images (
  id         BIGINT AUTO_INCREMENT PRIMARY KEY,
  entry_id   BIGINT NOT NULL,
  filename   VARCHAR(255) NOT NULL,
  sort_no    TINYINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_entry_id (entry_id),
  CONSTRAINT fk_bbs_entry_images_entry
    FOREIGN KEY (entry_id) REFERENCES bbs_entries(id) ON DELETE CASCADE
);


--  フォロ系--
CREATE TABLE IF NOT EXISTS user_relationships (
  follower_user_id BIGINT NOT NULL,
  followee_user_id BIGINT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (follower_user_id, followee_user_id),
  INDEX idx_ur_follower (follower_user_id),
  INDEX idx_ur_followee (followee_user_id),
  CONSTRAINT fk_ur_follower FOREIGN KEY (follower_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_followee FOREIGN KEY (followee_user_id) REFERENCES users(id) ON DELETE CASCADE
);

