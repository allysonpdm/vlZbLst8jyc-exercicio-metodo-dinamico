<?php

namespace App\Database\Connection;

abstract class Model
{
    protected string $table;

    const DYNAMIC_METHOD_FIND_BY = 'findBy';

    public function __call(string $method, array $arguments)
    {
        if(
            !$this->verificarExistenciaMetodo($method) &&
            $this->verificarPrefixo(self::DYNAMIC_METHOD_FIND_BY, $method)
        ){
            return $this->callFindBy($method, $arguments);
        }
    }

    protected function verificarExistenciaMetodo(string $method): bool
    {
        return method_exists($this, $method);
    }

    protected function verificarPrefixo(string $prefix, string $method): bool
    {
        $methodPrefix = substr($method, 0, strlen($prefix));
        return $methodPrefix === $prefix;
    }

    protected function getFields(string $method): array
    {
        $length = strlen(self::DYNAMIC_METHOD_FIND_BY);
        $fieldName = substr($method, $length);
        $fields = preg_split('/And|Or/', $fieldName);
        $fields = array_map('strtolower', $fields);
        return array_map('trim', $fields);
    }

    protected function getConditions(string $method): array
    {
        $length = strlen(self::DYNAMIC_METHOD_FIND_BY);
        $fieldName = substr($method, $length);
        preg_match_all('/And|Or/', $fieldName, $matches);

        return $matches[0];
    }

    protected function callFindBy(string $method, array $arguments): object|false
    {
        $fields = $this->getFields($method);
        $conditions = $this->getConditions($method);

        return $this->run($fields, $conditions, $arguments);
    }

    protected function mountSql($fields, $conditions): string
    {
        $sql = "SELECT * FROM `{$this->table}`";
        foreach ($fields as $key => $field){
            if($key == 0){
                $sql .= " WHERE `{$field}` = :{$field} ";
                continue;
            }

            $condition = strtoupper($conditions[$key - 1]) ?? null;
            $sql .= "$condition `{$field}` = :{$field} ";
        }

        return trim($sql);
    }

    protected function run(array $fields, array $conditions, array $arguments): object|false
    {
        $pdo = Connection::setConnection();
        $sql = $this->mountSql($fields, $conditions);
        $prepared = $pdo->prepare($sql);

        $params = [];
        foreach ($arguments as $key => $value){
            $params = [
                ...$params,
                $fields[$key] => $value
            ];
        }
        $prepared->execute($params);

        return $prepared->fetchObject();
    }
}
