<?php
    include dirname(__FILE__).'/config.php';

    class Dbconnect {

        protected $isConnected,$datab,$rowCount=0,$_table=null;
        public $lastInsertedId,$columns = array('*'),$lastQuery=null,$queries=array(),$pgntion=array();
        private $order = null, $group = null, $limit = null, $joins = null,$asColumns = array();

        private $reserved = array(
            'count',
            'max',
            'distinct'
        );

        public function __call($string,$args){
            $split = preg_split("/(?<=[a-z_])(?![a-z_])/", $string, -1, PREG_SPLIT_NO_EMPTY);
            if(count($split)>1){
                if(method_exists(__CLASS__,$split[0])){
                    $split = array_map('strtolower',$split);
                    $method = $split[0];
                    $table = $split[1];
                    array_unshift($args,$table);
                    unset($split[0],$split[1]);
                    $split = array_values($split);
                    if(isset($split[0]) && $split[0]=='count'){
                        $this->columns = array("count(".(isset($split[0])?$split[0]:array("*")).") as count");
                        $args[] = true;
                    }else{
                        $this->columns = count($split) ? $split : ($this->columns[0]!="*"?$this->columns:array("*"));
                    }
                    return call_user_func_array(array(__CLASS__,$method),$args);
                }else{
                    die( "function {$string} does not exist" );
                }
            }else{
                die( "function {$string} does not exist" );
                }
            }

        public function __toString(){
            return $this->lastQuery;
        }

        public function __construct($options=array()){
            global $APP_CONFIG;
            $username = $APP_CONFIG['DATABASE']['username'];
            $password = $APP_CONFIG['DATABASE']['password'];
            $host     = $APP_CONFIG['DATABASE']['host'];
            $dbname   = $APP_CONFIG['DATABASE']['dbname'];
            $this->isConnected = true;
            try {
                if(!$APP_CONFIG['DATABASE']['link']){
                    $this->datab = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options);
                    $this->datab->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->datab->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }else{
                    $this->datab = $APP_CONFIG['DATABASE']['link'];
                }
            }
             catch(PDOException $e) {
                    $this->isConnected = false;
                    throw new Exception($e->getMessage());
                }
        }

        public function Disconnect(){
            try {
                $this->datab = null;
                $this->isConnected = false;
            } catch(PDOException $e) {
                   $this->errMsg($e);
            }
        }

        public function getRow($query, $params=array()){
            try {
                $stmt = $this->datab->prepare($query);
                $stmt->execute($params);
                return $stmt->fetch();
            } catch(PDOException $e) {
                $this->errMsg($e);
            }
        }

        public function getRows($query, $params=array()){
            try {
                $stmt = $this->datab->prepare($query);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } catch(PDOException $e) {
                $this->errMsg($e);
            }
        }

        public function insertRow($query, $params=array()){
            try {
                $stmt = $this->datab->prepare($query);
                $stmt->execute($params);
                $this->lastInsertedId = $this->datab->lastInsertId();
            } catch(PDOException $e) {
                $this->errMsg($e);
            }
        }

        public function updateRow($query, $params=array()){
            try {
                $stmt = $this->datab->prepare($query);
                $stmt->execute($params);
                return $stmt->rowCount();
            } catch(PDOException $e) {
                $this->errMsg($e);
            }
        }

        public function deleteRow($query, $params=array()){
            try {
                        $stmt = $this->datab->prepare($query);
                $stmt->execute($params);
                return $stmt->rowCount();
            } catch(PDOException $e) {
                $this->errMsg($e);
            }
        }

        public function order($string=""){
            $this->order = " order by ".$string;
            return $this;
        }

        public function group($string=""){
            $this->group = " group by ".$string;
            return $this;
        }

        private function page($lmt=1,$page){
            return ($page==1?0:(($page-1)*$lmt)).",".$lmt;
        }

        public function limit($lmt=1,$page=null){
            $this->pgntion['offset'] = $lmt;
            $this->pgntion['page'] = $page;
            $lmt = $page ? $this->page($lmt,$page) : $lmt;
            $this->limit = " limit ".$lmt;
            return $this;
        }

        public function columns($data=array()){
            foreach($data as $cols){
                $exp = explode(' AS ',$cols);
                if(count($exp)==2){
                    $this->asColumns[$exp[1]] = $exp[0];
                }
            }
            $this->columns = $data;
            return $this;
        }

        private function errMsg($obj){
            foreach($obj->getTrace() as $t){
                echo (isset($t['file']))?$t['file']." on line ".$t['line']."\n":"";
            }
            echo $obj->getMessage();
            $this->isConnected = false;
                    return 0;
        }

        public function has($table="",$data=array()){
            $this->columns = array("id");
            return $this->Select($table,$data);
        }

        public function where($data=array()){
            $sql = '';
            $hasOr = false;
            if($data){
                $sql .= "where";
                foreach($data as $col => $val){
                    $exp = explode(' ',$col);
                    if($col && $val===false){
                        $sql .= " ".$col." and";
                        unset($data[$col]);
                    }elseif(strtolower($col)=="or" && is_array($val)){
                        $hasOr = true;
                        /*OR CONDITIONS*/
                        $sql.=" (";
                        foreach($val as $k => $v){
                            $data[$k] = $v;
                            $expOr = explode(' ',$k);
                            if(count($expOr)==1){
                                $dot = explode('.',$k);
                                $as = in_array($k,$this->asColumns) ? $this->asColumns[$k] :$k;
                                $sql .= " {$as} = ";
                                if(count($dot)==2){
                                    unset($data[$k]);
                                    $k = str_replace('.','_',$k);
                                    $data[$k] = $v;
                                }
                                $sql .= ":{$k} or";
                            }else{
                                $as = in_array($expOr[0],array_keys($this->asColumns)) ? $this->asColumns[$expOr[0]] :$expOr[0];
                                $sql .= " {$as} {$expOr[1]} ";
                                if($expOr[1]!='in'){
                                    $dot = explode('.',$expOr[0]);
                                    if(count($dot)==2){
                                        unset($data[$expOr[0]]);
                                        $expOr[0] = str_replace('.','_',$expOr[0]);
                                    }
                                    $sql .= ":{$expOr[0]} or";
                                    $data[$expOr[0]] = $v;
                                    unset($data[$k]);
                                }else{
                                    $sql.=$val . " and";
                                    unset($data[$col]);
                                }
                            }
                        }
                        unset($data['or']);
                        $sql = rtrim($sql,' or').")";
                        /*OR CONDITIONS END*/
                    }elseif(count($exp)==1){
                        $as = in_array($col,$this->asColumns) ? $this->asColumns[$col] :$col;
                        $sql .= " {$as} = ";
                        $dot = explode('.',$col);
                        if(count($dot)==2){
                            unset($data[$col]);
                            $col = str_replace('.','_',$col);
                            $data[$col] = $val;
                        }
                        $sql .= " :{$col} and";
                    }else{
                        $as = in_array($exp[0],array_keys($this->asColumns)) ? $this->asColumns[$exp[0]] :$exp[0];
                        $sql .= " {$as}";
                        for($x=1;$x<count($exp);$x++){
                            $sql .= " ".$exp[$x]." ";
                        }
                        if($exp[1]!='in'){
                            $dot = explode('.',$col);
                            if(count($dot)==2){
                                unset($data[$exp[0]]);
                                $exp[0] = str_replace('.','_',$exp[0]);
                            }
                            $sql .= ":{$exp[0]} and";
                            $data[$exp[0]] = $val;
                            unset($data[$col]);
                        }else{
                            $sql.=$val . " and";
                            unset($data[$col]);
                        }
                    }
                }
                $sql = rtrim($sql,'and');
            }
            return array(
                'sql'=>$sql,
                'data'=>$data
            );
        }

        public function Select($table="",$data=array(),$isMany=false){
            $sql = "Select ".implode(',',$this->columns)." from {$table} as {$table} ";
            $sql .= $this->joins;
            $where = $this->where($data);
            $sql .= $where['sql'];
            $data = $where['data'];
            if($this->limit){
                $pg = "Select count({$table}.id) as count from {$table} as {$table} ";
                $page = $this->getRow($pg.$this->joins.$where['sql'].$this->group.$this->order,$where['data']);
                $this->pgntion['totalRows'] = $page['count'];
                $val = $page['count'] / $this->pgntion['offset'];
                $int = intval($val);
                $this->pgntion['totalPages'] = $int < $val ? $int + 1 : $int;
            }
            $sql = $sql.$this->group.$this->order.$this->limit;
            $this->lastQuery = $sql;
            $this->queries[] = $sql;
            $this->order = $this->group = $this->joins = $this->joins = $this->limit = null;
            $this->asColumns = array();
            $this->columns = array("*");
            return !$isMany ? $this->getRows($sql,$data) : $this->getRow($sql,$data);
        }

        public function joins($data=array()){
            $data['type'] = isset($data['type']) ? $data['type'] : 'left';
            $as = (isset($data['as']) ? $data['as'] : $data['table'] );
            $this->joins .= "{$data['type']} join {$data['table']} {$as} on {$data['on']} ";
            return $this;
        }

        public function Insert($table="",$data=array()){
            $sql = "Insert into {$table} ";
            $columns = "(`".implode("`,`",array_keys($data))."`)";
            $values = " values (:".implode(",:",array_keys($data)).")";
            $this->queries[] = $sql.$columns.$values;
            $this->insertRow($sql.$columns.$values,$data);
            return $this->lastInsertedId;
        }

        public function Update($table="",$data=array()){
            if(!isset($data['id'])){
                die("Updating a row without an ID.");
                exit();
            }
            $sql = "Update {$table} set ";
            foreach($data as $col => $val){
                if($col=='id'){continue;}
                $sql.="`{$col}` = :{$col},";
            }
            $sql = rtrim($sql,',');
            $sql.= " Where id = :id";
            $this->queries[] = $sql;
            return $this->updateRow($sql,$data);
        }

        public function Updatewhere($table="",$data=array(),$where=array()){
            $sql = "Update {$table} set ";
            foreach($data as $col => $val){
                $sql.="`{$col}` = :{$col},";
            }
            $sql = rtrim($sql,',');
            if($where){
                $sql .= " where";
                foreach($where as $col => $val){
                    $exp = explode(' ',$col);
                    if(count($exp)==1){
                        $sql .= " `{$col}` = :{$col} and";
                    }else{
                        $sql .= " `{$exp[0]}` {$exp[1]} ";
                        if($exp[1]=='in'){
                            $sql .= $val." and";
                        }else{
                            $sql .= ":{$exp[0]} and";
                            $where[$exp[0]] = $val;
                        }
                        unset($where[$col]);
                    }
                }
                $sql = rtrim($sql,'and');
                $data = array_merge($data,$where);
            }
            $this->queries[] = $sql;
            return $this->updateRow($sql,$data);
        }

        public function Delete($table="",$data=array()){
            $sql = "Delete from {$table} ";
            if($data){
                $sql .= "where";
                foreach($data as $col => $val){
                    $exp = explode(' ',$col);
                    if(count($exp)==1){
                        $sql .= " `{$col}` = :{$col} and";
                    }else{
                        $sql .= " `{$exp[0]}` {$exp[1]} ";
                        if($exp[1]=='in'){
                            $sql .= $val." and";
                        }else{
                            $sql .= ":{$exp[0]} and";
                            $data[$exp[0]] = $val;
                        }
                        unset($data[$col]);
                    }
                }
                $sql = rtrim($sql,'and');
            }
            return $this->deleteRow($sql,$data);
        }
    }
?>
