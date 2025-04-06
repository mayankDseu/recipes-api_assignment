CREATE TABLE IF NOT EXISTS recipes (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    prep_time INT NOT NULL,
    difficulty INT CHECK (difficulty BETWEEN 1 AND 3),
    vegetarian BOOLEAN NOT NULL,
    ratings_count INT DEFAULT 0,
    ratings_sum INT DEFAULT 0
);
