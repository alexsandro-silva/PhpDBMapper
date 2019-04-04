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

use PhpDBMapper\Exceptions\AttributeNotFoundException;

/**
 * Description of BaseModel
 *
 * @author alexsandro
 */
abstract class BaseModel {
    
    protected $attributes = array();
    protected $configuration = array();
    
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
    
    public function getTableName() {
        if(array_key_exists('table', $this->configuration)) {
            return $this->configuration['table'];
        } else {
            throw new \RuntimeException("O nome da tabela não foi definido");
        }
    }
    
    public abstract function config();
}
