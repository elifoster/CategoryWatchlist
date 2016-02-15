CREATE TABLE IF NOT EXISTS watched_categories (
  `category` varchar(255) /*As defined in category table*/ NOT NULL,
  `users` text NOT NULL,
  PRIMARY KEY (`category`)
);