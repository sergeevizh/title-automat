<?php
/*
Plugin Name: Заголовок - автонаписание
Description: Скрывает поле написания заголовка на странице объектов и формирует его на базе данных объекта
Version: 1
*/


/**
 *
 */
class TitleAutomatS{
  function __construct()
  {
    add_action('save_post', array($this, 'chg_title'), 99);
    add_filter( 'comments_after_title', array($this, 'add_action_in_title'), $priority = 10, $accepted_args = 2 );
    add_filter( 'comments_after_title', array($this, 'add_rooms_in_title'), $priority = 10, $accepted_args = 2 );
    add_filter( 'comments_after_title', array($this, 'add_address_in_title'), $priority = 10, $accepted_args = 2 );
    add_action('admin_head', array($this, 'style_admin'));

  }

  function style_admin() {

    $post = get_post();

    if('property' != $post->post_type) return;

    ?>
      <style id="property_style">
        #title {
            display: none;
        }
      </style>

    <?php
  }


  //Добавляем адрес в заголовок
  function add_address_in_title($comment, $post_id){
    $address = get_post_meta( $post_id, 'address', true );

    $address = $address['address'];

    if(strlen($address) > 3) $comment[] = $address;

    return $comment;
  }

  //Добавляем количество комнат в заголовок
  function add_rooms_in_title($comment, $post_id) {
    $rooms = get_post_meta( $post_id, 'number_rooms', true );

    switch ($rooms) {
      case '1':
        $rooms = '1-комнатная';
        break;
      case '2':
        $rooms = '2-комнатная';
        break;
      case '3':
        $rooms = '3-комнатная';
        break;
      case '4':
        $rooms = '4-комнатная';
        break;
      case '5':
        $rooms = '5-комнатная';
        break;
      default:
        $rooms = '';
        break;
    }

    if(!empty($rooms)) $comment[] = $rooms;

    return $comment;
  }

  //Добавляем в заголовок тип предложения
  function add_action_in_title($comment, $post_id) {

    $action = wp_get_post_terms( $post_id, 'type_offer' );
    if(isset($action[0]->name)) $comment[] = $action[0]->name;

    return $comment;
  }

  //Формируем заголовок на основе категории
  function chg_title($post_id){

    // If it is our form has not been submitted, so we dont want to do anything
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $post = get_post($post_id);

    if(!current_user_can( 'create_users' )) return;

    if('property' != $post->post_type) return;

    $category_src = wp_get_post_terms( $post->ID, 'property_category' );

    $category = 'Объект';

    if(!empty($category_src)) $category = $category_src[0]->name;

    $title = $category;

    $comments = array();
    $comments = apply_filters( 'comments_after_title', $comments, $post_id );
    $comments = implode(", ", $comments);
    if($comments) $title = $title . ' (' . $comments . ')';

    remove_action( 'save_post', array($this, 'chg_title'), 99);

    wp_update_post( array(
		    'ID'           => $post_id,
		    'post_title' => $title . '- #' . $post_id,
        'post_name' => $post_id . '-' . $title,
  	));

    add_action( 'save_post', array($this, 'chg_title'), 99);
  }
}
 $TheTitleAutomatS = new TitleAutomatS;
