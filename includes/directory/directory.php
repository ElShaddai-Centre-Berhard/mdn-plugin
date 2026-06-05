<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Directory {

    public static function init() {
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-post-type.php';
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-directory.php';
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-featured.php';
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-submission.php';
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-review.php';
        require_once MDN_PATH . 'includes/directory/class-mdn-partner-map.php';

        MDN_Partner_Post_Type::init();
        MDN_Partner_Directory::init();
        MDN_Partner_Featured::init();
        MDN_Partner_Submission::init();
        MDN_Partner_Review::init();
        MDN_Partner_Map::init();
    }
}
