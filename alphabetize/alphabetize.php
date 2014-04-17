<?php
    
    class Alphabetize extends Modules {
        static function __install() {
            Route::current()->add("alphabetical/", "alphabetical");
        }

        static function __uninstall($confirm) {
            Route::current()->remove("alphabetical/");
        }

        public function parse_urls($urls) {
            $urls["/\/alphabetical\//"] = "/?action=alphabetical";
            return $urls;
        }

        public function main_context($context) {
            $context["alphabetize"] = url("alphabetical/");
            return $context;
        }

        public function main_alphabetical($main) {
            $record = SQL::current()->query("SELECT __posts.id FROM __posts
                                             LEFT JOIN __post_attributes
                                                ON (__posts.id = __post_attributes.post_id
                                                AND __post_attributes.name = 'title')
                                             WHERE (__post_attributes.value REGEXP '[[:alnum:]]+')");

            $ids = array();
            foreach ($record->fetchAll() as $entry)
                $ids[] = $entry['id'];

            if (!empty($ids)) {
                $results = Post::find(array("placeholders" => true,
                                            "where" => array("id" => $ids)));
                usort($results[0], array($this, "sort_alphabetically"));
                $posts = new Paginator($results, Config::current()->posts_per_page);
            } else {
                $posts = new Paginator(array());
            }

            $main->display(array("pages/alphabetical", "pages/index"),
                           array("posts" => $posts),
                              __("Alphabetical", "alphabetize"));
        }

        private function sort_alphabetically($a, $b) {
            $index_a = array_search("title", $a["attribute_names"]);
            $index_b = array_search("title", $b["attribute_names"]);
            return $this->mb_strcasecmp($a["attribute_values"][$index_a], $b["attribute_values"][$index_b], "UTF-8");
        }

        private function mb_strcasecmp($str1, $str2, $encoding = null) {
            if (null === $encoding)
                $encoding = mb_internal_encoding();
            $str1 = preg_replace("/[[:punct:]]+/", "", $str1);
            $str2 = preg_replace("/[[:punct:]]+/", "", $str2);
            return substr_compare(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding), 0);
        }
    }
