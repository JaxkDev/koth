-- #! sqlite

-- #{ arena

-- #  { init
CREATE TABLE IF NOT EXISTS arena(
    name TEXT NOT NULL,
    min_players INT UNSIGNED NOT NULL,
    max_players INT UNSIGNED NOT NULL,
    hill TEXT NOT NULL,
    spawns TEXT NOT NULL,
    rewards TEXT NOT NULL,
    world TEXT NOT NULL,
    PRIMARY KEY(name)
);
-- #  }

-- #  { create
-- #    :name string
-- #    :min int
-- #    :max int
-- #    :hill string
-- #    :spawns string
-- #    :rewards string
-- #    :world string
INSERT INTO arena (
    name,
    min_players,
    max_players,
    hill,
    spawns,
    rewards,
    world
) VALUES (
    :name,
    :min,
    :max,
    :hill,
    :spawns,
    :rewards,
    :world
);
-- #  }

-- #  { update
-- #    :name string
-- #    :min int
-- #    :max int
-- #    :hill string
-- #    :spawns string
-- #    :rewards string
-- #    :world string
UPDATE arena SET
    min_players = :min,
    max_players = :max,
    hill = :hill,
    spawns = :spawns,
    rewards = :rewards,
    world = :world
WHERE name = :name;
-- #  }

-- #  { delete
-- #    :name string
DELETE FROM arena WHERE name = :name;
-- #  }

-- #  { all
SELECT * FROM arena;
-- #  }

-- #}