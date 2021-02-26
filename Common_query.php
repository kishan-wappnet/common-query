<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Common_query extends Model{
    /*
    * This function is usefull for create dynamically query for any table which need output in datatable ajax format and have to customize the query
    * upto extent you want
    */
    public static function get_list_datatable_ajax($table,$datatable_fields, $conditions_array, $getfiled, $request, $join_str = array(),$where_date=[]) {
        
		$output = array();
	//Query object of DB and will use to chain the functions of query you want
        $data = DB::table($table)
                ->select($getfiled);
	//loop through join array for multiple joins
        if (!empty($join_str)) {
            //$data->where(function($query) use ($join_str) {
            foreach ($join_str as $join) {
                if (!isset($join['join_type'])) {
                    $data->join($join['table'], $join['join_table_id'], '=', $join['from_table_id']);
                } else {
                    $data->join($join['table'], $join['join_table_id'], '=', $join['from_table_id'], $join['join_type']);
                }
            }
            //});
        }
        //multiple and condition
        if (!empty($conditions_array)) {
            $data->where($conditions_array);
        }
        //if need condition based on date
        if(!empty($where_date)){
            foreach($where_date as $date){
                $data->whereDate($date[0],$date[1],$date[2]);
            }

        }
        //dynamically search the fields comming from datatable frontend
        if ( !empty($request) && $request['search']['value'] != '') {
            $data->where(function($query) use ($request, $datatable_fields) {
                for ($i = 0; $i < count($datatable_fields); $i++) {
                    if ($request['columns'][$i]['searchable'] == 'true') {
                        $query->orWhere($datatable_fields[$i], 'like', '%' . $request['search']['value'] . '%');
                    }
                }
            });
        }
        //dynamically order by the fields comming from datatable frontend
        if (isset($request['order']) && count($request['order'])) {
            for ($i = 0; $i < count($request['order']); $i++) {
                if ($request['columns'][$request['order'][$i]['column']]['orderable'] == true) {
                    $data->orderBy($datatable_fields[$request['order'][$i]['column']], $request['order'][$i]['dir']);
                }
            }
        }
        $count = $data->count();
        $start =  !empty($request['start'])?$request['start']:0;
        $length =  !empty($request['length'])?$request['length']:0;
        $draw = !empty($request['draw'])?$request['draw']:10;
        $data->skip($start)->take($length);
        //print_r(DB::getQueryLog());exit;
        $output['recordsTotal'] = $count;
        $output['recordsFiltered'] = $count;
        $output['draw'] = $draw;
        $final_data = $data->get();

        //$response['perPageCount'] = $i;
		//print_r($sms_data); die();
        $output['data'] = $final_data;
        //final output with json response as per the datatable requirement format
        return json_encode($output);
    }

    
}
