PRAGMA journal_mode = WAL;

PRAGMA foreign_keys=OFF;

BEGIN TRANSACTION;

CREATE TABLE "planets" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	"title" TEXT NOT NULL COLLATE NOCASE CHECK (LENGTH("title") <= 64),
	"rings" INTEGER NOT NULL CHECK ("rings" >= 0 AND "rings" <= 1) DEFAULT 0,
	"axial_tilt_deg" REAL DEFAULT NULL,
	"symbol" TEXT DEFAULT NULL,
	"discovery_year" INT DEFAULT NULL,
	CONSTRAINT "planets_title_uq" UNIQUE ("title")
);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Mercury',0,0.0,'☿',NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Venus',0,177.30,'♀',NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Earth',0,23.44,'',NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Mars',0,NULL,'♂',NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Jupiter',1,3.12,NULL,NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Saturn',1,26.73,NULL,NULL);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Uranus',1,97.86,NULL,1781);
INSERT INTO planets (title, rings, axial_tilt_deg, symbol, discovery_year) VALUES ('Neptune',1,28.32,'♆',1846);

CREATE TABLE "galaxies" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	"title" TEXT NOT NULL COLLATE NOCASE CHECK (LENGTH("title") <= 64),
	CONSTRAINT "galaxies_title_uq" UNIQUE ("title")
);

CREATE TABLE "stuff" (
	"id" INTEGER PRIMARY KEY NOT NULL,
	"label" TEXT NOT NULL COLLATE NOCASE CHECK (LENGTH("label") <= 64),
	CONSTRAINT "stuff_label_uq" UNIQUE ("label")
);

COMMIT;
