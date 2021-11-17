# \#2. PostgreSQL

## Local setup

1. Clone the following repository: `https://git.fe.up.pt/lbaw/template-postgresql.git`
2. Go into the new directory `template-postgresql`
3. Run `docker-compose up` (takes 2~3 min; it will be faster from now on)
4. Open pgAdmin by entering the URL `localhost:4321` into your browser
5. Enter username `postgres@lbaw.com` and password `pg!password` into pgAdmin, and log in
6. Right-click "Servers" on the left menu, and then Create > Server
7. Enter options:
    - General > Name: lbaw-local (this can be whatever you want, this name is local to your pgAdmin instance)
    - Connection > Host name/address: postgres
    - Connection > Port: 5432
    - Connection > Maintenance database: postgres
    - Connection > Username: postgres
    - Connection > Password: pg!password
8. Press "Save", and now click the new entry "lbaw-local"

### Some experiments

1. Go to Servers > lbaw-local > Databases > postgres > Schemas > public
2. Right-click "public" and choose "Query tool"
3. Now you have an SQL interface with your database; you can do whatever you'd normally do on a command line: add/remove tables/rows, query, etc.
4. Try to create a table by running in the query tool the following code (after editing the query, to run it press the "Execute" button on the top bar, or press F5):

```sql
CREATE TABLE "user" (
    id INT,
    username VARCHAR,
    password VARCHAR,
    name VARCHAR
);
```

5. Under schema "public", open "Tables", right-click "user" and choose View/Edit Data > All Rows
6. You will see all the rows of table "user" (which is currently empty)
7. Run a few queries to add some data, and repeat step 5 to see the new table rows

## Access production server

1. Turn on FEUP VPN
2. Navigate to pgAdmin and log in.
3. Right-click "Servers" on the left menu, and then Create > Server
4. Enter options:
    - General > Name: lbaw-remote
    - Connection > Host name/address: db.fe.up.pt
    - Connection > Port: 5432
    - Connection > Maintenance database: postgres
    - Connection > Username: up201906000 (use your student number)
    - Connection > Password: (use your DB password; this is NOT your Sigarra password)
4. Press "Save", and now click the new entry "lbaw-remote"
5. Go to Servers > lbaw-remote > Databases
6. You will see an enormous list of databases, most of which you can't double-click; find the one with your group ID `lbaw21gg`; this is your deployment database

## Schemas

Schemas are a PostgreSQL feature that did not exist in SQLite (SQLite also has something called schemas, but this is different).

A PostgreSQL schema is a namespace of tables. It is a sort of a "sub-database" inside a database, except tables in different schemas of the same database can refer to each other, while tables from different databases cannot refer to each other.

Simply put, a schema is just a namespace; if you have a schema called `myschema` you could pretty much just add `myschema_` before all your tables' names, and you'd achieve a similar result as using schemas.

A table named `mytable` in namespace `myschema` can be referred to in queries using `myschema.mytable`.

If you do not provide a schema name (e.g., if you refer only to `mytable`), PostgreSQL will assume you're talking about a table `mytable` in the default schema `public` (which exists in every PostgreSQL database). In this case, using `mytable` or `public.mytable` is exactly the same.

### Why we care about schemas

Because if you go into your deployment database, open schema `public` and open the list of tables, you'll see that there are already a large number of tables in your repository (154 tables to be precise).

These tables are not yours and were not supposed to be there. We are yet to discover what these tables are; in the meantime you're not supposed to touch them. In past years a different database server was used, so this is the first year using a shared, FEUP-provided server.

### How to get around this issue

To get around this issue, you should work on a different schema.

In your remote database, create a new schema named after your group ID `lbaw21gg`.

Because you're the single owner of the schema, none of your teammates will be able to use that schema. To allow that, you need to give `CREATE` permissions to your colleagues, by running the following query for each username of your colleagues:

```sql
GRANT CREATE ON SCHEMA lbaw21gg TO up201906000;
```

To make the whole process faster, use the following schema creation/authorization template:

```sql
CREATE SCHEMA IF NOT EXISTS lbaw21gg;
GRANT CREATE ON SCHEMA lbaw21gg TO up201906000;
GRANT CREATE ON SCHEMA lbaw21gg TO up201906001;
GRANT CREATE ON SCHEMA lbaw21gg TO up201906002;
...
```

Replace the UP numbers with yours. Note that whoever creates the schema formally does not require to run the `GRANT` command, but to make the same script usable by all group members you should use the `GRANT` instruction for all users (including whoever's the owner), as any member should be able to create a schema that all other members can use.

Now you can create your tables by appending them with `lbaw21gg.`

If you don't want to always type the schema name, you can run the following instruction to change the default schema for your database (so that when you do not specify a schema for your table, PostgreSQL will assume you're talking of a table in schema `lbaw21gg` instead of `public`):

```sql
SET search_path TO lbaw21gg;
```
