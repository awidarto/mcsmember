<?php

class Awbimport extends Application
{
    public $ordermap = array(
        'delivery_id'=>'',
        'merchant_trans_id'=>'',
        'fulfillment_code'=>'',
        'logistic_awb'=>'',
        'created'=>''
    );


    public function __construct()
    {
        parent::__construct();
        $this->ag_auth->restrict('admin'); // restrict this controller to admins only
        $this->table_tpl = array(
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="dataTable">'
        );
        $this->table->set_template($this->table_tpl);

        $this->breadcrumb->add_crumb('Home','admin/dashboard');

    }

    public function index()
    {
        $this->breadcrumb->add_crumb('AWB Import','admin/awbimport');

        $page['page_title'] = 'AWB Import';
        $this->ag_auth->view('awbimport/upload',$page); // Load the view
    }

    public function upload()
    {
        $config['upload_path'] = FCPATH.'upload/';
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = '1000';
        $config['max_width']  = '1024';
        $config['max_height']  = '768';

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload())
        {
            $error = array('error' => $this->upload->display_errors());

            print_r($error);
        }
        else
        {
            $this->load->library('xls');

            $data = $this->upload->data();

            $xdata = $this->xls->load($data['full_path'],$data['file_ext']);

            $merchant_id = $this->input->post('merchant_id');
            $merchant_name = $this->input->post('merchant_name');

            $head_index = $this->input->post('header_index');

            $label_index = $this->input->post('label_index');
            $header_index = $this->input->post('header_index');
            $data_index = $this->input->post('data_index');

            $update_status = $this->input->post('update_status');
            //var_dump($xdata);

            //exit();

            $sheetdata = array();

            $sheetdata['merchant_id'] = $merchant_id;
            $sheetdata['merchant_name'] = $merchant_name;
            $sheetdata['update_status'] = $update_status;

            foreach($xdata as $sheet=>$row){

                $headidx = $header_index;
                $dataidx = $data_index;

                $xdata = $row;

                /*
                foreach($xdata['cells'] as $dt){
                    if(in_array('id', $dt)){
                        $head = $dt;
                        break;
                    }
                    $headidx++;
                }
                */

                $label = $row['cells'][$label_index];
                $head = $row['cells'][$header_index];

                //print_r($head);

                //print_r($row);

                $orderdata = array();

                for($i = $dataidx; $i <= $row['numRows'];$i++){
                    $temp = $row['cells'][$i];
                    $line = array();
                    for($j = 0;$j < count($head);$j++){
                        $line[ $head[$j]] = $temp[$j];
                    }

                    $index = random_string('alnum', 5);
                    $orderdata[$index] = $line;


                }


                $orderdata = array('sheetname'=>$sheet,'label'=>$label,'head'=>$head,'data'=>$orderdata);

                $sheet_id = random_string('alnum', 6);
                $sheetdata[$sheet_id] = $orderdata;

            }

            print_r($sheetdata);

            $jsonfile = date('d-m-Y-h-i-s',time());

            file_put_contents(FCPATH.'json/'.$jsonfile.'.json', json_encode($sheetdata));

            redirect('admin/awbimport/preview/'.$merchant_id.'/'.$jsonfile, 'location' );

        }

    }

    public function preview($merchant_id,$jsonfile)
    {
        $json = file_get_contents(FCPATH.'json/'.$jsonfile.'.json');

        //print_r(json_decode($json));

        $json = json_decode($json,true);

        //print_r($json);

        $merchant_id = $json['merchant_id'];
        $merchant_name = $json['merchant_name'];
        $update_status = $json['update_status'];

        unset($json['merchant_id']);
        unset($json['merchant_name']);
        unset($json['update_status']);

        $tables = array();

        $apps = get_apps($merchant_id);

        $app_select = array();



        foreach($json as $sheet_id=>$rows){

            //print $sheet_id.'<br />';

            $this->load->library('table');

            $app_select = array();

            foreach($apps as $app ){
                $app_select[ $sheet_id.'|'.$app['key']] = $app['application_name'];
            }

            $selector = form_dropdown('apps[]',$app_select);

            $heads = array_merge(array('<input type="checkbox" id="select_all">'),$rows['head']);

            $this->table->set_heading(array('data'=>'SHEET : '.$rows['sheetname'].' '.$selector,'colspan'=>100));
            $this->table->set_subheading($heads);

            $cells = array();

            $idx = 0;

            foreach($rows['data'] as $index=>$cell){
                $cells[] = array_merge(array('<input name="entry[]" type="checkbox" class="selector" id="'.$index.'" value="'.$index.'">'),$cell);
                $idx++;
            }

            $tables[$sheet_id] = $this->table->generate($cells);

        }


        $page['tables'] = $tables;

        $page['merchant_id'] = $merchant_id;
        $page['merchant_name'] = $merchant_name;
        $page['update_status'] = $update_status;

        $page['app_select'] = $app_select;

        $page['jsonfile'] = $jsonfile;

        $this->breadcrumb->add_crumb('Import','admin/import');

        $page['page_title'] = 'AWB Import Preview';
        $this->ag_auth->view('awbimport/preview',$page); // Load the view
    }

    public function commit()
    {
        //print_r($this->input->post());

        $jsonfile = $this->input->post('jsonfile');
        $entry = $this->input->post('entry');
        $apps = $this->input->post('apps');
        $update_status = $this->input->post('update_status');

        $app_entry = array();
        foreach($apps as $app){
            $p = explode('|', $app);
            $app_entry[$p[0]] = $p[1];
        }

        $json = file_get_contents(FCPATH.'json/'.$jsonfile.'.json');

        $json = json_decode($json,true);

        //print_r($json);

        $merchant_id = $json['merchant_id'];
        $merchant_name = $json['merchant_name'];
        //$update_status = (isset($json['update_status']))?$json['update_status']:'no_changes';

        unset($json['merchant_id']);
        unset($json['merchant_name']);
        unset($json['update_status']);


        foreach($json as $sheet_id=>$rows){
            //print_r($rows);
            //print_r($entry);
            $app_key = $app_entry[$sheet_id];
            $app_id = get_app_id_from_key($app_key);

            $order = $this->ordermap;
            foreach ($rows['data'] as $key => $line) {
                if(in_array($key, $entry)){
                    print "order line: \r\n";

                    print_r($line);

                    $order['delivery_id'] = $line['delivery_id'];
                    $order['fulfillment_code'] = $line['fulfillment_code'];
                    $order['merchant_trans_id'] = $line['merchant_trans_id'];
                    $order['logistic_awb'] = $line['logistic_awb'];

                    $order['merchant_id'] = $merchant_id;
                    $order['application_id'] = $app_id;
                    $order['application_key'] = $app_key;

                    if($update_status != 'no_changes'){
                        $order['status'] = $update_status;
                    }else{
                        $order['status'] = 'no_changes';
                    }

                    print "order input: \r\n";
                    print_r($order);

                    $trx = json_encode($order);
                    $result = $this->order_save($trx,$app_key);

                    print $result;

                }

            }

        }

        redirect('admin/delivery/incoming', 'location' );

    }

    // worker functions

    public function order_save($indata,$api_key)
    {
        $args = '';

        //$api_key = $this->get('key');
        //$transaction_id = $this->get('trx');

        if(is_null($api_key)){
            $result = json_encode(array('status'=>'ERR:NOKEY','timestamp'=>now()));
            return $result;
        }else{
            $app = $this->get_key_info(trim($api_key));

            if($app == false){
                $result = json_encode(array('status'=>'ERR:INVALIDKEY','timestamp'=>now()));
                return $result;
            }else{
                //$in = $this->input->post('transaction_detail');
                //$in = file_get_contents('php://input');
                $in = $indata;

                //print $in;

                $buyer_id = 1;

                $args = 'p='.$in;

                $in = json_decode($in);

                print "order input to save: \r\n";
                print_r($in);

                $delivery_id = (isset($in->delivery_id) && $in->delivery_id != "")?$in->delivery_id:'';

                $merchant_trans_id = (isset($in->merchant_trans_id) && $in->merchant_trans_id != "")?$in->merchant_trans_id:'';

                $fulfillment_code = (isset($in->fulfillment_code))?$in->fulfillment_code:'';

                $logistic_awb = (isset($in->logistic_awb))?$in->logistic_awb:'';

                if($logistic_awb != ''){

                    if($in->status == 'no_changes'){
                        $up = array('logistic_awb'=>$logistic_awb);
                    }else{
                        $up = array(
                            'logistic_awb'=>$logistic_awb,
                            'status'=>$in->status
                        );
                    }

                    $this->db->where('fulfillment_code',$fulfillment_code);
                    $this->db->or_where('delivery_id',$delivery_id);
                    $this->db->or_where('merchant_trans_id',$merchant_trans_id);
                    $result = $this->db->update($this->config->item('incoming_delivery_table'),$up);

                }else{
                    $result = 0;
                }

            }
        }

        $this->log_access($api_key, __METHOD__ ,$result,$args);
    }

    //private supporting functions

    private function get_key_info($key){
        if(!is_null($key)){
            $this->db->where('key',$key);
            $result = $this->db->get($this->config->item('applications_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    private function get_dev_info($key){
        if(!is_null($key)){
            $this->db->where('key',$key);
            $result = $this->db->get($this->config->item('jayon_devices_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    private function get_dev_info_by_id($identifier){
        if(!is_null($identifier)){
            $this->db->where('identifier',$identifier);
            $result = $this->db->get($this->config->item('jayon_devices_table'));
            if($result->num_rows() > 0){
                $row = $result->row();
                return $row;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    private function check_email($email){
        $em = $this->db->where('email',$email)->get($this->config->item('jayon_members_table'));
        if($em->num_rows() > 0){
            return $em->row_array();
        }else{
            return false;
        }
    }

    private function check_phone($phone, $mobile1, $mobile2){
        $em = $this->db->like('phone',$phone)
                ->or_like('mobile1',$mobile1)
                ->or_like('mobile2',$mobile2)
                ->get($this->config->item('jayon_members_table'));
        if($em->num_rows() > 0){
            return $em->row_array();
        }else{
            return false;
        }
    }


    private function register_buyer($dataset){
        $dataset['group_id'] = 5;

        if($this->db->insert($this->config->item('jayon_members_table'),$dataset)){
            return $this->db->insert_id();
        }else{
            return 0;
        }
    }


    private function save_buyer($ds){

        if(isset($ds['buyer_id']) && $ds['buyer_id'] != '' && $ds['buyer_id'] > 1){
            if($pid = $this->get_parent_buyer($ds['buyer_id'])){
                $bd['is_child_of'] = $pid;
                $this->update_group_count($pid);
            }
        }

        $bd['buyer_name']  =  $ds['buyer_name'];
        $bd['buyerdeliveryzone']  =  $ds['buyerdeliveryzone'];
        $bd['buyerdeliverycity']  =  $ds['buyerdeliverycity'];
        $bd['shipping_address']  =  $ds['shipping_address'];
        $bd['phone']  =  $ds['phone'];
        $bd['mobile1']  =  $ds['mobile1'];
        $bd['mobile2']  =  $ds['mobile2'];
        $bd['recipient_name']  =  $ds['recipient_name'];
        $bd['shipping_zip']  =  $ds['shipping_zip'];
        $bd['email']  =  $ds['email'];
        $bd['delivery_id']  =  $ds['delivery_id'];
        $bd['delivery_cost']  =  $ds['delivery_cost'];
        $bd['cod_cost']  =  $ds['cod_cost'];
        $bd['delivery_type']  =  $ds['delivery_type'];
        $bd['currency']  =  $ds['currency'];
        $bd['total_price']  =  $ds['total_price'];
        $bd['chargeable_amount']  =  $ds['chargeable_amount'];
        $bd['delivery_bearer']  =  $ds['delivery_bearer'];
        $bd['cod_bearer']  =  $ds['cod_bearer'];
        $bd['cod_method']  =  $ds['cod_method'];
        $bd['ccod_method']  =  $ds['ccod_method'];
        $bd['application_id']  =  $ds['application_id'];
        //$bd['buyer_id']  =  $ds['buyer_id'];
        $bd['merchant_id']  =  $ds['merchant_id'];
        $bd['merchant_trans_id']  =  $ds['merchant_trans_id'];
        //$bd['courier_id']  =  $ds['courier_id'];
        //$bd['device_id']  =  $ds['device_id'];
        $bd['directions']  =  $ds['directions'];
        //$bd['dir_lat']  =  $ds['dir_lat'];
        //$bd['dir_lon']  =  $ds['dir_lon'];
        //$bd['delivery_note']  =  $ds['delivery_note'];
        //$bd['latitude']  =  $ds['latitude'];
        //$bd['longitude']  =  $ds['longitude'];
        $bd['created']  =  $ds['created'];

        $bd['cluster_id'] = substr(md5(uniqid(rand(), true)), 0, 20 );

        if($this->db->insert($this->config->item('jayon_buyers_table'),$bd)){
            return $this->db->insert_id();
        }else{
            return 0;
        }
    }

    private function get_parent_buyer($id){
        $this->db->where('id',$id);
        $by = $this->db->get($this->config->item('jayon_buyers_table'));

        if($by->num_rows() > 0){

            $buyer = $by->row_array();
            if($buyer['is_parent'] == 1){
                $pid = $buyer['id'];
            }elseif($buyer['is_child_of'] > 0 && $buyer['is_parent'] == 0){
                $pid = $buyer['is_child_of'];
            }else{
                $pid = false;
            }

            return $pid;

        }else{
            return false;
        }

    }

    private function update_group_count($id){

        $this->db->where('is_child_of',$id);
        $groupcount = $this->db->count_all_results($this->config->item('jayon_buyers_table'));

        $dataup = array('group_count'=>($groupcount + 1) );

        $this->db->where('id',$id);

        if($res = $this->db->update($this->config->item('jayon_buyers_table'),$dataup) ){
            return $res;
        }else{
            return false;
        }

    }

    private function get_device($key){
        $dev = $this->db->where('key',$key)->get($this->config->item('jayon_mobile_table'));
        print_r($dev);
        print $this->db->last_query();
        return $dev->row_array();
    }

    private function get_group(){
        $this->db->select('id,description');
        $result = $this->db->get($this->ag_auth->config['auth_group_table']);
        foreach($result->result_array() as $row){
            $res[$row['id']] = $row['description'];
        }
        return $res;
    }

    private function log_access($api_key,$query,$result,$args = null){
        $data['timestamp'] = date('Y-m-d H:i:s',time());
        $data['accessor_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['api_key'] = (is_null($api_key))?'':$api_key;
        $data['query'] = $query;
        $data['result'] = $result;
        $data['args'] = (is_null($args))?'':$args;

        access_log($data);
    }

    private function admin_auth($username = null,$password = null){
        if(is_null($username) || is_null($password)){
            return false;
        }

        $password = $this->ag_auth->salt($password);
        $result = $this->db->where('username',$username)->where('password',$password)->get($this->ag_auth->config['auth_user_table']);

        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }


}

?>