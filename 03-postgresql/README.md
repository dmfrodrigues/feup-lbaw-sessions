# \#3. PostgreSQL advanced concepts <!-- omit in toc -->

## Table of contents <!-- omit in toc -->

- [Indexes](#indexes)
  - [Anatomy of an index](#anatomy-of-an-index)
  - [Example](#example)
  - [Full-text search indexes](#full-text-search-indexes)
    - [tsvector](#tsvector)
  - [Dictionaries](#dictionaries)
  - [Weight](#weight)
- [Triggers](#triggers)
  - [Anatomy of a trigger](#anatomy-of-a-trigger)
    - [BEFORE, AFTER, INSTEAD OF](#before-after-instead-of)
    - [INSERT, UPDATE OF ..., DELETE](#insert-update-of--delete)
    - [FOR EACH](#for-each)
    - [WHEN](#when)
    - [The function](#the-function)
  - [Example](#example-1)
- [Transactions](#transactions)
  - [Anatomy of a transaction](#anatomy-of-a-transaction)
  - [Example](#example-2)
- [References](#references)


## Indexes

An index is a lookup table (or similar structure) that allows you to speed up some queries, at the cost of:
- Extra memory to store the index
- A little extra time when changing the table, so the index can be updated as well

### Anatomy of an index

```sql
CREATE INDEX [ name ]
ON table
[ USING { btree | hash | gist | gin } ]
( expression )
```

The index name is optional, but you should specify one.

The `USING` expression specifies the type of index you want to use:

- `btree`: Binary tree. It is the default index type. Useful for exact matches, or elements that have a field greater or less than a certain value.
- `hash`: Hash table. Useful for exact matches.
- `gist` and `gin`: Used for full text searches.

If you only want exact matches, you should use `hash` because it is faster by a factor of $O(\log N)$.

`expression` can be any expression you find useful to index, depending on the most frequent queries you have. It is however most often made of only the name of a single column.

### Example

```sql
CREATE INDEX user_work ON work USING btree (id_users);
CREATE INDEX user_loan ON loan USING hash (id_users);
CREATE INDEX start_loan ON loan USING btree (start_t);
CREATE INDEX end_loan ON loan USING btree (end_t);
```

### Full-text search indexes

FTS indexes are a bit more involved, because for each column you wish to index for FTS you need to create a new column with auxiliary values for SQL algorithms to use.

```sql
ALTER TABLE post ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION doc_search_update() RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    NEW.tsvectors = (
      setweight(to_tsvector('english', NEW.title), 'A') ||
      setweight(to_tsvector('english', NEW.text), 'B')
    );
  END IF;
  IF TG_OP = 'UPDATE' THEN
      IF (NEW.title <> OLD.title OR NEW.text <> OLD.text) THEN
        NEW.tsvectors = (
          setweight(to_tsvector('english', NEW.title), 'A') ||
          setweight(to_tsvector('english', NEW.text), 'B')
        );
      END IF;
  END IF;
  RETURN NEW;
END $$
LANGUAGE plpgsql;

CREATE TRIGGER doc_search_update
  BEFORE INSERT OR UPDATE ON post
  FOR EACH ROW
  EXECUTE PROCEDURE doc_search_update();

CREATE INDEX search_idx ON post USING GIN (tsvectors);
```

#### tsvector

`tsvector` is a special type which is a dictionary of word positions used as auxiliary data by the `GIN` index type to speed up search (yes, it's **not an atomic field**, but it's just a device to speed up search). You can try it out by simply running

```sql
SELECT to_tsvector('english', 'Never gonna give you up. Never gonna let you down');
-- 'give':3 'gonna':2,7 'let':8 'never':1,6
```

Thus, word `give` appears in position 3, `gonna` in positions 2 and 7, and so on.

### Dictionaries

Some words like `you` are excluded because the dictionary we're using to build the `tsvector` (which is `english`) ignores simple connectors; to include all words you can use `simple`:

```sql
SELECT to_tsvector('simple', 'Never gonna give you up. Never gonna let you down');
-- 'down':10 'give':3 'gonna':2,7 'let':8 'never':1,6 'up':5 'you':4,9
```

### Weight

You can create a full-text search index where you use several fields with different weights. For instance, because a post title is shorter and more important its words should have more weight than if the same word appears in the post text.

PostgreSQL has four weight classes: A, B, C, D, with default weights {1.0, 0.4, 0.2, 0.1} respectively. You can see its functioning by running the following query:

```sql
SELECT setweight(to_tsvector('english', 'Never gonna give you up. Never gonna let you down'), 'A');
--- 'give':3A 'gonna':2A,7A 'let':8A 'never':1A,6A
```

To use several columns with different weights you can use the `||` operator:

```sql
SELECT (
	setweight(to_tsvector('english', 'Never gonna give you up. Never gonna let you down'), 'A') ||
	setweight(to_tsvector('english', 'Never gonna run around and desert you'), 'B')
);
--- 'around':12B 'desert':14B 'give':3A 'gonna':2A,7A,10B 'let':8A 'never':1A,6A,9B 'run':11B
```

## Triggers

A trigger is a special set of SQL instructions to be performed when a specified event is triggered.

### Anatomy of a trigger

```sql
CREATE FUNCTION function_name( arguments ) RETURNS TRIGGER AS
$BODY$
BEGIN
    ...
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER name
{ BEFORE | AFTER | INSTEAD OF } { INSERT | UPDATE OF ... | DELETE }
ON table
[ FOR EACH { ROW | STATEMENT } ]
[ WHEN (condition) ]
EXECUTE PROCEDURE function_name ( arguments )
```

In the first statement, we define a function which is the actual content of our trigger. In the second statement we specify the conditions on which our trigger will run.

Let's take it one step at a time.

#### BEFORE, AFTER, INSTEAD OF

This specifies if the triggered is to be executed before, after or instead of a certain table/view operation.

| Expression | Meaning | Example |
|------------|---------|---------|
| `BEFORE`   | Runs before the actual operation is performed. | Check if a complex restriction is met by the new row. |
| `AFTER`    | Runs after the actual operation is performed. | Run special cleanup operations. |
| `INSTEAD OF` | Runs instead of the actual operation. Can only be used in views. | Changes in views do not tend to easily map to unambiguous changes in the original tables, so the user may disambiguate how to "operate" on views by writing himself/herself the instructions to change the tables the view uses. |

#### INSERT, UPDATE OF ..., DELETE

This specifies which table operations cause the trigger to be executed.

Triggers for each operation make available some useful variables for you to use:
- `NEW`: valid in `INSERT` and `UPDATE`, it is the new values of the row being inserted/changed.
- `OLD`: valid in `UPDATE` and `DELETE`, it is the old values of the row being changed/deleted.

| Expression | Variables | Example |
|------------|-----------|---------|
| `INSERT`   | `NEW`     | Check if a complex restriction is met. |
| `UPDATE`   | `OLD`, `NEW` | Check if a complex restriction is met. |
| `DELETE`   | `OLD` | Special cleanup operations. |

The `UPDATE` expression allows you to specify a list of columns, so the trigger will only be activated if any of those columns is changed; e.g.,

```sql
CREATE TRIGGER mytrigger
BEFORE UPDATE OF name, address
ON "user"
...
```

#### FOR EACH

This specifies if the trigger is run once for each SQL statement, or once for each row being changed.

- `FOR EACH STATEMENT`: Trigger runs once for each SQL statement that changes the table. Rarely used, but it can be useful if you want to notify someone that a table changed (you only need to notify once, even if several changes were performed by the SQL statement). This kind of triggers do not support variables `OLD` nor `NEW`, regardless of the type of table operation.
- `FOR EACH ROW`: Trigger runs once for each modified row. Most common.

#### WHEN

If this expression is present, the trigger is only activated if the condition inside the `WHEN` evaluates to true. If you're using `FOR EACH ROW`, you can use `OLD` and `NEW` in the `WHEN` condition.

The main advantage is that it improves database performance, because the trigger can be chosen to activate only if certain conditions are met.

#### The function

This is the actual body of the trigger. You can use `OLD`, `NEW`, as well as all the SQL statements you already know, plus a few structured control flow constructs (`IF`/`CASE`, `LOOP`/`WHILE`/`FOR`) that are defined by PL/pgSQL (which is what you're using when you specify `LANGUAGE plpgsql`).

### Example

```sql
CREATE FUNCTION loan_item() RETURNS TRIGGER AS
$BODY$
BEGIN
    IF EXISTS (SELECT * FROM loan WHERE NEW.id_item = id_item AND end_t > NEW.start_t) THEN
        RAISE EXCEPTION 'An item can only be loaned to one user in every moment.';
    END IF;
    RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;
 
CREATE TRIGGER loan_item
BEFORE INSERT OR UPDATE ON loan
FOR EACH ROW
EXECUTE PROCEDURE loan_item();
```

## Transactions

A transaction is an *atomic* change to a database, which means it must either be completely executed or not executed at all.

Transactions are used to guarantee DB consistency when multiple, parallel operations are being performed (because some complex operations may place the DB in a transient inconsistent state, which could cause issues for other operations running in parallel).

### Anatomy of a transaction

```sql
BEGIN;
...
COMMIT;
```

There are other features of transactions, namely you can specify the isolation level you want for a transaction. You will have to determine the isolation level for each transaction, but won't need to code it.

You should be extra careful about transactions in your projects. Transactions cannot be easily tested at the present stage, so you must be absolutely sure that, if they were to actually be used, they would work as expected. You can test the contents of the transaction (what comes between `BEGIN` and `COMMIT`), but you cannot test if the isolation level you specified is enough to guarantee whatever it is you want to guarantee for that transaction.

Transactions often do not need to be implemented in the final product, unless you actually find issues that could be fixed with transactions (which is very rare).

### Example

TODO: Find a decent MediaLibrary example.

## References

- https://www.postgresql.org/docs/current/sql-createindex.html
- https://www.postgresql.org/docs/9.1/textsearch-controls.html
- https://forestry.io/blog/full-text-searching-with-postgres/
- https://www.postgresql.org/docs/current/plpgsql-trigger.html
- https://www.postgresql.org/docs/current/sql-createtrigger.html
