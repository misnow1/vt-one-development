<?php

function GetSubpageMenu () {
      wp_nav_menu( array('fallback_cb' => '',
            'theme_location' => 'main_nav_menu',
            'container' => false,
            'depth' => 2,
            'menu_class' => 'nav navbar-nav navbar-right',
            'walker' => new vtone_bootstrap_menu(),
      ));
}

class vtone_sidebar_menu {
    var $menu = null;
    var $level = 1;
    var $current_node = array();

    function vtone_sidebar_menu($title) {
        $this->menu = new vtone_sidebar_menu_item($title, '#');
        $this->level = 1;
    }

    function add_item($name, $href, $level = 1) {
        $this->current_node[$level] =& $this->get_nearest_parent($level)->add_child($name, $href);
    }

    function &get_nearest_parent($level) {
        $level--;
        if ($level <= 0) {
            return $this->menu;
        }
        if (isset($this->current_node[$level])) {
            return $this->current_node[$level];
        }
        else {
            return $this->get_nearest_parent($level);
        }
    }

    function get_html() {
        $str .= "<ul class=\"nav\">\n";
        $str .= "<li><a href=\"#title\">" . $this->menu->name . "</a>\n";
        if ($this->menu->children) {
            $str .= "<ul class=\"nav\">\n";
            foreach ($this->menu->children as $root_child) {
                $str .= $root_child->get_html();
            }
            $str .= "</ul>\n";
        }
        $str .= "</li></ul>\n";

        return $str;
    }
}

class vtone_sidebar_menu_item {
    var $name = '';
    var $href = '';
    var $children = array();

    function vtone_sidebar_menu_item ($name, $href) {
        $this->name = $name;
        $this->href = $href;
    }

    function &add_child($name, $href) {
        $item = new vtone_sidebar_menu_item($name, $href);
        $this->children[] =& $item;
        return $item;
    }

    function get_html() {
        $str = "<li><a href=\"" . $this->href . "\">" . $this->name . "</a>";
        if ($this->children) {
            $str .= "\n<ul class=\"nav\">\n";
            foreach ($this->children as $child) {
                $str .= $child->get_html();
            }
            $str .= "</ul>\n";
        }
        $str .= "</li>\n";

        return $str;
    }
}

$vtone_sidebar_menu = null;
$anchor_counter = 0;

add_filter('the_content', 'vtone_filter_toc_items', 99);
function vtone_filter_toc_items($content) {
    global $anchor_counter;
    $anchor_counter = 0;
    $matches = array();

    return preg_replace_callback('|<h([2-9])([^>]*)>(.*)</h[^>]+>|U', 'vtone_filter_toc_replace', $content);
}

function vtone_filter_toc_replace($matches) {
    global $vtone_sidebar_menu, $anchor_counter;

    $anchor_counter++;

    $level = $matches[1];
    $tag_data = $matches[2];
    $title = $matches[3];

    $idmatches = array();
    if (preg_match('/id="([^"]+)"/', $tag_data, $idmatches)) {
        $id = $idmatches[1];
    }
    else {
        $id = "sidebar_anchor$anchor_counter";
        $tag_data = "$tag_data id=\"$id\"";
    }

    $vtone_sidebar_menu->add_item($title, "#$id", $level);

    return "<a name=\"$id\"></a><h$level$tag_data>$title</h$level>";
}

add_action('the_post', 'vtone_post_action');
function vtone_post_action($post) {
    global $vtone_sidebar_menu;
    $vtone_sidebar_menu = new vtone_sidebar_menu($post->post_title);
}

function get_sidebar_menu() {
    global $vtone_sidebar_menu;
    echo $vtone_sidebar_menu->get_html();
}
