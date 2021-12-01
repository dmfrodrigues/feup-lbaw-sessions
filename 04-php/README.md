# \#4. PHP

(Better than my introduction, are the [slides by A. Restivo](https://web.fe.up.pt/~arestivo/presentation/php/))

PHP (originally *Personal Home Page*) is a programming language created in 1994. It is dynamically typed and is mainly used for web servers. It was invented to replace C as the server language.

## The PHP CLI

PHP has a CLI (Command Line Interface) which provides some features that make development easier. It can be generally accessed using the `php` command.

## How to run PHP

You'd usually use server software to run PHP like an actual server does.
However, for our small experiments we can use the CLI built-in web server:

```sh
php -S <addr>:<port>
```

This command will cause the folder from which you're running the command to be available on your browser at the specified address and port, and that PHP files that are requested on the browser will be interpreted as PHP code.

You can try it out by running `php -S localhost:8080` on this directory, and navigate to <http://localhost:8080> in your browser. By running our sample [index.php](index.php), you get something you'd never be able to get with a bunch of static HTML: the **system time** in our example.

## Accessing parameters

There are two main types of requests: GET and POST. As such, you need to be able to access the information you're passed with either type of requests.

**Quick note:** although all requests can only have **one** method (GET, POST, ...), you can create a request where you send both GET and POST variables. You can for instance create an HTML form that uses method POST to a certain URL which may have GET variables. For more, see [this link](https://stackoverflow.com/questions/2749406/post-and-get-at-the-same-time-in-php))

### GET

To get information from a GET request, you can use variable `$_GET`. It is a dictionary of keys and values, which correspond to the information you passed as GET parameters. For instance, the URL <http://localhost:8080/get.php?var1=hi&var2=2> causes you to pass the following information to the server:

- There is a variable `var1`, with value `hi`
- There is a variable `var2`, with value `2`

GET requests are useful if you're passing non-confidential information to the server, because the variables in GET requests are transparently passed in the URL, after the `?`, and separated from each othen by `&`.

### POST

Similarly to GET requests, the information passed through POST requests is available in variable `$_POST`.

You can try this out by navigating to <http://localhost:8080/form.php>, filling out the form and sending it. After you press *Go!*, the information you placed in the form will be sent to URL <http://localhost:8080/post.php> in the request body as POST variables (i.e., you won't see the variables in the new URL; it will still be <http://localhost:8080/post.php>).

Notice that, although these variables are not in the URL (and as such would not be visible by someone looking over your shoulder), they are not secret nor encrypted in any other way. After you send the request, if you're using Firefox you can actualle press Ctrl+Shift+I, navigate to *Console*, open request *POST http://localhost:8080/post.php*, go to *Request* and see your very private information laying there, in plain text (and plain sight). To cover this issue we have HTTPS, but we're not going into that today.

## Database access

PDO (PHP Data Objects) is an interface for accessing databases in PHP, and ships with PHP. The basic workflow is:

1. Create the PDO object. This object represents a connection to the database.
2. Create a statement from the PDO object using `prepare()`
3. Fill in the values and execute the statement using `execute()`
4. If it has results, get results with `fetch()` or `fetchAll()`

In our example at <http://localhost:8080/database.php>, we have a PHP script which first queries the remote database about table `author`, and prints it. Then, it presents a form which allows you to create a new database row. If you fill in the form fields and press *Go!*, a GET request will be sent to the same script `database.php`, which, upon detecting that there are GET arguments, collects those arguments and uses PDO to insert that row in the remote database table. Then, as before, `database.php` gets the whole `author` table, which allows you to check the database was in fact changed (you can also check using the pgAdmin client).

To test this script with your own database, create a `.env` file inside this folder (`04-php`) with the following contents (replace DB and USER values with your group ID, and PASSWORD with your remote database password):

```txt
HOST=db.fe.up.pt
PORT=5432
DB=lbaw21gg
USER=lbaw21gg
PASSWORD=...
```

These values will be loaded by `database.php` as environment variables using a special class `DotEnv.php`, so I don't have to place my credentials in the PHP code and accidentally push them to the repository. You should also change the necessary parts in the script (e.g., if your table has a different name/schema you'll have to change the statements accordingly).

## Sessions

Servers don't keep state information when you access them by default: if you access two different pages, the server does not really know it's the same computer and person accessing them.

PHP sessions solve that problem. A PHP session is a set of data associated to a certain client computer, which expires after a certain amount of time (10min by default). It allows for a very simple authentication service, as we're about to see.

PHP sessions work by associating, for each client, a pseudo-random token as a cookie. You can check there is in fact a PHP cookie by using Firefox, pressing Ctrl+Shift+I, navigating to *Storage* and then expanding *Cookies*, and there should be a cookie named *PHPSESSID*. This session ID is associated with domain localhost:8080, so the browser will automatically send the cookie along with any following requests to localhost:8080.

On the server side, each new session ID is stored along with any data you want. This session data is stored only on the server side, although a client can use its session ID to "authenticate" and get that session data.

We have a small script at <http://localhost:8080/login.php> which shows your session data, and asks you to login. If you can present appropriate credentials that match with those in a database (in our case we don't have a DB, so we just check if the user is `user1` and password is `1234`) and press *Login*, you'll be redirected to the same page but this time you can see that there is a new session variable called `user`. Other pages can check if session variable `user` is set to assert that a client is logged in (and assert other permissions), and because this variable is only stored on the server and it is only stored because at some point the server checked that the client-provided credentials match those in the DB, this is a safe authentication method.

If you now navigate to <http://localhost:8080/logout.php> you'll be logged out (i.e., your session is destroyed), and if you now go again to <http://localhost:8080/login.php> you'll see session variable `user` no longer exists, because you deleted your session (which is equivalent to log-out in this simple system).

## HTML includes

The classis approach to reusable HTML is not to reuse it: just copy your headers and footers across all your pages. But there are two problems with this approach:
1. You cannot reuse code, so you're needlessly replicating code
2. If you want to change an element that appears in several pages, you'll have to manually change it in all pages, or otherwise risk changing almost all pages except for one.

This can be solved by using the PHP `include` construct. It is a special instruction that allows you to literally include another PHP file into the current one, processing it as if the included file's content was in fact inlined into the PHP script calling the `include`.

If you navigate to <http://localhost:8080/homepage.php> and <http://localhost:8080/about.php>, you'll see the footer is exactly the same. Even more: if you change [footer.php](footer.php), you'll see those two webpages will immediately reflect that change. Usually you'd want to place footer.php outside of the public eye (because you'll never show the footer alone by itself) but somewhere where PHP scripts could reach to and include.

Next week you'll learn to do this in a more formal and flexible way by using Blade templates, a feature of the Laravel PHP library.
