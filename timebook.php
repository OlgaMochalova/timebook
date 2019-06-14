<?php
/*
Plugin Name: Timebook Plugin
Plugin URI: http://mif.vspu.ru
Description: Плагин Timebook.
Author: Ольга Мочалова
Version: 1.0
Author URI: 
*/

require ( 'materials.php' );


global $gmt;
$gmt = 4;

// Функция запускается после загрузки WordPress и перед выводом чего-либо на экран
// Здесь можно определять свои типы записей, назначить шорткоды и др. 
// Подробнее (все хуки и фильтры): http://wp-kama.ru/hooks

add_action( 'init', 'timebook_init' );

function timebook_init() 
{

    // В примере - создается новый тип записей "Анкета" (questionnaire)
    // Подробнее - http://wp-kama.ru/function/register_post_type

    register_post_type( 'timetable', array(
        'labels' => array(
        'name'            => __( 'Расписание' ),
        'singular_name'   => __( 'Расписание' ),
        'add_new'         => __( 'Добавить расписание' ),
        'add_new_item'    => __( 'Добавить расписание' ),
        'edit'            => __( 'Редактировать расписание' ),
        'edit_item'       => __( 'Редактировать элемент расписания' ),
        'new_item'        => __( 'Новый элемент' ),
        'all_items'       => __( 'Все элементы' ),
        'view'            => __( 'Просмотреть все элементы' ),
        'view_item'       => __( 'Просмотреть элемент' ),
        'search_items'    => __( 'Найти элементы' ),
        'not_found'       => __( 'Элемент не найден' ),
    ),
    'public' => true, 
    'menu_position' => 20,
    'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
    'taxonomies' => array( 'tttax' ),
    'has_archive' => true,
    'capability_type' => 'post',
    'menu_icon'   => 'dashicons-smiley',
    'rewrite' => array('slug' => 'timetable'),
    ));    
    // !!! Изменения вступают в силу после нажания кнопки "Сохранить" на странице /wp-admin/options-permalink.php  

    register_taxonomy('tttax', array('timetable'), array(
		'label'                 => '', // определяется параметром $labels->name
		'labels'                => array(
			'name'              => 'Genres',
			'singular_name'     => 'Genre',
			'search_items'      => 'Search Genres',
			'all_items'         => 'All Genres',
			'view_item '        => 'View Genre',
			'parent_item'       => 'Parent Genre',
			'parent_item_colon' => 'Parent Genre:',
			'edit_item'         => 'Edit Genre',
			'update_item'       => 'Update Genre',
			'add_new_item'      => 'Add New Genre',
			'new_item_name'     => 'New Genre Name',
			'menu_name'         => 'Genre',
		),
		'description'           => 'Группы', // описание таксономии
		'public'                => true,
		'publicly_queryable'    => null, // равен аргументу public
		'show_in_nav_menus'     => true, // равен аргументу public
		'show_ui'               => true, // равен аргументу public
		'show_in_menu'          => true, // равен аргументу show_ui
		'show_tagcloud'         => true, // равен аргументу show_ui
		'show_in_rest'          => null, // добавить в REST API
		'rest_base'             => null, // $taxonomy
		'hierarchical'          => true,
		//'update_count_callback' => '_update_post_term_count',
		'rewrite'               => true,
		//'query_var'             => $taxonomy, // название параметра запроса
		'capabilities'          => array(),
//		'meta_box_cb'           => null, // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
		'show_admin_column'     => false, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
		'_builtin'              => false,
		'show_in_quick_edit'    => true, // по умолчанию значение show_ui
	) );





}


// Подключаем свой файл CSS
// Подробнее: http://wp-kama.ru/function/wp_register_style

add_action( 'wp_enqueue_scripts', 'add_plugin_styles' );

function add_plugin_styles() {
	wp_register_style( 'plugin-styles', plugins_url( 'styles.css', __FILE__ ) );
	wp_enqueue_style( 'plugin-styles' );
}


// Функция получает текст записи, который можно изменить перед выводом на экран

add_filter ('the_content', 'add_custom_content');

function add_custom_content( $content ) 
{
    global $post;

    if ( $post->post_type == 'timetable' ) {
        // Делаем изменения только для зарегистрированного нами типа записей
        global $post;
            
        return get_timetable($post->post_content);
 
    } else {
    
    
        return $content;
    
    }
    

    
}


function get_timetable($content)
{
    $out = '';
    
    $arr = parse_timetable($content);
    $arr_now = get_now( $arr );
    if (!empty($arr_now)){
    $out .= arr_to_html($arr_now, 'Текущие занятия' );
    }
    else $out .= ('<h2>' . 'Текущих занятий нет'. '</h2>' );  
    $out .= arr_to_html($arr, 'Расписание занятий');
    
    return $out;

}

function arr_to_html($arr, $title)
{
    $out = '';
    $out .= '<h2>' . $title . '</h2>';


    $out .= '<table>';
    $date = '';
    $month = '';
    
    foreach ( (array) $arr as $row ) {
       

       if ( $month != $row['month'] ) {
            
            $out .= '<tr>'; 
            $out .= '<th colspan="4">' . $row['month'] .  '</th>';   //месяц
            $out .= '</tr>';
            $month = $row['month'];
            
       }
            


       if ( $date != $row['date'] ) {
            // Выводим текущую дату - один раз
           
            $out .= '<tr>';
            $out .= '<th colspan="4">' . $row['date'] . ', ' . $row['day'] . '</th>';   
            $out .= '</tr>';
            $date = $row['date'];

       }
       
       $out .= '<tr>';

       
       $out .= '<td>' . $row['time'] . '</td>';
       $out .= '<td>' . $row['course'] . '</td>';
       $out .= '<td>' . $row['pg'] . '</td>';     //подгруппа
       $out .= '<td>' . $row['room'] . '</td>';
       $out .= '<td>' . $row['teacher'] . '</td>';
       $out .= '<td>' . get_icon( $row['url'] ) . '</td>';
       
       
       

       $out .= '</tr>';
       
       
 
    
    }
    
    
     $out .= '</table>';
     
     return $out;
}

function get_now( $arr )
{
    $arr_now = array();

    $now = time();
    
    foreach ( $arr as $item ) {

        $delta = $now -  $item['timestamp'];
        
        if ( $delta > 0 && $delta < 60*60*1.5 ) $arr_now[] = $item;
    
    }

    return $arr_now;
}



function get_icon( $url )
{

    $out = '';
    
//    $out .= $url;
    
    if ( ! empty($url) ) $out .= "<a href=\"$url\"><i class=\"fa fa-file-text fa- \"></i></a>";    
    
    
    return $out;

}

function parse_timetable($content)
{
// ГГГГ-MM-ДД ЧЧ:ММ:СС. Например, 2008-10-23 10:37:22. 
    $arr = array();
    
    $lines_raw = explode( "\n", $content ); 
    
    // Развернуть повторящиеся занятия в массиве текстовых строк  

    $lines = array();

    foreach ( (array) $lines_raw as $line ) {
    
        $c = explode( "|", $line );

        if ( preg_match( '/\.\./', $c[0] ) ) {

            // Указан диапазон - развертываем
            
            $delta = ( preg_match( '/\*\*/', $c[0] ) ) ? 60*60*24*14 : 60*60*24*7; // Период повторения - неделя или две недели (в секундах)    

            $d = explode( "..", $c[0] ); 
            $start = get_timestamp( $d[0], $c[1] );
            $end = get_timestamp( $d[1], $c[1] );

            // Перебрать все даты повторения занятий

            for ( $i=$start; $i<$end; $i+=$delta ) {
            
                $new_line = $c;
                $new_line[0] = date( 'Y-m-d', $i ); 
                
                $lines[] = implode( "|", $new_line );
                
            }

       
        } else {
        
            // Указана просто дата - используем как есть
        
            $lines[] = $line;
            
        }
    
    }    
    
//    p( $lines_raw );
//    p( $lines );
    
    // Создать полный массив расписания на основе массива текстовых строк
    
    foreach ( (array) $lines as $line ) {
        
        if ( ! trim( $line ) ) continue;
        
        $c = explode( "|", $line );
        $c = array_map( "trim", $c );
        
        $timestamp = get_timestamp( $c[0], $c[1] );
        
        $day = date( "D", $timestamp );
        $month = date( "F", $timestamp );      //Месяц
         
        
        $col = array();
        $col['timestamp'] = $timestamp;
        $col['date'] = $c[0];
        $col['time'] = $c[1];
        $col['day'] = $day;
        $col['month'] = $month;                //Месяц
        $col['course'] = $c[2];
        $col['pg'] = $c[3];//подгруппа
        $col['room'] = $c[4];
        $col['teacher'] = $c[5];
        $col['url'] = ( isset( $c[6] ) ) ? $c[6] : '';
        
        $arr[] = $col;
       
    }
 
    usort($arr, "cmp");

    // p($arr);
    
    return $arr;
} 

// Функция для сравнения строк расписания

function cmp($a, $b)
{
    if ($a['timestamp'] == $b['timestamp']) {
        return 0;
    }
    return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
}

// Вернуть метку времени по сведениям о дате и времени
// $d = 2019-02-16
// $t = 12:10 

function get_timestamp( $d, $t )
{
    global $gmt;
    
    $y = explode( "-", $d ); 
    $h = explode( ":", $t );
    $timestamp = mktime( (int) $h[0], (int) $h[1], 0, (int) $y[1], (int) $y[2], (int) $y[0] ) - $gmt * 3600;
    
    return $timestamp;
}


function p($txt)
{
    print_r('<pre>');
    print_r($txt);    
    print_r('</pre>'); 
}



?>
