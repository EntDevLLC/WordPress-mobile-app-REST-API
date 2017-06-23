# WordPress mobile app REST API

Easily Using the WordPress REST API in a mobile app

## Installation
Upload the plugin to your wordpress, Activate it, then build your home page from pages section.

1, 2, 3: You\'re done!

## How to use
Service name space is `/tech-labs/v1` you request throw `https://yourwebsite.com/wp-json/tech-labs/v1/anyResourceYouWant`

#### API Calls
| Proccess  | Base Route | Notes |
| ------------- | ------------- | ------------- |
| Get posts  | /tech-labs/v1/posts | you can make send get or post parm `order` , `orderby` , `per_page` , `page` , `category` |
| Get post item  | /tech-labs/v1/posts/(:id) | only send post ID parm `id` |
| Get categories  | /tech-labs/v1/categories | you can make send get or post parm `order` , `orderby` , `hide_empty` |
| Get post comments  | /tech-labs/v1/comments/(:id) | only send post ID parm `id` |
| Make user comment | /tech-labs/v1/comments/make_comment | send `post_id` , `username` , `password` , `content` for registered users and `post_id` , `fullname` , `content` for geust comment |
| Verifiy login information | /tech-labs/v1/users/make_authenticate | send `username` , `password` |
| Register new user | /tech-labs/v1/users/register | send `username` , `password` , `email` , `fullname` |


### Version
1.0

License
----

GPL v2