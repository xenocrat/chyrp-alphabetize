<?php
    
    class Alphabetize extends Modules {
        static function __install() {
            Route::current()->add("alphabetical/", "alphabetical");
        }

        static function __uninstall() {
            Route::current()->remove("alphabetical/");
        }

        public function parse_urls($urls) {
            $urls["|/alphabetical/|"] = "/?action=alphabetical";
            return $urls;
        }

        public function main_context($context) {
            $context["alphabetize"] = url("alphabetical/");
            return $context;
        }

        public function main_alphabetical($main) {
            $query = SQL::current()->select("post_attributes",
                                            array("post_id"),
                                            array("name" => "title",
                                                  "value REGEXP" => "[[:alnum:]]+"),
                                            array("ORDER BY" => "post_id DESC"))->fetchAll();

            $ids = array();

            foreach ($query as $result)
                $ids[] = $result['post_id'];

            if (!empty($ids)) {
                $results = Post::find(array("placeholders" => true,
                                            "where" => array("id" => $ids)));

                usort($results[0], array($this, "sort_alphabetically"));
                $posts = new Paginator($results, Config::current()->posts_per_page);
            } else
                $posts = new Paginator(array());

            $main->display(array("pages".DIR."alphabetical", "pages".DIR."index"),
                           array("posts" => $posts),
                           __("Alphabetical", "alphabetize"));
        }

        private function sort_alphabetically($a, $b) {
            $index_a = array_search("title", $a["attribute_names"]);
            $index_b = array_search("title", $b["attribute_names"]);
            return $this->mb_strcasecmp($a["attribute_values"][$index_a], $b["attribute_values"][$index_b]);
        }

        private function mb_strcasecmp($str1, $str2, $encoding = "UTF-8") {
            $str1 = preg_replace("/[[:punct:]]+/", "", $str1);
            $str2 = preg_replace("/[[:punct:]]+/", "", $str2);

            if (!function_exists("mb_strtoupper"))
                return substr_compare(strtoupper($str1), strtoupper($str2), 0);

            return substr_compare(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding), 0);
        }
    }
