<?php
namespace Sinkab;


class Database extends \mysqli {

    public function __construct($host, $username, $passwd, $dbname)
    {
        parent::__construct($host, $username, $passwd);
        $this->real_query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8");
        $this->select_db($dbname);
        $this->set_charset('utf8');
    }

    public function one_field($sql){
        $res=$this->query($sql);
        if($res){
            $row=$res->fetch_row();
            return isset($row[0]) ? $row[0] : false;
        }else{
            return false;
        }
    }
    public function one_row($sql){
        $res=$this->query($sql);
        $row=$res->fetch_assoc();
        return $row===null ? false : $row;
    }
    public function key_value($sql){
        $tmp_array=array();
        $res=$this->query($sql);
        while ($row=$res->fetch_row()) {
            $tmp_array[$row[0]]=$row[1];
        }
        return $tmp_array;
    }
    public function value_array($sql){
        $tmp_array=array();
        $res=$this->wm_query($sql);
        while ($row=$res->fetch_row()) {
            $tmp_array[]=$row[0];
        }
        return $tmp_array;
    }
    public function to_list($sql, $default="NULL"){
        $tmp_array=array();
        $res=$this->wm_query($sql);
        while ($row=$res->fetch_row()) {
            $tmp_array[]=$row[0];
        }
        return count($tmp_array) ? implode(",",$tmp_array) : $default;
    }
    public function to_array($sql, $key=""){
        $tmp_array=array();
        $res=$this->wm_query($sql);
        if ($key==="") {
            while ($row=$res->fetch_assoc()) {
                $tmp_array[]=$row;
            }
        } else {
            while ($row=$res->fetch_assoc()) {
                $tmp_array[$row[$key]]=$row;
            }
        }
        return $tmp_array;
    }
}
?>