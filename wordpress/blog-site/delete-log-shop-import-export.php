<?php

/**
 * Delete log and dump
 */
class DeleteLogShopImportExport
{
    /**
     * Log Path
     */
    const LOG_PATH = '/var/log/shoplog';

    /**
     * Back up dump file
     */
    const DUMP_PATH = '/manager/wp-content/plugins/shop-import-export/bk';

    /**
     * Source directory
     */
    const SRC_DIR = '/usr/share/nginx/html';

    /**
     * wordpressテーブル接頭辞
     */
    const PREFIX = 'tokyo';

    /**
     * Identify what kind
     * of site. It varies
     * on the parameters
     *
     * @var string
     */
    private $site;

    /**
     * Prefix or site type
     * tokyo, miyagi, saitama
     *
     * @var string
     */
    private $prefix;

    /**
     * Log directory path
     *
     * @var string
     */
    private $log_dir_path;

    /**
     * Dump directory path
     *
     * @var string
     */
    private $dump_dir_path;

    /**
     * Array of site list.
     * This will change when putting
     * in production.
     *
     * @var array
     */
    private $site_list = array(
        'tokyo'   => 'wp',
        'saitama' => 'saitama',
        'miyagi'  => 'miyagiwp',
        'osaka'   => 'osaka',
        'fukuoka' => 'fukuoka',
    );

    /**
     * Get the kind of parameter
     *
     * @param string $argv
     */
    public function __construct($argv)
    {
        $type = isset($argv[1])?$argv[1]:self::PREFIX;
        if (!in_array($type, array_keys($this->site_list ))) {
            $this->prefix = $type;
            exit;
        }

        $this->prefix = $type;
        $this->site = $this->site_list[$type];
        $this->log_dir_path = self::LOG_PATH;
        $this->dump_dir_path = self::SRC_DIR.'/'.$this->site_list[$type].'/'.self::DUMP_PATH;
    }

    /**
     * Execute the deleting of files
     *
     * @return
     */
    public function exec()
    {
        $this->get_log_dir();

        $this->get_dump_dir();
    }

    /**
     * Get the list of files for
     * the log
     *
     * @return
     */
    private function get_log_dir()
    {
        $log_dir = scandir($this->log_dir_path);
        $sched = $shop = array();
        foreach ($log_dir as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // other sites aside from the current site
            if (strpos($file, $this->prefix) === false) {
                continue;
            }

            if (is_dir($this->log_dir_path.'/'.$file)) {
                continue;
            }

            $filemtime = filemtime($this->log_dir_path.'/'.$file);

            if (strpos($file, 'sched') !== false) {
                $sched[$filemtime] = $this->log_dir_path.'/'.$file;
            } else {
                $shop[$filemtime] = $this->log_dir_path.'/'.$file;
            }

        }

        // for shop
        arsort($shop);
        $shop_key = 0;
        foreach($shop as $key => $file) {
            if ($shop_key > 4) {
                unlink($file);
            }
            $shop_key++;
        }

        // for sched
        arsort($sched);
        $sched_key = 0;
        foreach($sched as $key => $file) {
            if ($sched_key > 4) {
                unlink($file);
            }
            $sched_key++;
        }
    }

    /**
     * Get the list of dump files
     * for the dump backup files
     *
     * @return
     */
    private function get_dump_dir()
    {
        $dump_dir = scandir($this->dump_dir_path);
        $postmeta = $shop = array();

        foreach ($dump_dir as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($this->dump_dir_path.'/'.$file)) {
                continue;
            }

            $filemtime = filemtime($this->dump_dir_path.'/'.$file);

            if (strpos($file, 'postmeta') !== false) {
                $postmeta[$filemtime] = $this->dump_dir_path.'/'.$file;
            } else {
                $shop[$filemtime] = $this->dump_dir_path.'/'.$file;
            }
        }

        // for shop
        arsort($shop);
        $shop_key = 0;
        foreach($shop as $key => $file) {
            if ($shop_key > 4) {
                unlink($file);
            }
            $shop_key++;
        }

        // for postmeta
        arsort($postmeta);
        $postmeta_key = 0;
        foreach($postmeta as $key => $file) {
            if ($postmeta_key > 4) {
                unlink($file);
            }
            $postmeta_key++;
        }
    }
}

$delete = new DeleteLogShopImportExport($argv);
$delete->exec();
