Installation

1. git clone git@gitlab02.orcas.lan:orcas/wordpress_plugin-skeleton.git include
or
1.git submodule add --force git@gitlab02.orcas.lan:orcas/wordpress_plugin-skeleton.git include

2. in your main plugin file add in the end following line **include_once "include/autoload.php";**
3. every extension for your plugin must be included int he folder **extension/**