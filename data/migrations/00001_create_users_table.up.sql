CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  email TEXT UNIQUE NOT NULL,
  password TEXT, -- in case of OAuth, this field will be NULL
  created_at INTEGER NOT NULL DEFAULT (unixepoch()),
  last_updated INTEGER NOT NULL DEFAULT (unixepoch())
);
