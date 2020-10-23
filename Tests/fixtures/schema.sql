CREATE TABLE test (
	id serial PRIMARY KEY,
	name character varying(50) NOT NULL,
	type character(1) UNIQUE,
	flag BOOLEAN
);
