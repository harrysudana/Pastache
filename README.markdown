Pastache
=================
Pastache is a [Panada](http://github.com/panada/Panada) Library for using [Mustache](http://defunkt.github.com/mustache/) templates.

## Example:
### Simple Usage:

```php
<?php
$this->pastache = new Libraries\Pastache;
echo $this->pastache->render('Hello {{planet}}', array('planet' => 'World!'));
// "Hello World!"
```

### Using Themes
#### Configuration theme path

If you want to make themes add in config main site with themes path

```php
'theme' => array(
        'path' => APP.'themes/'
    ),
```

Example usage in App config/main.php
```php
<?php
return array(
    
    // Just put null value if you has enable .htaccess file
    'indexFile' => INDEX_FILE . '/',
    
    'module' => array(
        'path' => GEAR,
        'domainMapping' => array(),
    ),
    
    'vendor' => array(
        'path' => GEAR.'vendors/'
    ),
    
    'alias' => array(
        /*
        'controller' => array(
            'class' => 'Controllers\Alias',
            'method' => 'index'
        ),
        */
        'method' => 'alias'
    ),

    'theme' => array(
        'path' => APP.'themes/'
    ),
);
```

#### Controller usage

```php
<?php
namespace Controllers;
use Resources, Models, Libraries;

class Home extends Resources\Controller {
    public function __construct(){
        parent::__construct();
        $this->pastache = new Libraries\Pastache;
    }
    
    public function index(){
        
        $data['title'] = 'Hello world!';
        //$this->output('admin/home', $data);
        echo $this->pastache->render('home.mustache', $data);
        
    }
}
```

## For specific usage and documentation, see:

[Panada Framework](http://github.com/panada/Panada)
[PHP Mustache](http://github.com/bobthecow/mustache.php)
[Original Mustache](http://defunkt.github.com/mustache/)
