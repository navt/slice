<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parser_model extends CI_Model
{
	// INSERT
	public function i_ad($fields=array())
	{
        $q = "INSERT INTO `{$this->db->dbprefix('digest')}`
	        (ad_date,ad_hash,src_id,src_link,ad_title,ad_text)
	        VALUES ({$fields[1]},{$fields[2]},
	        	{$fields[3]},{$fields[4]},{$fields[5]},{$fields[6]})";
        $reply = $this->db->query($q);
        return $reply;
	}
	// UPDATE
	public function u_mi($fields=array())
	{
		$q  = "UPDATE `{$this->db->dbprefix('meteoinfo')}`
			SET `mi_date` = {$fields[1]},
				`mi_title` = {$fields[2]},
				`mi_description` = {$fields[3]}
            WHERE `mi_id` = {$fields[0]}";
        $reply = $this->db->query($q);
        return $reply;
	}
	// добавление поля ad_hash в запись по ad_id
	public function upd_record($ad_id, $ad_hash)
	{
		$q = "UPDATE `{$this->db->dbprefix('digest')}`
			SET `ad_hash` = {$ad_hash}
			WHERE `ad_id` = {$ad_id}";
		$rep = $this->db->query($q);
		return $rep;
	}

	// SELECT

	// выборка левой(правой) границы временного интервала для новостей
	public function border($side='right')
	{
		$limit = '1';
		switch ($side) {
			case 'right':
				$direction = 'DESC';
				break;
			case 'left':
				$direction = 'ASC';
				$limit = '8,1';
				break;
			default:
				return false;
				break;
		}
		$q = "SELECT `ad_id`,`ad_date`
				FROM `{$this->db->dbprefix('digest')}`
				ORDER BY `ad_id` {$direction} LIMIT {$limit}";
		$query = $this->db->query($q);
        if ($query->num_rows() > 0){
        	$row = $query->row_array();
        } else $row = false;
        return $row;
	}
	// выборка qty новостей с заданным idPub на момент viewedTime и ранее
	public function find_ads($viewedTime='',$idPub=1,$qty=3)
	{
		$q = "SELECT `ad_id`,`ad_date`,`src_link`,`src_id`,`ad_title`,`ad_text`
			FROM `{$this->db->dbprefix('digest')}`
			WHERE `src_id` = {$idPub} AND `ad_date` <= '{$viewedTime}'
			ORDER BY `ad_date` DESC
			LIMIT {$qty}";
		$query = $this->db->query($q);
		if ($query->num_rows() > 0){
			$queryRes = array();
			$queryRes = $query->result_array();
		} else $queryRes = false;
		return $queryRes;
	}
	// выборка метео информации
	public function mi()
	{
		$q = "SELECT `mi_title`,`mi_description`
			FROM `{$this->db->dbprefix('meteoinfo')}`
			LIMIT 2";
		$query = $this->db->query($q);
		if ($query->num_rows() > 0){
			$queryRes = array();
			$queryRes = $query->result_array();
		} else $queryRes = false;
		return $queryRes;
	}
	// выборка времени для предидущего блока записей
	public function until($tView='')
	{
		$q = "SELECT `ad_id`,`ad_date`
			FROM `{$this->db->dbprefix('digest')}`
			WHERE `ad_date` < '{$tView}'
			ORDER BY `ad_date` DESC
			LIMIT 1";
		$query = $this->db->query($q);
        if ($query->num_rows() > 0){
        	$row = $query->row_array();
        } else $row = false;
        return $row;
	}
	// выборка времени для следущего блока записей
	public function later($tView='')
	{
		$q = "SELECT `ad_id`,`ad_date`
			FROM `{$this->db->dbprefix('digest')}`
			WHERE `ad_date` > '{$tView}'
			ORDER BY `ad_date` ASC
			LIMIT 1";
		$query = $this->db->query($q);
        if ($query->num_rows() > 0){
        	$row = $query->row_array();
        } else $row = false;
        return $row;
	}
	// выборка всех записей (поля: src_link, ad_title )
	public function all_for_hash()
	 {
		$q = "SELECT `ad_id`,`src_link`,`ad_title` FROM `{$this->db->dbprefix('digest')}`";
		$query = $this->db->query($q);
		if ($query->num_rows() > 0){
			$rows = array();
			$rows = $query->result_array();
		} else $rows = false;
		return $rows;
	 }
	 // есть ли запись в БД с таким ad_hash?
	 public function find_hash($hash='')
	 {
	 	$hash = '\''.$hash. '\'';
	 	$row = array();
	 	$q = "SELECT `ad_id`
		 	FROM `{$this->db->dbprefix('digest')}`
		 	WHERE `ad_hash` = {$hash}
		 	LIMIT 1";
		$query = $this->db->query($q);
		// если запись есть, возвращаем ad_id, нет - false
		if ($query->num_rows() > 0){
        	$row = $query->row_array();
        	$replay = (int)$row['ad_id'];
        } else $replay = false;
        return $replay;
	 }

	 // DELETE
	 public function delete($fromMoment='2010-12-12 00:00:00')
	 {
	 	$q = "DELETE FROM `{$this->db->dbprefix('digest')}`
		 	WHERE `ad_date` < '{$fromMoment}'";
		$query = $this->db->query($q);
		return $query;
	 }
}