CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;

CREATE TABLE test (
    id serial PRIMARY KEY,
    name character varying(50) NOT NULL,
    type character(1) UNIQUE,
	flag BOOLEAN
);

CREATE FUNCTION exception_procedure(message TEXT) RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
	RAISE EXCEPTION '%', message;
END;
$$;

