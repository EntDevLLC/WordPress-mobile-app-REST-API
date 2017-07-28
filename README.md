# WordPress mobile app REST API
[![alt text](https://tech-labs.co/img/logo.png "Tech Labs")](https://tech-labs.co)


Easily Using the WordPress REST API in a mobile app by extended WordPress REST API Library Edit

## Installation
Upload the plugin to your wordpress, Activate it, Then use you API.

1, 2, 3: You\'re done!

## How to use
Service name space is `/tech-labs/v1` you can request throw `https://yourwebsite.com/wp-json/tech-labs/v1/anyResourceYouWant`

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
| Get mobile nav menu items | /tech-labs/v1/menus | |

### ToDo
* Pages
* Setting
* Sidebars

### Version
1.0

Author
----
[Ibrahim Mohamed Abotaleb](https://www.mrkindy.com) find me on GitHub [@mrkindy](https://github.com/mrkindy)

License
----
GPL v2