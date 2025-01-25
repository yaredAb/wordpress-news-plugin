<?php

class Author_Dashboard {
    public function __construct() {
        add_action('admin_menu',[$this, 'register_admin_menu']);
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
    }

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
} new Author_Dashboard();