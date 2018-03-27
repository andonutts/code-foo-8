# code-foo-8

IGN Code Foo 8 application

## 1. About me

Hey there. My name is Andy He, and I am a (soon to be graduating) Master's student studying electrical engineering at the University of Minnesota Twin Cities. I'm passionate about art, music, technology, and of course, video games. My earliest experience with video games was watching my brother play Star Fox 64 and Super Mario 64, and getting whupped at Super Smash Bros. on the Nintendo 64. Since then, I've developed an appreciation for all things gaming-related and consider myself a die hard Nintendo fan (proud Wii U owner here).

My interest in gaming has made me a frequent consumer of IGN's content over the past decade, and I have a lot of respect for IGN's presence in the gaming industry. Nowadays, I often look to IGN for video game, movie, and TV show reviews as well as coverage of large events such as E3, SDCC, etc. I also regularly tune in to IGN's weekly Nintendo podcast (and best Nintendo podcast), Nintendo Voice Chat. Aside from all that, the prospect of doing an internship at IGN excites me because I would get to meet and work with people who share my passion for games, gain valuable experience in a professional software development environment, and experience life outside of the Midwest.

Even though my academic background isn't in software engineering, I have had a healthy exposure to a variety of different programming languages and concepts, via both school courses and personal interest. More importantly, I feel that my training as an electrical engineering student has supplied me with the necessary tools to be a competent software developer, namely, strong organizational skills and the patience and resourcefulness to independently seek out solutions. If given the opportunity, I'm confident I can be a positive contributor to whatever project is thrown at me. 

Thank you for your time and consideration!

## 2. Building the Eiffel Tower out of Geomags&#8482;

## 3. ChickenRoad

### Prerequisites

*  Java SE 1.7+

### Usage



## 4. Back end: RssLoader

A series of PHP scripts used to create tables in a MySQL database, then load them with RSS data from https://ign-apis.herokuapp.com/content/feed.rss.

### Prerequisites

* PHP 5.6+
* MySQL 5.7.X

### Usage

First, replace the placeholder login credentials in `db_config.php` to that of the desired server, user, and database. For example:

```php
$servername = 'localhost';
$username = 'andy';
$password = 'andy123';
$dbname = 'codefoo8';
```

Make sure the user has been granted all privileges on the database, i.e. `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `DROP`, and `REFERENCES`.

Next, simply execute the following commands from the `RssLoader` directory:

1. `php create_tables.php`
2. `php load_rss_data.php`

**Warning**: If using a pre-existing database, be aware that executing `php create_tables.php` will drop any tables named `content` or `content_types` in that database.

All done! The `content` and `content_types` tables should now be populated with RSS data using the design described below.

### Database Design

<p align="center">
  <img src="./RssLoader/rss_db.png" alt="Database diagram">
</p>

Inspecting `create_tables.php`, we can see that the following MySQL queries were used to generate the tables:

```
```

My approach to designing the database began with an observation of the data to be stored. 

## Survey

I discovered this application via Twitter post by @IGN.