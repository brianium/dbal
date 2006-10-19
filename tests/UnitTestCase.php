<?php
require_once dirname(__FILE__).'/../lib/Doctrine.php';

//Doctrine::compile();

//require_once("../Doctrine.compiled.php");


//Doctrine::loadAll();
ini_set('include_path', $_SERVER['DOCUMENT_ROOT']);

function __autoload($class) {
    Doctrine::autoload($class);
}
require_once("classes.php");

require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");


class Doctrine_UnitTestCase extends UnitTestCase {
    protected $manager;
    protected $connection;
    protected $objTable;
    protected $new;
    protected $old;
    protected $dbh;
    protected $listener;
    protected $cache;
    protected $users;
    protected $valueHolder;
    protected $tables = array();

    private $init = false;

    public function init() {
        $name = get_class($this);

        $this->manager   = Doctrine_Manager::getInstance();
        $this->manager->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_IMMEDIATE);
        

        $this->tables = array_merge($this->tables, 
                        array("entity",
                              "entityReference",
                              "email",
                              "phonenumber",
                              "groupuser",
                              "album",
                              "song",
                              "element",
                              "error",
                              "description",
                              "address",
                              "account",
                              "task",
                              "resource",
                              "assignment",
                              "resourceType",
                              "resourceReference")
                              );



        if($this->manager->count() > 0) {
            $this->connection = $this->manager->getConnection(0);
            $this->connection->evictTables();
            $this->dbh     = $this->connection->getDBH();
            $this->listener = $this->manager->getAttribute(Doctrine::ATTR_LISTENER);

            $this->manager->setAttribute(Doctrine::ATTR_LISTENER, $this->listener);
        } else {
            //$this->dbh     = Doctrine_DB::getConnection();
            $this->dbh      = Doctrine_DB::getConn("sqlite::memory:");
            //$this->dbh      = new PDO("sqlite::memory:");
            $this->connection  = $this->manager->openConnection($this->dbh);
            $this->listener = new Doctrine_EventListener_Debugger();
            $this->manager->setAttribute(Doctrine::ATTR_LISTENER, $this->listener);
        }
        $this->connection->setListener(new Doctrine_EventListener());
        $this->query = new Doctrine_Query($this->connection);
        $this->prepareTables();
        $this->prepareData();

        $this->valueHolder = new Doctrine_ValueHolder($this->connection->getTable('User'));
    }
    public function prepareTables() {
        foreach($this->tables as $name) {
            $query = "DROP TABLE ".Doctrine::tableize($name);
            try {
                $this->dbh->query($query);
            } catch(PDOException $e) {

            }
        }

        foreach($this->tables as $name) {
            $name = ucwords($name);
            $table = $this->connection->getTable($name);

            $table->clear(); 
        }

        $this->objTable = $this->connection->getTable("User");
    }
    public function prepareData() {
        $groups = new Doctrine_Collection($this->connection->getTable("Group"));

        $groups[0]->name = "Drama Actors";

        $groups[1]->name = "Quality Actors";


        $groups[2]->name = "Action Actors";
        $groups[2]["Phonenumber"][0]->phonenumber = "123 123";
        $groups->save();

        $users = new Doctrine_Collection($this->connection->getTable("User"));


        $users[0]->name = "zYne";
        $users[0]["Email"]->address = "zYne@example.com";
        $users[0]["Phonenumber"][0]->phonenumber = "123 123";

        $users[1]->name = "Arnold Schwarzenegger";
        $users[1]->Email->address = "arnold@example.com";
        $users[1]["Phonenumber"][0]->phonenumber = "123 123";
        $users[1]["Phonenumber"][1]->phonenumber = "456 456";
        $users[1]->Phonenumber[2]->phonenumber = "789 789";
        $users[1]->Group[0] = $groups[2];

        $users[2]->name = "Michael Caine";
        $users[2]->Email->address = "caine@example.com";
        $users[2]->Phonenumber[0]->phonenumber = "123 123";

        $users[3]->name = "Takeshi Kitano";
        $users[3]->Email->address = "kitano@example.com";
        $users[3]->Phonenumber[0]->phonenumber = "111 222 333";

        $users[4]->name = "Sylvester Stallone";
        $users[4]->Email->address = "stallone@example.com";
        $users[4]->Phonenumber[0]->phonenumber = "111 555 333";
        $users[4]["Phonenumber"][1]->phonenumber = "123 213";
        $users[4]["Phonenumber"][2]->phonenumber = "444 555";

        $users[5]->name = "Kurt Russell";
        $users[5]->Email->address = "russell@example.com";
        $users[5]->Phonenumber[0]->phonenumber = "111 222 333";

        $users[6]->name = "Jean Reno";
        $users[6]->Email->address = "reno@example.com";
        $users[6]->Phonenumber[0]->phonenumber = "111 222 333";
        $users[6]["Phonenumber"][1]->phonenumber = "222 123";
        $users[6]["Phonenumber"][2]->phonenumber = "123 456";

        $users[7]->name = "Edward Furlong";
        $users[7]->Email->address = "furlong@example.com";
        $users[7]->Phonenumber[0]->phonenumber = "111 567 333";

        $this->users = $users;
        $this->connection->flush();
    }
    public function getConnection() {
        return $this->connection;
    }
    public function clearCache() {
        foreach($this->tables as $name) {
            $table = $this->connection->getTable($name);
            $table->getCache()->deleteAll();
        }
    }
    public function setUp() {
        if( ! $this->init) $this->init(); 
        
        if(isset($this->objTable))
            $this->objTable->clear();
        
        $this->init    = true;
    }
}
?>
