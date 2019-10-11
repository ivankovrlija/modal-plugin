<?php 

$path = $_SERVER['DOCUMENT_ROOT']; 
include_once $path . '/wp-load.php';

$post_type = $_POST['post'];
// list posts
$args = array(
'post_type'=> $post_type,
'posts_per_page' => -1 // this will retrive all the post that is published 
);

$result = new WP_Query( $args );
if ( $result-> have_posts() ) : 
?>


<?php
while ( $result->have_posts() ) : $result->the_post();
?>

<p><?php the_title(); ?><input type="checkbox" name="all_posts" post-id="<?php echo get_the_ID(); ?>" post-type="<?php echo $post_type; ?>" post-status="1" value="<?php the_title(); ?>"></p>


<?php endwhile; ?> 

 

<?php
 endif; 
 wp_reset_postdata(); 

 ?>