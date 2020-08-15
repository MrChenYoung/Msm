<?php
/**
 * 数据库访问层的接口
 * 用来规范DAOMysqli  DAOPDO这些类的
 */
namespace framework\dao;
interface i_DAOPDO
{
    //查询一条记录的方法
    public function fetchOne($sql);
    //查询所有记录
    public function fetchAll($sql);
    //查询一个字段的值
    public function fetchColumn($sql);
    //执行增删改的方法
    public function query($sql);
    //返回刚刚执行插入语句的主键
    public function lastId();
    //对数据引号转义、并包裹的方法
    public function quote($data);
}
