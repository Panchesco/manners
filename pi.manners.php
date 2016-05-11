<?php

/**
 * Manners
 *
 * @package		Manners
 * @author		Richard Whitmer
 * @copyright		Copyright (c) 2016, Richard Whitmer
 * @link 			https://github.com/panchesco/manners
 * @license 		MIT
 * @version 		1.0.0
 * @filesource		manners/pi.manners.php
 */

 
	class Manners {
		
		var $short_names	= array();
		
		function __construct(){}
		
		//-----------------------------------------------------------------------------
		
		/**
		 * Return srcset string for an image file.
		 * @access public
		 * @return string
		 */
		 public function srcset()
		 {
			 $srcset 			= '';
			 $file_id			= ee()->TMPL->fetch_param('file_id');
			 $this->short_names = explode("|",ee()->TMPL->fetch_param('short_names'));
			 
			 // Break lines in returned srcset?
			 $break_lines	= ee()->TMPL->fetch_param('break_lines','y');
			 $break_lines	= str_ireplace(array('yes','y','on','true'),'y',$break_lines);
			 
			$rows = $this->srcset_array($file_id);
			
			if($break_lines=='y')
			{
				$srcset = implode(", \n",$rows);
			} else {
				$srcset = implode(", ",$rows);
			}

			 return $srcset;
		 }
		  
		//-----------------------------------------------------------------------------
		  
		/**
		 * Return array of srcset strings for a file.
		 * @param $file_id integer
		 * @return array
		 *
		*/
		private function srcset_array($file_id) 
		{
			$data = array();
			
			$rows = $this->file_manipulations($file_id);
			
			foreach($rows as $key => $row)
			{
				$data[] = $row->url . ' ' . $row->width . 'w';
			}
			
			return $data;
		}
				
		//-----------------------------------------------------------------------------

		  /** 
		   * Return an array of image manipulations for a file.
		   * @param $file_id integer
		   * @return array
		   */
		  private function file_manipulations($file_id)
		  {
			  $data = array();

			  $sel[]	= 'files.file_id';
			  $sel[]	= 'files.file_name';
			  $sel[]	= 'files.upload_location_id';
			  $sel[]	= 'upload_prefs.url';
			  $sel[]	= 'upload_prefs.server_path';
			  $sel[]	= 'file_dimensions.short_name';
			  $sel[]	= 'file_dimensions.width';
			  
			 ee()->db->select($sel); 
			 ee()->db->join('upload_prefs','upload_prefs.id=files.upload_location_id','left');
			 ee()->db->join('file_dimensions','file_dimensions.upload_location_id=files.upload_location_id','left');
			 ee()->db->where('files.file_id',$file_id);
			 ee()->db->where_in('file_dimensions.short_name',$this->short_names);
			 ee()->db->order_by('file_dimensions.width','asc');
			 $query = ee()->db->get('files');
			 
			 $rows = $query->result();
			 
			 // Set each manipulations url.
			 
			 foreach($rows as $key => $row)
			 {
				$rows[$key]->url = $row->url . '_' . $row->short_name . '/' . $row->file_name;
				$rows[$key]->server_path = $row->server_path . '_' . $row->short_name . '/' . $row->file_name;
				$rows[$key]->width	= '';
				$rows[$key]->height	= '';
				
				// Check that the file actually exists and set the width and height values from it.
				
				if(file_exists($rows[$key]->server_path))
				{
					list($rows[$key]->width,$rows[$key]->height) = getimagesize($rows[$key]->server_path);
				} else {
					
				// If it doesn't exist, remove it from the array.
					unset($rows[$key]);
				}
				
			 }
			 
			 return $rows;
		  }
		  
		 //-----------------------------------------------------------------------------

	}