<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class device_model extends CI_Model {
	

	public function __construct()
		{
			
		}
		
	//----------------------------------------------------------	
		
	public function all_device($num,$offset) // Вывод оборудования
		{
			$count=$this->db->count_all('device_all'); //считаем оборудование
			$result['result_count']=$count;
			if ($count!=0) // если есть то...
				{
					$que=$this->db->get('device_all',$num,$offset); // получаем
					$result['device']=$que->result_array(); // записываем в переменную 
					$result['error']=0; // ошибок нет
				} else $result['error']=1;
			return $result; 
		}
		
	public function save_device($array)
		{
			$error=0; $k=0; $text='';
			if(empty($array['name'])) {$error++; $errorinfo[$error]='Не указано НАЗВАНИЕ оборудования!';}
			if(empty($array['price'])) {$error++; $errorinfo[$error]='Не указана ЦЕНА оборудования!';}
			if($array['category']==0) {$error++; $errorinfo[$error]='Не выбрана КАТЕГОРИЯ оборудования!';}
			switch($array['optionsRadios'])
				{
					case '1': {if((empty($array['inv_start'])) OR (empty($array['inv_finish']))) {$error++; $errorinfo[$error]='Не правильно указан диапозон ИНВ.номеров';} break;} 
					case '0': {if(empty($array['inv_count'])) {$error++; $errorinfo[$error]='Не указано колличество оборудования!';} break;}
					default: {$error++; $errorinfo[$error]='Неверный запрос! Обратитесь к разработчику!';}
				}
			$this->load->model('send_model');
			if($error==0)
				{
					$array['inv_start']= str_replace(' ','',$array['inv_start']);
					$array['inv_finish']= str_replace(' ','',$array['inv_finish']);
					switch($array['optionsRadios'])
						{
							case '1': $data=$this->oprionsradios($array); break;
							case '0': $data=$this->oprionsradios($array,true); break;
						}
					
				} else
				{
					$data['error']['status']=4;
					$data['error']['text']='Ошибка! Некоторые поля не верно заполнены или вообще не заполнены: <ol>';
					foreach($errorinfo as $info)
						{
							$data['error']['text'].='<li>'.$info.'</li>';
						}
					$data['error']['text'].='</ol>';	
				}
					$result=$this->send_model->arlet($data);
		return $result;
		}
		
	function oprionsradios($array,$in=false)
		{
		$k=0; $text='';
			if(!empty($in))
				{
					$x=0;
					while($x++<$array['inv_count'])
						{
							$k++;
							$dbinsert=array(
							'id'=>0,
							'contract'=>'0',
							'category'=>$array['category'],
							'name'=>$array['name'],
							'inv'=>'-',
							'ser'=>'-',
							'price'=>$array['price'],
							'work'=>1,
							'education_id'=>0,
							'location'=>'ЦЛПДО'
							);
							$this->db->insert('device_all',$dbinsert);
						}
					$data['error']['status']=1;
					$data['error']['text']='Будет создано '.$k.' оборудования.';
				}
				else
				{	
					$startinv=$array['inv_start'];
					while($startinv<=$array['inv_finish'])
						{
							$k++;
							$dbinsert=array(
							'id'=>0,
							'contract'=>'0',
							'category'=>$array['category'],
							'name'=>$array['name'],
							'inv'=>$startinv,
							'ser'=>'-',
							'price'=>$array['price'],
							'work'=>1,
							'education_id'=>0,
							'location'=>'ЦЛПДО'
							);
							$this->db->insert('device_all',$dbinsert);
							$startinv++;
						}
					$data['error']['status']=1;
					$data['error']['text']='Будет создано '.$k.' оборудования. C инв.номерами начиная с '.$array['inv_start'].' заканчивая '.$array['inv_finish'];
				}
				
			return $data;
		}
}
