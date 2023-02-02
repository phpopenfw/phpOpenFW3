-----------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------
# phpOpenFW
-----------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------
phpOpenFW is an open source PHP web development framework released under the MIT License. Version 3.0 changes direction from version 2.x by dropping support for traditional web applications and focusing on the development of APIs, batch / backend systems, and command line utilities.

-----------------------------------------------------------------------------------------------------------
## Author
-----------------------------------------------------------------------------------------------------------
Christian J. Clark

-----------------------------------------------------------------------------------------------------------
## Website / Documentation
-----------------------------------------------------------------------------------------------------------
http://www.phpopenfw.org/

-----------------------------------------------------------------------------------------------------------
## License
-----------------------------------------------------------------------------------------------------------
Released under the MIT License: https://mit-license.org/

-----------------------------------------------------------------------------------------------------------
## Version
-----------------------------------------------------------------------------------------------------------
3.0.4

-----------------------------------------------------------------------------------------------------------
## Requirements
-----------------------------------------------------------------------------------------------------------
phpOpenFW requires PHP >= 7.0

-----------------------------------------------------------------------------------------------------------
## Features
-----------------------------------------------------------------------------------------------------------
phpOpenFW has an abundance of features that facilitate the development of powerful, flexible applications, sites, and scripts. 
Below is an outline of some of the features offered by phpOpenFW:

* Database Abstraction Layers
* Active Record Class
* SQL Query Builder
* Cache Abstraction Layer
* MongoDB Abstraction Layer
* LDAP Abstraction Layer
* XML Generation and Processing Classes
* Validation Object
* Code Benchmark

-----------------------------------------------------------------------------------------------------------
## Apache ModRewrite Rules
-----------------------------------------------------------------------------------------------------------

* RewriteEngine On
* RewriteRule  .*favicon\.ico$ - [L]
* RewriteRule ^.*$ index.php [L,qsa]

**If you are using Virtual Document Roots with Apache your rules will most likely need to look something like this:**

* RewriteEngine On
* RewriteBase /
* RewriteRule ^.*favicon\.ico$ - [L]
* RewriteRule ^.*$ index.php [L,qsa]

-----------------------------------------------------------------------------------------------------------
## phpOpenFW v2.x to v3.0 Migration
-----------------------------------------------------------------------------------------------------------

**Change:**

```php
\phpOpenFW\Framework\LiteFW::Run($base_dir);
```

**To:**

```php
\phpOpenFW\Core::Bootstrap($base_dir);
```

--------------------------------------------------------------

**Change:**

Anything that starts with "\phpOpenFW\Framework\Core"

**To:**

To start with "\phpOpenFW\Core"
