<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<style>
td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}
body{background-color:#c1c1be;}
.container{background-color:white;margin:auto;margin-top:2%;width:80%;border:1px solid white;border-radius:10px;}
.content-container{margin:auto;margin-top:2%;margin-bottom:2%;}
</style>
<?php
$manifest = array (
  'acceptable_sugar_flavors' => 
   array (
    0 => 'CE',
    1 => 'PRO',
    2 => 'ENT',
    3 => 'ULT',
  ),
  'acceptable_sugar_versions' => 
  array (
    'exact_matches' => 
    array (
    ),
    'regex_matches' => 
    array (
      0 => '(.*?)\\.(.*?)\\.(.*?)$',
    ),
  ),
  'readme' => '',
  'key' => '',
  'author' => 'OutrightCRM',
  'description' => 'Installs Outright Hook Extension , Outright Logick Hook, Outright Field Generator',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => '',
  'published_date' => date('Y-m-d h:i:s'),
  'type' => 'module',
  'version' => '',
  'remove_tables' => 'prompt',
);

$current_dir = getcwd();
$new_manifest = $manifest;
$manifest_exist = file_exists('manifest.php') ? true : false;
if($manifest_exist){
		include 'manifest.php';
		$manifest['published_date'] = date('Y-m-d h:i:s');
		$new_manifest = $manifest;		
	}
	
echo "<div class='container' ><form method='POST' ><div class='content-container' ><table align='center' width='80%'>";
echo "<tr><th colspan=4 ><center><h3>Outright Genrate Package.</h3></center></th></tr>";
echo "<tr><td>Enter Package Name : </td><td><input type='text' name='package_name' value=$new_manifest[name] ></td><td>Enter Package Version : </td><td><input type='text' name='package_version' value=$new_manifest[version] ></td></tr>";		
echo "<tr><td>Enter Bean Name : </td><td><textarea name='bean_name' rows='4' cols='50'></textarea></td><td></td><td></td></tr>";		
echo "<tr><td colspan='4'><center><input type='submit' name='gen_package' value='Genrate Package' class='btn btn-warning'></center></td></tr>";
echo "</table></div></form></div>";

if(isset($_POST['gen_package'])){	
		global $new_installdefs,$skipArr;
		$new_installdefs = array();
		$new_installdefs = array('id' => '' , 'beans' => array() , 'image_dir' => '' , 'copy' => array() , 'language' => array() , 'relationships' => array() , 'connectors' => array()  , 'dashlets' => array() , 'layoutfields' => array() , 'layoutdefs' => array() , 'vardefs' => array()  , 'custom_fields' => array() , 'logic_hooks' => array() , 'pre_execute' => array() ,  'pre_uninstall' => array() , 'post_uninstall' => array() );
		$skipArr = array('LICENSE.txt','manifest.php','outright_create_package.php');		
		$idArr = explode(' ',$_POST['package_name']);
		$new_installdefs['id'] = $idArr[0].'_'.rand();	
		if(strpos($_POST['package_name'],'utright')){
				$new_manifest['name'] = $_POST['package_name'];
				$new_manifest['key'] = $_POST['package_name'];
			}	
		else{
				$new_manifest['name'] = 'Outright_'.$_POST['package_name'];
				$new_manifest['key'] = 'Outright_'.$_POST['package_name'];
			}
		$packageName = $new_manifest['name'];
		$new_manifest['version'] = $_POST['package_version'];
		$bean_names = explode(',',$_POST['bean_name']);
		if($_POST['bean_name']){
			     foreach($bean_names as $key=>$value)
			        {
						$new_installdefs['beans'][] = array(
							  'module' => $value,
							  'class' => $value,
							  'path' => 'modules/'.$value.'/'.$value.'.php',
							  'tab' => true,
						);
			        }
		  }
		outright_collect_all_file($packageName);
		if($new_installdefs['image_dir']=='')
		{
		   unset($new_installdefs['image_dir']);
	    }
		file_put_contents('manifest.php',"<?php\n".'$manifest = '.var_export($new_manifest,true).";\n".'$installdefs = '.var_export($new_installdefs,true).';');
		chmod('manifest.php',0777);
		outright_create_zip_with_folder($packageName);
	}

function outright_collect_all_file($packageName){
		global $new_installdefs,$skipArr;		
		$dirArr = glob("*");
		if(!is_dir('scripts'))
		{
		  mkdir('scripts',0777,true);	
		}
		$rel_table_name_array= array();
		if(count(scandir('custom/metadata'))>2)
		{
		   foreach(glob('custom/metadata/*') as $files)
		   {
			   $my_con = file($files);
			   foreach($my_con as $rel_value)
			   {
				  if(strpos($rel_value,'join_table') == true)
				  {
					$rel_value = str_replace(array("'",","),"",$rel_value);
					$rel_table_name = end(explode('=>',$rel_value));
					array_push($rel_table_name_array,$rel_table_name);
				  }   
			   }
		   }
		   $rel_table_name_array = array_filter($rel_table_name_array);
		   if(!empty($rel_table_name_array)){
					   $outright_rel_names = trim(implode(',',$rel_table_name_array));
					   $out_rel_value ='';
					  $out_rel_value .='global $db;'."\n".'$sql = "DROP TABLE '.$outright_rel_names.'";'."\n".'$db->query($sql);'."\n";
					  $out_rel_value_txt  .= "<?php\n function pre_uninstall(){"."\n".$out_rel_value."}\n";
					  $myfile3 = fopen('scripts/pre_uninstall.php',"w") or die("Unable to open file!");
				      fwrite($myfile3, $out_rel_value_txt);
			      }  
		}
		if(!file_exists('scripts/pre_uninstall.php'))
		{
			$content = '';
		    $content .='<?php'."\n";
		    $content .= 'function pre_uninstall(){'."\n";
		    $content .= '}'."\n";
		    $fi = fopen('scripts/pre_uninstall.php',"w");
			fwrite($fi,$content);
		
		}
		if(!file_exists('scripts/post_uninstall.php'))
		{
			$content = '';
		    $content .='<?php'."\n";
		    $content .= 'function post_uninstall(){'."\n";
		    $content .= '}'."\n";
		    $fi = fopen('scripts/post_uninstall.php',"w");
			fwrite($fi,$content);
		
		}
		$content = '';
		$content .='<?php'."\n";
		$content .= 'function post_install(){'."\n";
		$content  .= 'outright_post_install_msg("'.$packageName.'");'."\n";
		$content .= '}'."\n";
		if(!file_exists('scripts/post_install.php'))
		{
			$fi = fopen('scripts/post_install.php',"w");
			fwrite($fi,$content);
		}
		$content1 = '';
		$content1 .='<?php'."\n";
		$content1 .= 'function pre_install(){'."\n";
		$content1 .= '$final_file="outright_utils/final/final.php";'."\n".'if(!file_exists($final_file))'."\n".'{'."\n".'$msg ="<h1> Oopps!!! , <br/>Seems Core package was not installed. However nothing to worry , download it from <a href=https://store.outrightcrm.com/outright_utils.zip>here</a> , then install it first , before installing this package</h1>";'."\n".'echo $msg;'."\n".'exit();'."\n".'};'."\n";
		$content1 .= '}'."\n";
		if(!file_exists('scripts/pre_install.php'))
		{
			$fi = fopen('scripts/pre_install.php',"w");
			fwrite($fi,$content1);
		}
		foreach($dirArr as $key => $dirFile){
				if(is_file($dirFile)){
						if(!in_array($dirFile,$skipArr) ){
								$new_installdefs['copy'][] = array(
									'from' => '<basepath>/'.$dirFile,
									'to' => $dirFile,
								);
							}
					}
			 if(is_dir($dirFile) && $dirFile == 'SugarModules'){
								outright_collect_subDir_file($dirFile);
							}
				if(is_dir($dirFile) && $dirFile != 'icons' && $dirFile != 'language' && $dirFile != 'scripts' && $dirFile != 'SugarModules'){
						$new_installdefs['copy'][] = array(
									'from' => '<basepath>/'.$dirFile,
									'to' => $dirFile,
								);
					}
				else if(is_dir($dirFile) && $dirFile == 'icons'){
						$new_installdefs['image_dir'] = '<basepath>/icons';
					}
				else if(is_dir($dirFile) && $dirFile == 'language'){
						$new_installdefs['language'][] = array (
							  'from' => '<basepath>/'.$dirFile,
							  'to_module' => 'application',
							  'language' => 'en_us',
							);
					}
			}
	}
	
function outright_collect_subDir_file($path){
				global $new_installdefs,$skipArr;
				$dirArr = glob($path."/*");
				foreach($dirArr as $key => $dirFile){
						if(is_file($dirFile) && strpos($dirFile,'ugarModules/relationships/relationships/')){
								$new_installdefs['relationships'][] = array (
									  'meta_data' => '<basepath>/'.$dirFile,
									);
							}
						 if(is_file($dirFile) && strpos($dirFile,'ugarModules/relationships/layoutdefs/')){	
							  ob_start();						 
							  include $dirFile;
							$mod  = array_keys($layout_defs)[0];
							ob_end_clean(); 
							$new_installdefs['layoutdefs'][] = array (
									   'from' => '<basepath>/'.$dirFile,
                                       'to_module' => $mod,
									);
									unset($layout_defs);
							}
							if(is_file($dirFile) && strpos($dirFile,'ugarModules/relationships/vardefs/')){							 
							  include $dirFile;
							$mod  = array_keys($dictionary)[0];
							$new_installdefs['vardefs'][] = array (
									   'from' => '<basepath>/'.$dirFile,
                                       'to_module' => $mod,
									);
									unset($dictionary);
							}
							if(is_dir($dirFile) && strpos($dirFile,'ugarModules/modules/')){							 
                                 $mod_name  = end(explode('/',$dirFile));                               
							$new_installdefs['copy'][] = array(
									'from' => '<basepath>/'.$dirFile,
									'to' => 'modules/'.$mod_name,
								);
							}
						else if(is_dir($dirFile)){
								outright_collect_subDir_file($dirFile);
							}
					}
			}
			
function outright_create_zip_with_folder($zip_name){
		$rootPath = getcwd();
		$zip = new ZipArchive();
		$zip->open($zip_name.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);	
		
		foreach ($files as $name => $file)
		{	
			if (!$file->isDir() && !strpos($file,'outright_create_package.php'))
			{
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				$zip->addFile($filePath, $relativePath);
			}
		}
		$zip->close();
		chmod($zip_name.'.zip',0777);
		set_time_limit(0); 
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		$zipname = $zip_name.'.zip';
		ob_clean();
		ob_end_flush();
		if (file_exists($zipname)) {
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.$zip_name.'.zip');
			header('Content-Length: ' . filesize($zipname));
			readfile($zipname);
		}
		unlink($zipname);
	}
