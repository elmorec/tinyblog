# tinyblog

A super lightweight personal blog system using php and markdown.

## install

init your mysql database, run sql in the sql folder

### then config the config.ini file

```ini
; yourname
user = yourname
; timezone
timezone = timezone

; how many records will display per page
blog_per_page = 4

; db info
db_host = dbhost
db_name = dbname
db_user = user
db_passwd = password

; location where it lies on your server, such as /blog
url =

; enable or disable admin module
admin = 1

; admin login token
name = admin
passwd = 123

; sns address
github = https://github.com/yourname
twitter = https://twitter.com/yourname
weibo = http://weibo.com/yourname
gplus = https://plus.google.com/
```
