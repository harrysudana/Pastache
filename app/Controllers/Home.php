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
        
        //$this->output('home', $data);
        echo $this->pastache->render('home.mustache', $data);
    }
}