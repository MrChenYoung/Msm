<?php


namespace framework\tools;


use framework\dao\DAOPDO;

class DatabaseTableManager extends DatabaseManager
{
    // 创建数据表
    public function createTable($tableName,$sql){
        // 如果数据表不存在才创建
        if ($this -> dao -> tableExist($tableName) == false){
            // 创建表
            $this -> dao -> FetchAll($sql);
        }
    }

    /**
     * 修改表名
     * @param $oldTbName    旧的表名
     * @param $newTbName    新的表名
     */
    public function changeTbName($oldTbName,$newTbName){
        $sql = "ALTER TABLE $oldTbName RENAME TO $newTbName";
        $this -> dao -> fetchAll($sql);
    }

    /**
     * 修改表的注释
     * @param $tbName       表名
     * @param $newComment   新的注释
     */
    public function changeTbComment($tbName,$newComment){
        $sql = "ALTER TABLE $tbName COMMENT '$newComment'";
        $this -> dao -> fetchAll($sql);
    }

    /**
     * 删除表
     * @param $tbName  要删除的表名
     */
    public function deleteTable($tbName){
        $this -> dao -> deleteTable($tbName);
    }

    /**
     * 添加一条记录到表
     * @param $tbName               表名
     * @param $standardFiledName    判断标准字段，如果$data内该字段对应的value在表中不存在，则添加，否则无操作
     * @param array $data           要添加的一条记录键值对
     * @return bool                 添加是否成功
     */
    public function insertData($tbName,$standardFiledName,$data=[]){
        $success = false;
        if (!$data) return $success;

        // 查询表中是否有该记录
        $value = $data[$standardFiledName];
        $querySql = <<<EEE
SELECT * FROM $tbName WHERE $standardFiledName=$value
EEE;
        $res = $this -> dao -> FetchAll($querySql);

        if (!$res){
            // 没有该条数据 添加
            $fileds = [];
            $values = [];
            foreach ($data as $filed => $value){
                $fileds[] = $filed;
                $values[] = StringTool::singleQuotesInclude($value);
            }

            $fileStr = implode(",",$fileds);
            $valueStr = implode(",",$values);

            $insertSql = <<<EEE
                INSERT INTO $tbName ($fileStr) VALUES($valueStr);
EEE;
            // 添加数据
            $this -> dao -> FetchAll($insertSql);
            $success = true;
        }

        return $success;
    }

    /**
     * 给表添加字段
     * @param $tbName 表名
     * @param mixed ...$fields 要添加的字段
     * @return bool            添加是否成功
     */
    public function addField($tbName,...$fields){
        $success = false;
        foreach ($fields as $field){
            // 字段不存在 添加
            if (array_key_exists("name",$field) && array_key_exists("type",$field) && !$this -> fieldExist($tbName,$field["name"])){
                $fieldName = array_key_exists("name",$field) ? $field["name"] : null;
                $fieldType = array_key_exists("type",$field) ? $field["type"] : null;
                $fieldComment = array_key_exists("comment",$field) ? "COMMENT ".$field["comment"] : null;
                $filedDefault = array_key_exists("default",$field) ? "DEFAULT ".$field["default"] : null;
                $sql = "ALTER TABLE $tbName ADD $fieldName  $fieldType $filedDefault  $fieldComment";

                $this -> dao -> fetchAll($sql);
                $success = true;
            }
        }

        return $success;
    }

    /**
     * 修改表字段信息
     * @param $tbName       表名
     * @param $oldFieldName 要修改的字段名
     * @param $newFiled     新字段内容(包含字段名，字段类型，备注，默认值)
     * @return bool         修改是否成功
     */
    public function changeField($tbName,$oldFieldName,$newFiled){
        $success = false;
        if ($this -> fieldExist($tbName,$oldFieldName)){
            // 查询表创建信息
            $sql = "SHOW FULL COLUMNS FROM $tbName";
            $result = $this -> dao -> FetchAll($sql);
            foreach ($result as $v) {
                if ($v["Field"] == $oldFieldName){
                    $result = $v;
                    break;
                }
            }


            // 字段名
            $fieldName = (array_key_exists("name",$newFiled) ? $newFiled["name"] : null) ?: $oldFieldName;
            // 字段类型
            $fieldType = (array_key_exists("type",$newFiled) ? $newFiled["type"] : null) ?: $result["Type"];
            // 字段默认值
            $filedDefault = (array_key_exists("default",$newFiled) ? $newFiled["default"] : null) ?: $result["Default"];
            // 字段备注
            $fieldComment = (array_key_exists("comment",$newFiled) ? $newFiled["comment"] : null) ?: $result["Comment"];
            $fieldComment = StringTool::singleQuotesInclude($fieldComment);

            // 修改字段名
            $defaultStr = strlen($filedDefault)>0 ? "DEFAULT $filedDefault" : "";
            $sql = "ALTER TABLE $tbName CHANGE $oldFieldName $fieldName $fieldType $defaultStr COMMENT $fieldComment";
            $this -> dao -> fetchAll($sql);
            $success = true;
        }

        return $success;
    }

    /**
     * 删除表字段
     * @param $tbName       表名
     * @param $fieldName    字段名
     * @return bool         是否删除成功
     */
    public function deleteField($tbName,$fieldName){
        $success = false;
        // 字段存在 删除
        if ($this -> fieldExist($tbName,$fieldName)){
            // 删除字段名
            $sql = "ALTER TABLE $tbName DROP  $fieldName";
            $this -> dao -> fetchAll($sql);
            $success = true;
        }

        return $success;
    }

    /**
     * 检查表中是否有指定字段
     * @param $tbName 表名
     * @param $fieldName 字段名
     * @return bool 是否存在
     */
    public function fieldExist($tbName,$fieldName){
        $sql = "Describe $tbName $fieldName";
        $res = $this -> dao -> fetchAll($sql);
        if ($res){
            // 字段存在
            return true;
        }else {
            // 字段不存在
            return false;
        }
    }
}