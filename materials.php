<?php


function materials_show( $atts )
{

    $out = '';
    
    $user = wp_get_current_user();
    
    if ( $user->ID == 0 ) return;
   
    $gpoup = $user->user_description;
 //   p($gpoup);
    $term = get_term_by('name', $gpoup, 'tttax');
//    p($term);
    if ( empty( $term ) ) return;

//    p($term->term_id);
    
    $posts = get_posts( array(
	'numberposts' => 5,
    'tax_query' => array(
		array(
			'taxonomy' => 'tttax',
			'field'    => 'id',
			'terms'    => $term->term_id
		)
	),
	'orderby'     => 'date',
	'order'       => 'DESC',
	'post_type'   => 'timetable',
    'post_status' => 'publish'
    ) );

//    p($posts);

    $content = '';
    
    foreach ( $posts as $post ) {

        $content .= $post->post_content;
        $content .= "/n";
    
    }


    $arr = parse_timetable($content);
    $arr_now = get_now( $arr );
    if (!empty($arr_now)){
    $out .= arr_to_html($arr_now, 'Текущие занятия' );
    }
    else $out .= ('<h3>' . 'Текущих занятий нет'. '</h3>' );
    
    
    

	return $out;
}
 
add_shortcode( 'materials', 'materials_show' );



?>