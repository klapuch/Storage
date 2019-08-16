CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;

CREATE EXTENSION IF NOT EXISTS hstore;

CREATE TABLE test (
	id serial PRIMARY KEY,
	name character varying(50) NOT NULL,
	type character(1) UNIQUE,
	flag boolean
);

CREATE TABLE test_table2 (first integer);
CREATE TYPE test_type2 AS (second integer);

CREATE TABLE test_full (
	one integer,
	two smallint,
	three bigint,
	four BOOLEAN,
	five NUMERIC,
	six text,
	seven VARCHAR(10),
	eight character VARYING,
	nine char
);

CREATE TYPE test_type1 AS (second integer);
CREATE TYPE test_type3 AS (first test_type1);

CREATE TYPE test_type4 AS (second integer);
CREATE TABLE test_table4 (first test_type4);

CREATE TABLE person_table (name TEXT, race TEXT);

CREATE TYPE length AS (value INTEGER, unit TEXT);

CREATE TYPE person_type AS (name TEXT, race TEXT);

CREATE TABLE person_table2 (length length, name TEXT);

CREATE TABLE simple_table (id integer, name text);
CREATE TABLE coordinates_table (id integer, coordinates point);
CREATE TABLE scalars (name text, age smallint, good boolean, bad boolean, id integer);
CREATE TABLE pg_types (list hstore, age int4range, gps point);

CREATE TYPE person_type3 AS (name TEXT, age INTEGER, cool BOOLEAN);

CREATE FUNCTION exception_procedure(message TEXT) RETURNS VOID
LANGUAGE plpgsql
AS $$
BEGIN
	RAISE EXCEPTION '%', message;
END;
$$;


