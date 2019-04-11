<?php

/*
 * Copyright 2019 alexsandro.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PhpDBMapper;

use PhpDBMapper\Database\DatabaseAdapter;
use PhpDBMapper\Database\DB;
use PhpDBMapper\Exceptions\AttributeNotFoundException;

/**
 * Description of BaseModel
 *
 * @author alexsandro
 */
class BaseModel {
    
    protected $attributes = array();
    
    static $db_name = DatabaseAdapter::_DEFAULT;
    static $tableName;
    static $primary_key = 'id';
    static $keys;
    
    function __construct($attributes = null) {
        $this->attributes = $attributes;
    }
 
    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }
    
    public function __get($name) {
        if(array_key_exists($name, $this->attributes)){
            return $this->attributes[$name];
        } else {
            throw new AttributeNotFoundException(sprintf("Atributo \"%s\" não encontrado", $name));
        }
    }
    
    public function __isset($name) {
        return array_key_exists($name, $this->attributes);
    }
    
    public function __unset($name) {
        unset($this->attributes[$name]);
    }
    
    public function get_table_name() {
        return static::$tableName;
    }

    private function getDatabaseAdapter() {
        $database = new DatabaseAdapter(static::$db_name);
        return $database;
    }

    public static function find($where, $whereValues) {
        $sql = sprintf("SELECT * FROM %s WHERE %s", static::$tableName, $where);
        $result = self::getDatabaseAdapter()->fetch($sql, $whereValues);
        $object = new self();
        foreach ($result as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    public static function find_all() {
        $sql = "SELECT * FROM %s";
        $sql = sprintf($sql, static::$tableName);
        $result = DB::fetch_all($sql);
        $objects = array();
        for ($i = 0; $i < sizeof($result); $i++) {
            $object = new self();
            foreach ($result[$i] as $key => $value) {
                $object->$key = $value;
            }
            array_push($objects, $object);
        }

        return $objects;
    }

    private function insert() {
        $fields = array();
        $values = array();
        foreach ($this->attributes as $field => $value) {
            $fields[] = $field;
            array_push($values, '?');
        }

        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", static::$tableName,
            implode(', ', $fields), implode(', ', $values));

        $db = $this->getDatabaseAdapter();
        $db->execute($sql, array_values($this->attributes));
        $lastId = $db->get_last_insert_id();
        if ($lastId) {
            $this->attributes[static::$primary_key] = $lastId;
        }

        return $this;
    }

    public function save(){
        return $this->insert();
    }
    
}
