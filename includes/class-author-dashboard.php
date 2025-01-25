<?php
require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/helper-function.php';
class Author_Dashboard {
    public function __construct() {
        add_action('admin_menu',[$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handleFormSubmission']);
    }

    public function register_admin_menu() {
        add_menu_page(
            'Author Dashboard', //page title
            'Author Dashboard', //Menu Title
            'edit_posts',
            'author-dashboard', //slug
            [$this, 'display_author_dashboard'],//callback
            'dashicons-welcome-write-blog'
        );
    }

    public function display_author_dashboard() {
        echo '<div class="wrap">';
        echo '<h1>Your Articles<h1>';
        $this->list_author_articles();
        echo '<h1>Create an Article</h1>';
        $this->article_form();
    }

    //listing author articles
    public function list_author_articles() {
        $user_id = get_current_user_id();

        $args = [
            'author' => $user_id,
            'post_type' => 'post',
            'post_status' => ['draft', 'publish', 'pending', 'future'],
            'date_query' => []
        ];

        $query = new WP_Query($args);
        if($query->have_posts()){
            echo '<table class="widefat fixed">';
            echo '<thead><tr><th>Title</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>';
            while($query->have_posts()) {
                $query->the_post();
                echo '<tr>';
                echo '<td>'.get_the_title().'</td>';
                echo '<td>'.get_post_status().'</td>';
                echo '<td>'.get_the_date().'</td>';
            }
            echo '</tbody></table>';
        }else{
            echo '<p>No article</p>';
        }
        wp_reset_postdata();
    }

    //creating an article
    public function article_form() {
        echo '<form method="post" action="" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="submit_article"';

        echo '<p><label for="article-title">Title:</label>';
        echo '<input type="text" name="article-title" id="article-title" required></p>';

        echo '<p><label for="article-content">Content:</label>';
        wp_editor('', 'article-content', ['textarea_name'=>'article-content']);
        echo '</p>';

        echo '<p><label for="article-featured-image">Featured Image:</label>';
        echo '<input type="file" name="article-featured-image" id="article-featured-image" accept="image/*"></p>';

        echo '<p><label for="article-media">Media File:</label>';
        echo '<input type="file" name="article-media[]" id="article-media" multiple accept="image/*"></p>';

        echo '<p><label for="article-category">Categories:</label>';
        wp_dropdown_categories( ['name'=>'article-category', 'hide_empty'=>false] );
        echo '</p>';

        echo '<p><label for="article-tags">Tags (comma separated):</label>';
        echo '<input type="text" name="article-tags" id="article-tags" required></p>';

        echo '<p><input type="submit" name="submit-article" value="Create Article" class="button button-primary"></p>';
        echo '</form>';
    }

    public function handleFormSubmission() {
        if($_SERVER['REQUEST_METHOD']=="POST") {
            if(isset($_POST['action'])){
                $action = sanitize_text_field($_POST['action']);
                if($action === 'submit_article') {
                    $this->createArticle();
                }
            }
        }
    }

    public function createArticle() {
        $title = sanitize_text_field($_POST['article-title']);
        $content = wp_kses_post($_POST['article-content']);
        $category = intval($_POST['article-category']);
        $tags = sanitize_text_field($_POST['article-tags']);

        $user_id = get_current_user_id();

        $post = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'draft',
            'post_type' => 'post',
            'post_author' => $user_id,
            'post_category' => [$category]
        ];

        $post_id = wp_insert_post($post);

        if($post_id) {
            wp_set_post_tags($post_id, $tags);
            
            if(!empty($_FILE['article-featured-image']['name'])){
                $featured_image_id = Helper_Function::upload_file($_FILES['article-featured-image']);
                if($featured_image_id) {
                    set_post_thumbnail($post_id, $featured_image_id);
                }
            }

            if (!empty($_FILES['article-media']['name'][0])) {
                foreach ($_FILES['article-media']['name'] as $key => $media_name) {
                    $media_file = [
                        'name'     => $_FILES['article-media']['name'][$key],
                        'type'     => $_FILES['article-media']['type'][$key],
                        'tmp_name' => $_FILES['article-media']['tmp_name'][$key],
                        'error'    => $_FILES['article-media']['error'][$key],
                        'size'     => $_FILES['article-media']['size'][$key],
                    ];
                    $attachment_id = Helper_Function::upload_file($media_file);
                    if ($attachment_id) {
                        add_post_meta($post_id, 'attached_media', $attachment_id);
                    }
                }
            }

            wp_safe_redirect(admin_url('admin.php?page=author-dashboard&success=1'));
        }
    }
} 
new Author_Dashboard();