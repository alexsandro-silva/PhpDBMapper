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
use ReflectionClass;

/**
 * Description of BaseModel
 *
 * @author alexsandro
 */
class BaseModel {

    static $db_name = DatabaseAdapter::_DEFAULT;
    static $tableName;
    static $primary_key = 'id';
    static $keys;

    protected $attributes = array();
    protected $dirty = array();
    
    function __construct($attributes = null) {
        $this->attributes = $attributes;
    }
 
    public function __set($name, $value) {
        if (isset($this->attributes) && sizeof($this->attributes) > 0) {
            if (array_key_exists($name, $this->attributes)) {
                $this->__setDirty($name, $value);
            }
        }

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

    private function __setDirty($name, $value) {
        if ($this->attributes[$name] != $value) {
            $this->dirty[$name] = $value;
        }
    }

    private function __get_table() {
        $clazz = new ReflectionClass(get_called_class());
        $table = $clazz->getStaticPropertyValue('tableName');
        return $table;
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
        $class = get_called_class();
        $object = new $class();
        foreach ($result as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    public static function find_all() {
        $sql = sprintf("SELECT * FROM %s", static::$tableName);
        $result = self::getDatabaseAdapter()->fetch_all($sql);
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

    private function update() {
        $fields = array();
        if (sizeof($this->dirty) > 0) {
            foreach ($this->dirty as $field => $value) {
                array_push($fields, $field . ' = ?');
            }

            $sql = sprintf("UPDATE %s SET %s", $this->__get_table(), implode(', ', $fields));
            $sql .= sprintf(" WHERE %s = %s", static::$primary_key, $this->attributes[static::$primary_key]);

            $db = $this->getDatabaseAdapter();
            return $db->execute($sql, array_values($this->dirty));
        }
    }

    public function delete() {
        $sql = sprintf("DELETE FROM %s WHERE %s = ?", static::$tableName, static::$primary_key);
        $statement = $this->getDatabaseAdapter()->execute($sql, [$this->attributes[static::$primary_key]]);
        return $statement->rowCount() > 0 ? true : false;
    }

    public function save(){
        if (array_key_exists(static::$primary_key, $this->attributes)) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
}
