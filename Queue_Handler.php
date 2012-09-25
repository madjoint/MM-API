<?php
class Queue_Handler
{
    var $local_id =0;
    var $id_name = "req_queue";
    /*function __construct()
    {
        global $predis;
        $this->local_id = $predis->incr("queue_space");
        $this->id_name = "req_queue";
    }*/
    function push($element)
    {
        global $predis;
        $push_element = $predis->lpush($this->id_name, $element);
    }
    function pop()
    {
        global $predis;
        echo $this->id_name;
        return $predis->rpop($this->id_name);
    }
}
?>