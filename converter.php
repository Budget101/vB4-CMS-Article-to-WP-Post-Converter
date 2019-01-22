<?php
header('Content-Type: text/html; charset=UTF-8');
require('./converter_inc.php');
require('./wp-load.php');
$wp_upload_dir = wp_upload_dir();
$pathImg=$wp_upload_dir['path'];

// Get CMS Article data
$conn = new mysqli($DBHost,$DBUser,$DBPassword, $DBName );
    if($conn->connect_errno > 0) {
         die('Unable to connect to database [' . $db->connect_error . ']');
    }
$conn->query("SET character_set_results=utf8mb4");
echo $query = "SELECT n.nodeid,i.title, pagetext,FROM_UNIXTIME(n.publishdate,'%Y-%m-%d %H:%i:%s') as publishdate, FROM_UNIXTIME(n.lastupdated,'%Y-%m-%d %H:%i:%s') as lastupdated, 
u.username,u.email,n.setpublish,n.comments_enabled,u.userid,n.parentnode,i.associatedthreadid
FROM ".$TablePrefix."cms_article a 
join ".$TablePrefix."cms_node n on a.contentid = n.contentid 
join ".$TablePrefix."cms_nodeinfo i on i.nodeid = n.nodeid 
join ".$TablePrefix."user u on n.userid = u.userid
ORDER BY n.nodeid DESC";
$results = $conn->query($query);
    if (!$results){
        die('query error');
    }
$i = 0;

// Associate Users to Articles
while ($row = $results->fetch_array(MYSQLI_BOTH))
{
    echo "<font color='#009900' size='+1'>Articles:".$row['title']." CMSID:".$row['nodeid']."</font><br />";
    echo "UserName CMS:".$row['username']."<br />";
        if($row['userid']==1){
        global $wpdb;
        $wpdb->query("UPDATE wp_users SET user_login='".$row['username']."', user_nicename='".$row['username']."',display_name='".$row['username']."' WHERE ID='".$row['userid']."'");
     }
$userWP = get_user_by('login' ,$row['username']);
$user_id_wp = $userWP->ID;
echo "UserID WP:".$user_id_wp."<br />";

// Process the articles as post, process attachments and categories
$UrlAttach="";
$AttachmentURL="";
$resultsw="";
$UrlAttach=array();
$queryw = "SELECT attachmentid,dateline,filename,settings FROM ".$TablePrefix."attachment WHERE contentid = '".$row['nodeid']."'";
$resultsw = $conn->query($queryw);
    while ($roww = $resultsw->fetch_array(MYSQLI_BOTH))
    {
        $AttachmentURL=$AttachmentsURL."".$roww["attachmentid"]."d".$roww["dateline"]."-".$roww["filename"]."/";
        $AttachmentURL=SaveAttachment($AttachmentURL,"".$roww["attachmentid"]."d".$roww["dateline"]."-".$roww["filename"]."");
        $UrlAttach[]=$AttachmentURL;
        $filetype = wp_check_filetype( basename($AttachmentURL), null );
            if($filetype['type']=="image/png" or $filetype['type']=="image/jpeg" or $filetype['type']=="image/jpg" or $filetype['type']=="image/gif"){
	            $class="";
                if(strpos($roww["settings"], "left") != false) {
	                $class='class="alignleft"';
                }   elseif(strpos($roww["settings"], "right") != false) {
	                $class='class="alignright"';
                }   elseif(strpos($roww["settings"], "center") != false){
	                $class='class="aligncenter"';
                }
	            $row['pagetext']=str_replace("]".$roww["attachmentid"]."[","]<img src=\"".$wp_upload_dir['url'] . "/" . basename( $AttachmentURL )."\" alt=\"\"".$class."/>[",$row['pagetext']);
	            $row['pagetext']=str_replace("]".$roww["attachmentid"]."[","]<img src=\"".$wp_upload_dir['url'] . "/" . basename( $AttachmentURL )."\" alt=\"\"".$class."/>[",$row['pagetext']);

            }elseif($filetype['type']=="application/pdf"){
	        // Else
            }
    }

    $post = array();
    $post['post_status'] = $row['setpublish'] == 1 ? 'publish':'draft';
    $post['post_date'] = $post['post_date_gmt'] = $row['publishdate'];
    $post['post_modified'] = $post['post_modified_gmt'] = $row['lastupdated'];
    $post['post_title'] = $row['title'];
    $post['post_content'] = bbcode_to_html(preg_replace("/\[(.*?)\]\s*(.*?)\s*\[\/(.*?)\]/", "[$1]$2[/$3]",nl2br($row['pagetext'])));
    $post['post_type'] = 'post';
    $post['import_id'] = $row['nodeid'];
    $post['comment_status'] = $row['comments_enabled'] == 1 ? 'open': 'closed';
    $post['ping_status'] = 'closed';
    $post['post_author'] = $user_id_wp;
    $post_ID = wp_insert_post($post,true);
	    if (is_wp_error($post_ID)){
                echo "<br/>post " . $row['title'] . "could not be inserted";
				echo '<pre>';
				print_r ($wp_error);
				echo '</pre>';
			
	    }else{
		$Categories="";
		$Categories=array();
		$SubCategory=$row['parentnode'];
		$var=array(1,2,3,4,5); 
				foreach($var as $valor){ 
							if($SubCategory!=1){
							  $tags_query = "
							  SELECT c.nodeid, c.parentnode, c.url, n.title
							  FROM ".$TablePrefix."cms_node c
							  JOIN ".$TablePrefix."cms_nodeinfo n ON c.nodeid = n.nodeid
							  WHERE n.nodeid =".$SubCategory;
							    $tags_results = $conn->query($tags_query);
								if (!$tags_results){
									   echo "The post does not have category.<br />";
								} else {
									$cat_ids = '';
									    while ($tags_row = $tags_results->fetch_array(MYSQLI_BOTH)) 
                                        {
									        $Categories[]=array($tags_row['title'],$tags_row['url']);
										    $SubCategory=$tags_row['parentnode'];
										}
								}
											
							}
				}
		$Categories=array_reverse($Categories);
		$c=0;
		$term_id="";
		$IDCategory="";
		$IDCategory=array();
				foreach($Categories as $Category)
				{
                    $c++;
                    if (get_cat_ID($Category[0])==0) {
						if($c==1) {
						    echo "Category: ";
						    $term=wp_insert_term($Category[0], 'category');
						} else {
						    echo "SubCategory: ";
						    $term=wp_insert_term($Category[0], 'category',  array('parent'=> $term_id));
						}
                    }
				$IDCategory[]=get_cat_ID($Category[0]);
				$term_id=get_cat_ID($Category[0]);
				echo $Category[0];
				echo " TermID: ";
				echo $term_id;
				echo "<br />";
				}
		wp_set_post_terms($post_ID,$IDCategory,'category',FALSE);

foreach($UrlAttach as $filename)
{
    $filetype = wp_check_filetype( basename( $filename ), null );
    $attachment = array(
	'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
	'post_mime_type' => $filetype['type'],
	'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
	'post_content'   => '',
	'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $filename, $post_ID);
}	
	
    if($row['associatedthreadid']!=0){
        echo $queryx = "SELECT postid,pagetext,username,userid,parentid,dateline FROM post WHERE threadid ='".$row['associatedthreadid']."' ORDER BY parentid,postid ASC";
        echo "<br />";
        $resultsx = $conn->query($queryx);
        $LastCommentID=0;
        $IDPrincipal="";
        $ArrayComment=array();
            while ($roww = $resultsx->fetch_array(MYSQLI_ASSOC))
            {
                echo "UserName CMS:".$roww['username']."<br />";
                if($roww['userid']==1){
                    global $wpdb;
                    $wpdb->query("UPDATE wp_users SET user_login='".$roww['username']."', user_nicename='".$roww['username']."',display_name='".$roww['username']."' WHERE ID='".$roww['userid']."'");
                }
               $userWP = get_user_by('login' ,$roww['username']);
               $user_id_wp = $userWP->ID;
               echo "UserID WP:".$user_id_wp."<br />";
                if($roww['parentid']==0) {
                    $IDPrincipal=$roww['postid'];
                     } else {
                if($IDPrincipal==$roww['parentid']){
                    $parentID=0;
                    } else {
                    $parentID=$ArrayComment[$roww['parentid']];
                }
            $data = array(
                'comment_post_ID' => $post_ID,
                'comment_author' => $roww['username'],
                'comment_author_url' => '',
                'comment_content' => bbcode_to_html(preg_replace("/\[(.*?)\]\s*(.*?)\s*\[\/(.*?)\]/", "[$1]$2[/$3]",nl2br($roww['pagetext']))),
                'comment_type' => '',
                'comment_parent' => $parentID,
                'user_id' => $roww['userid'],
                'comment_author_IP' => '127.0.0.1',
                'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
                'comment_date' => current_time('mysql'),
                'comment_approved' => 1,
            );
                $LastCommentID=wp_insert_comment($data);
                $wpdb->query("UPDATE wp_comments SET comment_date='".date("Y-n-j H:i:s",$roww['dateline'])."',comment_date_gmt='".date("Y-n-j H:i:s",$roww['dateline'])."' WHERE comment_ID='".$LastCommentID."'");
                $ArrayComment[$roww['postid']]=$LastCommentID;
                }
            }
    }
}}
?>
