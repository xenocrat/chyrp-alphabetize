<?php
    
    class Alphabetize extends Modules {
        public function main_alphabetical($main): void {
            $results = SQL::current()->select(
                tables:"post_attributes",
                fields:array("post_id", "value"),
                conds:array("name" => "title"),
                order:array("post_id DESC")
            )->fetchAll();

            $ids = array();

            foreach ($results as $result) {
                if (preg_match("/[[:alnum:]]+/", $result["value"]))
                    $ids[] = $result["post_id"];
            }

            if (!empty($ids)) {
                $results = Post::find(
                    array(
                        "placeholders" => true,
                        "where" => array("id" => $ids)
                    )
                );
                usort(
                    $results[0],
                    array($this, "sort_alphabetically")
                );
                $posts = new Paginator($results, $main->post_limit);
            } else {
                $posts = new Paginator(array());
            }

            $main->display(
                array("pages".DIR."alphabetical", "pages".DIR."index"),
                array("posts" => $posts),
                __("Posts in alphabetical order", "alphabetize")
            );
        }

        private function sort_alphabetically($a, $b): int {
            $index_a = array_search("title", $a["attribute_names"]);
            $index_b = array_search("title", $b["attribute_names"]);
            return $this->mb_strcasecmp(
                $a["attribute_values"][$index_a],
                $b["attribute_values"][$index_b]
            );
        }

        private function mb_strcasecmp($str1, $str2, $encoding = "UTF-8"): int {
            $str1 = preg_replace("/[[:punct:]]+/", "", $str1);
            $str2 = preg_replace("/[[:punct:]]+/", "", $str2);

            return substr_compare(
                mb_strtoupper($str1, $encoding),
                mb_strtoupper($str2, $encoding),
                0
            );
        }
    }
