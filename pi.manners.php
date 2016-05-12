<?php

/**
 * Manners
 *
 * @package		Manners
 * @author		Richard Whitmer
 * @copyright		Copyright (c) 2016, Richard Whitmer
 * @link 			https://github.com/panchesco/manners
 * @license 		MIT
 * @version 		1.1.0
 * @filesource		manners/pi.manners.php
 */

 
	class Manners {
		
		var $short_names	= array();
		
		function __construct(){
			
			$this->short_names = explode("|",ee()->TMPL->fetch_param('short_names'));
			 
			 // Break lines in returned srcset?
			 $break_lines	= ee()->TMPL->fetch_param('break_lines','y');
			 $this->break_lines	= str_ireplace(array('yes','y','on','true'),'y',$break_lines);
		}
		
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

			$rows = $this->srcset_array($file_id);
			
			if($this->break_lines=='y')
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
		 
			/**
			 * 
			 *
			*/
			public function srcset_wrap() 
			{
				
				$directory_name	= ee()->TMPL->fetch_param('directory_name');
				$directory_id		= ee()->TMPL->fetch_param('directory_id');
				
				$str = '';
				$img_lines = array();
				
				$tagdata = str_replace("\r","\n",ee()->TMPL->tagdata);
				
				// Get the upload directory info.
				
				if($directory_id)
				{
					ee()->db->where('id',$directory_id);
					
				} elseif($directory_name){
					
					ee()->db->where('name',$directory_name);
					
				} else {
					
					return $tagdata;
				}
				
				ee()->db->limit(1);
				$query = ee()->db->get('upload_prefs');

				// If no directory found, return the tagdata and be done with it.
				
				if($query->num_rows()==0)
				{
					return $tagdata;
					
					} else {
					
					$dir = $query->row(); 
	
				}
				
				
				// Convert tagdata to array
				$lines = explode("\n",$tagdata);
				
				
				// Loop through array and create new array of lines with img tags.
				foreach($lines as $key => $string)
				{
					$img = strpos($string, $dir->url);
					
					if($img>0)
					{
						$img_lines[] = $key;	
					}
				}
				
				// Now loop through the lines with images.
				foreach($img_lines as $key)
				{
					// Get the src value.
					
					$src = $lines[$key];
					
					
					$match = preg_match("/\/[[:alnum:]-_]+\.(png|jpe?g){1}/i", $lines[$key],$result);
					
					if(isset($result[0]))
					{
						$file_name = trim($result[0],'/');
						
						// We have the directory_id and filename.
						// We can get the file_id.
						
						$file_id = $this->file_id($dir->id,$file_name);
						
						
						// Now that we have the file_id, we can get the srcset string 
						
						if($file_id)
						{
							$srcset = $this->srcset_array($file_id);
							
							if($this->break_lines=='y')
							{
								$srcset = implode(", \n",$srcset);
							} else {
								$srcset = implode(", ",$srcset);
							}
							
							// Now add the srcset string to the img tag.
							$lines[$key] = str_replace('<img ','<img srcset="' . $srcset . '" ',$lines[$key]);

						}
	
					}
					
				}
				
				// Now loop through the lines and rebuild the tagdata.
				
				foreach($lines as $row)
				{
					$str.= $row . "\n";
				}
				
				return $str;	
			}
				
			//-----------------------------------------------------------------------------
			
			
			/**
			 * Get the file id.
			 * @param $directory_id integer
			 * @param $file_name integer
			 * @return mixed integer/bool
			*/
			private function file_id($directory_id,$file_name) 
			{
					ee()->db->select('file_id');
					ee()->db->where('upload_location_id',$directory_id);
					ee()->db->where('file_name',$file_name);
					ee()->db->limit(1);
					$query = ee()->db->get('files');
					
					if($query->num_rows()==0)
					{
						return FALSE;
					} else {
						return $query->row()->file_id;
					}
				
			}
				
			//-----------------------------------------------------------------------------

	}