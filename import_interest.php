<?php
ini_set('include_path','/webroot/api/lt/');
include_once("PorterStemmer.class.php");
include_once("Debug.class.php");
include_once("Cache.class.php");
include_once("CacheGlobal.class.php");
include_once("Transformer.class.php");
include_once("TransformerEN.class.php");
include_once("LT.class.php");
include_once("LTen.class.php");
include_once("KeyValueStoreFile.class.php");
class ImportInterest
{
    public function _interest_prepare($title) {
        $this->Debug = new Debug('rInterests.Debug');
        $this->Cache = new CacheGlobal('rInterests.CacheGlobal');
        $this->Cache->set('title', 0, $title);
        $this->Transformer = new TransformerEN('rInterests.TransformerEN', $this->Cache, $this->Cache, $this->Debug);		
        $this->Transformer->prepare();
        $GLOBALS['match_debug'] .= $this->Debug->getDebugInfo(0);
        $GLOBALS['match_debug'] .= "END _debug_info\n";
        $GLOBALS['match_debug'] .= $this->Debug->getProfileInfo();
        $GLOBALS['match_debug'] .= "END _profile_info\n";
        return($this->Cache->getArray(array('title', 'stems', 'stems_numless', 'kind', 'forbidden_count', 'forbidden_words'), 0));
    }
    function Select($sql)
    {
      
        $result = array();
        $resource = mysql_query($sql);
        if($resource){        
            if(mysql_num_rows($resource) > 0) {
                
                for($i = 0; $i < mysql_num_rows($resource); $i++) {
                        $tmp_result = mysql_fetch_array($resource, MYSQL_ASSOC);
                        $result[] = $tmp_result;
                }
                return $result;
            }
        }
        return $result;
    }
    public function insert($title)
    {
        $lt = $this->_interest_prepare($title);
       $interests  = file_get_contents("user_not_found.csv");
       $lines = explode("\n", $interests);
        mysql_connect("localhost","rest_5","mismatch");
        mysql_select_db("rest_5"); 
       $myFile = "user_not_found_2.csv";
            $fh = fopen($myFile, 'a') or die("can't open file"); 
       for($i=0;$i<count($lines); $i++)
       {
            $values = explode(",",$lines[$i]);
            $sql = "select id from user where mobile_number =" . $values[0];
            $r = $this->Select($sql);
            if(count($r)>0)
            {
                mysql_query("INSERT INTO `interest` (`user`,`title`,`stems`,`stems_numless`,`kind`,`description`,`latitude`,`longitude`,`distance`,`expire`,`create_time`)  VALUES (".$values[0]. ",'".$values[1] . "','" . $lt['stems'] . "','" . $lt['stems_numless']  ."','" .$lt['kind'] . "','',10,10,10,732,(UNIX_TIMESTAMP() + (60 * 60 * 4)))");

            }
            else
            {
             fwrite($fh, $lines[$i]);
            }
           
            
       } fclose($fh);
    }
}

$obj = new ImportInterest();
$obj->insert("SAMSUNG GALAXY NOTE BRAND NEW WITH COMPANY WARRANTY FOR SALE @ FONO TECHNOLOGIES WELLAWATTE!!!");

?>