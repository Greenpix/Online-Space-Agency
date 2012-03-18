<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of c_personnel
 *
 * @author greenpix
 */
class c_personnel extends CI_Controller {
    
   public function __construct(){
   parent::__construct();
        if($this->session->userdata('is_connect')){
            
            $this->load->model('m_user');
            $user_id = $this->session->userdata('id');
            $resources['resource'] = $this->m_user->getResources($user_id);
            $data['topbar'] = $this->load->view('template/topbar/user_interface_topbar', $resources, TRUE);
        }
        else{
            redirect('c_main/index');
        }
    }
    
    function index(){
        $data['header'] = $this->load->view('template/header/user_interface_header', '', TRUE);
        $data['content'] = $this->load->view('template/content/personnel_index_content', '', TRUE);
        $data['footer'] = $this->load->view('template/footer/user_interface_footer', '', TRUE);

        $this->load->view('layout',$data);
    }
   
    function createPersonnel($personnel_type, $rep){
        
        $this->load->model('m_personnel');
        $this->load->library('getPersonnelInfo');
        
        //Le nom du personnel
        $name = $this->getpersonnelinfo->getName();
        //Le type du personnel
        $personnel = $this->getpersonnelinfo->getPersonnelType($personnel_type);
        //La réputation du personnel
        $reputation = $this->getpersonnelinfo->getReputation($rep);
        //Les skils du personnel
        $skill = $this->getpersonnelinfo->getSkill($reputation);       
        //Le status du personnel
        $status = 0;       
        //Le propriétaire du personnel
        $owner = 0;       
        //Salaire du personnel
        $salaire = $this->getpersonnelinfo->getSalaire($skill['skill1'], $skill['skill2'],$skill['skill3']);      
        //Valeur du personnel
        $valeur = $this->getpersonnelinfo->getValeur($personnel, $salaire);
        
        //Ajout des informations dans la base de donnée
        $data = array(
           'name_personnel' => $name ,
           'id_type_personnel' => $personnel ,
           'reputation_personnel' => $reputation,
           'skill1_personnel' => $skill['skill1'] ,
           'skill2_personnel' => $skill['skill2'] ,
           'skill3_personnel' => $skill['skill3'] ,
           'status_personnel' => $status ,
           'owner_personnel' => $owner ,
           'salaire_personnel' => $salaire ,
           'valeur_personnel' => $valeur
        );
        $this->m_personnel->addPersonnel($data);
    }
    
    function listRecruitable(){
        
        $this->load->model('m_personnel');
        $list = $this->m_personnel->getList(0,'all');
        $table['types'] = $this->m_personnel->getType();
        
        $table['spationautes'] = array();
        $table['pilotes'] = array();
        $table['scientifiques'] = array();
        $table['horslalois'] = array();
        $table['securites'] = array();
        
        foreach($list as $personnel){
            if($personnel->id_type_personnel == 1){
                $table['spationautes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 2){
                $table['pilotes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 3){
                $table['scientifiques'][] = $personnel;
            }
            if($personnel->id_type_personnel == 4){
                $table['horslalois'][] = $personnel;
            }
            if($personnel->id_type_personnel == 5){
                $table['securites'][] = $personnel;
            }
        }
        
        $data['header'] = $this->load->view('template/header/user_interface_header', '', TRUE);
        $data['content'] = $this->load->view('template/content/personnel_listRecruitable_content', $table, TRUE);
        $data['footer'] = $this->load->view('template/footer/user_interface_footer', '', TRUE);
        $data['script'] = $this->load->view('template/script/personnel_script', '', TRUE);

        $this->load->view('layout',$data);
        
    }
    
    function listHave(){
        
        $user_id = $this->session->userdata('id');
        
        $this->load->model('m_personnel');
        $list = $this->m_personnel->getList(1,$user_id);
        $table['types'] = $this->m_personnel->getType();
        
        $table['spationautes'] = array();
        $table['pilotes'] = array();
        $table['scientifiques'] = array();
        $table['horslalois'] = array();
        $table['securites'] = array();
        
        
        foreach($list as $personnel){
            if($personnel->id_type_personnel == 1){
                $table['spationautes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 2){
                $table['pilotes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 3){
                $table['scientifiques'][] = $personnel;
            }
            if($personnel->id_type_personnel == 4){
                $table['horslalois'][] = $personnel;
            }
            if($personnel->id_type_personnel == 5){
                $table['securites'][] = $personnel;
            }
        }
        
        $data['header'] = $this->load->view('template/header/user_interface_header', '', TRUE);
        $data['content'] = $this->load->view('template/content/personnel_listHave_content', $table, TRUE);
        $data['footer'] = $this->load->view('template/footer/user_interface_footer', '', TRUE);
        $data['script'] = $this->load->view('template/script/personnel_script', '', TRUE);
        
        $this->load->view('layout',$data);
        
    }
   
    function recruit(){
        $user_id = $this->session->userdata('id');
        $id = $this->uri->segment(3);
        
        $this->load->model('m_user');
        $resources = $this->m_user->getResources($user_id);
        
        $this->load->model('m_personnel');
        $personnel = $this->m_personnel->getOnce($id);
        
        if($resources->argent >= $personnel->valeur_personnel){
            
            $argent = $resources->argent - $personnel->valeur_personnel;
            
            $this->m_user->updateResource($user_id, 'argent', $argent);
            $this->m_personnel->updateStatus($id, 1);
            $this->m_personnel->updateOwner($id, $user_id);
            
            $status = 'success';
        }
        else{
            $status = 'error';
        }
        
        $data = array('status'=>$status);
        echo json_encode($data);
        
    }
    
    function kick(){
        
        $user_id = $this->session->userdata('id');
        $this->load->model('m_user');
        $resources = $this->m_user->getResources($user_id);
        
        $id = $this->uri->segment(3);
        $this->load->model('m_personnel');
        $personnel = $this->m_personnel->getOnce($id);
        
        $argent = $resources->argent + ((($personnel->valeur_personnel)/100)*60);
            
        $this->m_user->updateResource($user_id, 'argent', $argent);
        $this->m_personnel->updateStatus($id, 0);
        $this->m_personnel->updateOwner($id, 0);
        
        redirect('c_personnel/listHave');
    }
    
    function auction(){
        $user_id = $this->session->userdata('id');
        $id = $this->uri->segment(3);
        
        $this->load->model('m_personnel');
        $info['personnel'] = $this->m_personnel->getOnce($id);
        
        $this->load->helper('date');
        
        $data['header'] = $this->load->view('template/header/user_interface_header', '', TRUE);
        $data['content'] = $this->load->view('template/content/personnel_auction_content', $info, TRUE);
        $data['footer'] = $this->load->view('template/footer/user_interface_footer', '', TRUE);
        $data['script'] = $this->load->view('template/script/personnel_script', '', TRUE);
        
        $this->load->view('layout',$data);
    }
    
    function auction_start(){
        
        $this->load->helper('date');
        $this->load->model('m_personnel');
        
        $id_seller = $this->session->userdata('id');
        $id_personnel = $this->input->post('id_personnel');
        $time_start = now();
        $time_end = human_to_unix($this->input->post('time_end'));
        $price_start = $this->input->post('price_start');
        
        $data = array(
           'id_seller_auction_personnel' => $id_seller ,
           'id_personnel' => $id_personnel ,
           'time_start_auction_personnel' => $time_start,
           'time_end_auction_personnel' => $time_end,
           'price_start_auction_personnel' => $price_start,
           'price_current_auction_personnel' => $price_start,
           'id_buyer_auction_personnel' => $id_seller
        );
        
        $this->m_personnel->addAuction($data); 
        $this->m_personnel->updateStatus($id_personnel, 2);
        
        redirect('c_personnel/index');
    }
    
    function listAuction(){        
        
        $user_id = $this->session->userdata('id');
        
        $this->load->model('m_personnel');
        $list = $this->m_personnel->getAuctionList($this->session->userdata('id'));
        $table['types'] = $this->m_personnel->getType();
        
        $table['spationautes'] = array();
        $table['pilotes'] = array();
        $table['scientifiques'] = array();
        $table['horslalois'] = array();
        $table['securites'] = array();
        
        
        foreach($list as $personnel){
            if($personnel->id_type_personnel == 1){
                $table['spationautes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 2){
                $table['pilotes'][] = $personnel;
            }
            if($personnel->id_type_personnel == 3){
                $table['scientifiques'][] = $personnel;
            }
            if($personnel->id_type_personnel == 4){
                $table['horslalois'][] = $personnel;
            }
            if($personnel->id_type_personnel == 5){
                $table['securites'][] = $personnel;
            }
        }
        
        $data['header'] = $this->load->view('template/header/user_interface_header', '', TRUE);
        $data['content'] = $this->load->view('template/content/personnel_listAuction_content', $table, TRUE);
        $data['footer'] = $this->load->view('template/footer/user_interface_footer', '', TRUE);
        $data['script'] = $this->load->view('template/script/personnel_script', '', TRUE);
        
        $this->load->view('layout',$data);
        
    }
    
    function bid(){
        
        $this->load->model('m_personnel');
        
        $id_buyer = $this->session->userdata('id');
        $price_current = $this->input->post('price_current_auction_personnel');
        $id_personnel = $this->input->post('id_personnel');
        
        $this->load->model('m_user');
        $resources = $this->m_user->getResources($id_buyer);   
        
        $personnel_auction = $this->m_personnel->getAuction($id_personnel);
        
        if(($personnel_auction->price_current_auction_personnel +1000)<=$price_current){
            
            if($resources->argent >= $price_current){

                $resources_old = $this->m_user->getResources($personnel_auction->id_buyer_auction_personnel); 
                
                $argent = ($resources->argent) - $price_current;
                $argent_old = $resources_old->argent + $personnel_auction->price_current_auction_personnel;
                
                $this->m_user->updateResource($personnel_auction->id_buyer_auction_personnel, 'argent', $argent_old);
                $this->m_user->updateResource($id_buyer, 'argent', $argent);
                $this->m_personnel->updateAuction($personnel_auction->id_auction_personnel, $id_buyer, $price_current);

                //$status = 'success';
            }
            else{
                //$status = 'error_argent';
            
            }
        }
        else{
            //$status = 'error_price';
        }
        
        //$data = array('status'=>$status);
        
        //echo json_encode($data);
        redirect('c_personnel/index');
    }
}

?>