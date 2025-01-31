DROP TABLE IF EXISTS clients;
CREATE TABLE clients (
    client_id serial,
    country varchar NOT NULL,
    city varchar NOT NULL,
    isActive boolean NOT NULL,
    gender varchar NOT NULL,
    birthDate date NOT NULL,
    salary int4 NOT NULL,
    hasChildren boolean NOT NULL,
    familyStatus varchar NOT NULL,
    registrationDate date NOT NULL
);