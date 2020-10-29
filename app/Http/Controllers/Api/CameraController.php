<?php
namespace App\http\Controllers\Api;

use App\Http\Controllers\Api\CommonController;
use Illuminate\Support\Facades\Log;

class CameraController extends CommonController{

    public function __construct()
    {
        $this->model = New \App\Models\CameraLogModel();
    }

    public function received(){
        $xml = file_get_contents('php://input');

        $xml_object = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $data = [
            'attributes' => json_encode(get_object_vars($xml_object)['@attributes']),
            'ip_address' => $xml_object->ipAddress,
            'protocol_type' => $xml_object->protocolType,
            'mac_address' => $xml_object->macAddress,
            'channel_id' => $xml_object->channelID,
            'date_time' => $xml_object->dateTime,
            'active_post_count' => $xml_object->activePostCount,
            'event_type' => $xml_object->eventType,
            'event_state' => $xml_object->eventState,
            'event_description' => $xml_object->eventDescription,
            'channel_name' => $xml_object->channelName,
            'statistical_methods' => $xml_object->peopleCounting->statisticalMethods,
            'enter' => $xml_object->peopleCounting->enter,
            'exit' => $xml_object->peopleCounting->exit,
            'pass' => $xml_object->peopleCounting->pass,
            "date" => date("Y-m-d"),
            'create_time' => date("Y-m-d H:i:s")
        ];

        if($xml_object->peopleCounting->statisticalMethods == 'timeRange'){
            $data['start_time'] = $xml_object->peopleCounting->TimeRange->startTime;
            $data['end_time'] = $xml_object->peopleCounting->TimeRange->endTime;
        }

        if($xml_object->peopleCounting->statisticalMethods == 'realTime'){
            $data['real_time'] = $xml_object->peopleCounting->RealTime->time;
        }

        $this->model->insert($data);

        Log::info("接收到报文:\r\n".json_encode($xml));
    }

    /**
     * 日志列表
     * @param page int 当前页码
     * @param limit int 分页大小
     * @param start_date date 筛选开始时间
     * @param end_date date 筛选结束时间
     * @param type 数据类型 realTime警报触发上报日志 timeRange定时上报日志
     */
    public function getLogList(){
        $start_date = request()->start_date ? date("Y-m-d",strtotime(request()->start_date)) : null;
        $end_date = request()->end_date ? date("Y-m-d",strtotime(request()->end_date)) : null;
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $type = request()->type;

        $condition[] = ['id','>',0];

        if($type){
            $condition[] = ['statistical_methods','=',$type];
        }

        if($start_date && $end_date){
            $condition[] = ['date','>=',$start_date];
            $condition[] = ['date','<=',$end_date];
        }
        


        $res = $this->model->where($condition)->paginate($limit);

        if($res->isEmpty()){
            return $this->returnApi(203,'暂无数据');
        }

        return $this->returnApi(200,'ok',$res);
    }

    /**
     * 统计数据
     * @param page int 当前页码 type为day时生效
     * @param limit int 分页大小 type为day时生效
     * @param start_date date 筛选开始时间
     * @param end_date date 筛选结束时间
     * @param type 统计纬度 day天 nonth月 year年
     */
    public function getCountByDate(){
        $page = request()->page ? intval(request()->page) : 1;
        $limit = request()->limit ? intval(request()->limit) : 10;
        $start_date = request()->start_date ? date("Y-m-d",strtotime(request()->start_date)) : null;
        $end_date = request()->end_date ? date("Y-m-d",strtotime(request()->end_date)) : null;
        $type = request()->type;

        if(!$start_date || !$end_date){
            return $this->returnApi(201,'请选择时间段');
        }

        if(strtotime($start_date) > strtotime($end_date)){
            return $this->returnApi(201,'结束时间请大于开始时间');
        }

        if(!$type || !in_array($type,['day','month','year'])){
            return $this->returnApi(201,'type参数传递错误');
        }

        
        $condition[] = ['statistical_methods','=','realTime'];
        $condition[] = ['date','>=',$start_date];
        $condition[] = ['date','<=',$end_date];

        $res = $this->model->where($condition)
        ->selectRaw('date,max(enter) as enter,max(`exit`) as `exit`,max(pass) as pass')
        ->groupBy('date');

        if($type == 'day'){
            $res = $res->paginate($type);
        }else{
            $res = $res->get();
        }


        if($res->isEmpty()){
            return $this->returnApi(203,'暂无数据');
        }

        if($type == 'day'){
            $res_data = $res->toArray();
        }

        if($type == 'month'){
            $month_list = $this->getMonthList($start_date,$end_date);
            $res_data = array();
            foreach ($month_list as $key => $value) {
                $temp = [];
                $temp['date'] = $value;
                $temp_enter = 0;
                $temp_exit = 0;
                $temp_pass = 0;
                foreach ($res as $res_key => $res_value) {
                    if($value == date("Y-m",strtotime($res_value['date']))){
                        $temp_enter = $temp_enter + $res_value->enter;
                        $temp_exit = $temp_exit + $res_value->exit;
                        $temp_pass = $temp_pass + $res_value->pass;
                    }
                }
                $temp['enter'] = $temp_enter;
                $temp['exit'] = $temp_exit;
                $temp['pass'] = $temp_pass;
                $res_data[] = $temp;
            }
        }

        if($type == 'year'){
            $year_list = $this->getYearList($start_date,$end_date);
            $res_data = array();
            foreach ($year_list as $key => $value) {
                $temp = [];
                $temp['date'] = $value;
                $temp_enter = 0;
                $temp_exit = 0;
                $temp_pass = 0;
                foreach ($res as $res_key => $res_value) {
                    if($value == date("Y",strtotime($res_value['date']))){
                        $temp_enter = $temp_enter + $res_value->enter;
                        $temp_exit = $temp_exit + $res_value->exit;
                        $temp_pass = $temp_pass + $res_value->pass;
                    }
                }
                $temp['enter'] = $temp_enter;
                $temp['exit'] = $temp_exit;
                $temp['pass'] = $temp_pass;
                $res_data[] = $temp;
            }
        }

        return $this->returnApi(200,'ok',$res_data);
    }

    public function getMonthList($start_date,$end_date){
        $start_date = date("Y-m",strtotime($start_date));
        $end_date = date("Y-m",strtotime($end_date));
        $month_list = array();
        while($start_date <= $end_date){
            $month_list[] = date("Y-m",strtotime($start_date));
            $start_date = date("Y-m",strtotime($start_date . '+1 month'));
        }
        return $month_list;
    }

    public function getYearList($start_date,$end_date){
        $start_date = date("Y",strtotime($start_date));
        $end_date = date("Y",strtotime($end_date));
        $year_list = array();
        while($start_date <= $end_date){
            $year_list[] = date("Y",strtotime($start_date));
            $start_date = date("Y",strtotime($start_date . '+1 year'));
        }

        return $year_list;
    }
}