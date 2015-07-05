<?php
/*
Plugin Name: CasePress. Выводит дел на странице объекта
Description: Функции выводит на странице объекта все связанные дела. Объект должен быть указан через поле ACF (relationship) с ключем object.
Plugin URI: https://github.com/systemo-biz/casepress-cl4objects
Version: 1.0
Author: Systemo
*/

function list_cases_for_object($content){

    if(is_singular('objects')):
        $post = get_post();
    
        $items = get_posts(array(
							'post_type' => 'cases',
							'meta_query' => array(
								array(
									'key' => 'object', // name of custom field
									'value' => '"' . $post->ID . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
									'compare' => 'LIKE'
								)
							)
						));
    
        if($items):
            $url_list = add_query_arg( array('post_type'=>'cases','acf_object'=>$post->ID), get_site_url());
            ob_start();
            ?>    
                <section class="list_cases_for_object">
                    <header>
                        <h1>Дела по объекту</h1>
                    </header>
                    <ul>
                        <?php foreach( $items as $post ): setup_postdata($post);?>
                            <li>
                                <a href="<?php echo get_permalink( $post->ID ); ?>">
                                    <h2 class="entry-title"><?php echo get_the_title( $post->ID ); ?></h2>
                                </a>
                                <div>
                                    <ul class="list-inline">
                                        <?php do_action('case_meta_top_add_li'); ?>
                                    </ul> 
                                </div>
                            </li>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </ul>
                    <footer>
                        <a href="<?php echo $url_list ?>" class='btn btn-default'>Все дела</a>
                    </footer>
                </section>
            <?php
            $html = ob_get_contents();
             ob_get_clean();
             $content .= $html;
        endif;
    endif;
    
    return $content;
} add_filter('the_content', 'list_cases_for_object');


//добавляе возможность отбора постов через параметр урл case_members, который может содержать ИД персоны
// сейчас используется в досье Персноны, по возможности надо заменить на filter_posts_meta_cp (полный аналог, но более универсальный) и удалить всю данную функцию
function filter_case_object_acf( $query ) {
	
	if(! $query->is_main_query() ) return;
	if(empty($_REQUEST['acf_object'])) return;
    
	$acf_client = $_REQUEST['acf_object'];
    
	if($acf_client):
	
		//Get original meta query
		$meta_query = $query->get('meta_query');
		//Add our meta query to the original meta queries
		$meta_query[] = array(
                                        'key' => 'object', // name of custom field
                                        'value' => '"' . $acf_client . '"', // matches exaclty "123", not just 123. This prevents a match for "1234"
                                        'compare' => 'LIKE'
                                    );
		$query->set('meta_query',$meta_query);
		
	endif;
    
    return;
}
add_action( 'pre_get_posts', 'filter_case_object_acf' );