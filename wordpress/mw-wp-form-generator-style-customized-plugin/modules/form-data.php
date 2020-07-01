<?php

/**
 * Display form data
 */
class Form_data
{
    public function __construct()
    {
    }

    /**
     * Display html
     *
     * @return
     */
    public function display_data()
    {
        $this->html_template();
    }

    /**
     * Display template
     *
     * @return
     */
    private function html_template()
    { ?>
        <div class="wrap sie-body">
            <form method="post" action="">
                <h1 class="wp-heading-inline">お申し込みデータ</h1>

            </form>
        </div>
<?php
    }
}
