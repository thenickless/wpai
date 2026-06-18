<?php

namespace BK\WPAI\Common\Sync;

use BK\WPAI\Common\API\SyncAPI;

use BK\WPAI\Common\Tools;

defined('ABSPATH') || exit;

class Sync
{

    protected $syncAPI;

    public function __construct()
    {
        $this->syncAPI = new SyncAPI();
        add_action('wp_ai_auto_sync', [$this, 'runCronjob']);
    }

    public function runCronjob()
    {
        $this->doSync('automatic');
    }

    public function setCronjob($frequency)
    {
        $hook = 'wp_ai_auto_sync';

        if ($frequency == '') {
            wp_clear_scheduled_hook($hook);
            return;
        }

        switch ($frequency) {
            case 'daily':
                $interval = DAY_IN_SECONDS;
                break;
            case 'twicedaily':
                $interval = 12 * HOUR_IN_SECONDS;
                break;
            default:
                return;
        }

        $nextcron = time() + $interval;

        wp_clear_scheduled_hook($hook);
        wp_schedule_event($nextcron, $frequency, $hook);

        $timestamp = wp_next_scheduled($hook);
        $wp_tz = wp_timezone();
        $dt = new \DateTime('@' . $timestamp); // @ = UTC timestamp
        $dt->setTimezone($wp_tz);

        $message = __('Next automatic synchronization:', 'wp-ai') . ' ' . $dt->format('d.m.Y H:i:s');
        add_settings_error('BK-WP AI', 'autosynccomplete', $message, 'updated');
    }


    public function doSync($mode)
    {
        $tStart = microtime(true);
        $max_exec_time = ini_get('max_execution_time') - 40; // ini_get('max_execution_time') is not the correct value perhaps due to load-balancer or proxy or other fancy things I've no clue of. But this workaround works for now.
        $iCnt = 0;

        $domains = $this->syncAPI->getDomains();
        $options = get_option('wp-ai');
        // $allowSettingsError = ($mode == 'manual' ? true : false);
        $allowSettingsError = true;
        $syncRan = false;

        $types = [
            'faq' => 'FAQ',
            'glossary' => __('Glossary', 'wp-ai')
        ];

        foreach ($domains as $identifier => $url) {
            $tStartDetail = microtime(true);

            foreach ($types as $type => $label) {
                $fieldname = $type . '_categories_' . $identifier;

                $categories = (!empty($options[$fieldname]) ? implode(',', $options[$fieldname]) : false);

                if ($categories) {
                    $aCnt = $this->syncAPI->setEntries($type, $identifier, $categories, $url);
                    if (is_wp_error($aCnt)) {
                        $error_msg = __('Domain', 'wp-ai') . ' "' . $url . '": ' . $label . ' - ' . $aCnt->get_error_message();
                        Tools::logIt($error_msg . ' | ' . $mode);
                        if ($allowSettingsError) {
                            add_settings_error('BK-WP AI', 'syncerror', $error_msg, 'error');
                        }
                        continue;
                    }
                    $syncRan = true;

                    foreach ($aCnt['URLhasSlider'] as $URLhasSlider) {
                        $error_msg = __('Domain', 'wp-ai') . ' "' . $url . '": ' . __('Synchronization error. This ' . $label . ' contains sliders ([gallery]) and cannot be synchronized:', 'wp-ai') . ' ' . $URLhasSlider;
                        Tools::logIt($error_msg . ' | ' . $mode);

                        if ($allowSettingsError) {
                            add_settings_error('BK-WP AI', 'syncerror', $error_msg, 'error');
                        }
                    }

                    $sync_msg = __('Domain', 'wp-ai') . ' "' . $url . '": ' . $label . ' ' . __('Synchronization completed.', 'wp-ai') . ' ' . $aCnt['iNew'] . ' ' . __('new', 'wp-ai') . ', ' . $aCnt['iUpdated'] . ' ' . __('updated', 'wp-ai') . ' ' . __('and', 'wp-ai') . ' ' . $aCnt['iDeleted'] . ' ' . __('deleted', 'wp-ai') . '. ' . __('Required time:', 'wp-ai') . ' ' . sprintf('%.1f ', microtime(true) - $tStartDetail) . __('seconds', 'wp-ai');
                    Tools::logIt($sync_msg . ' | ' . $mode);

                    if ($allowSettingsError) {
                        add_settings_error('BK-WP AI', 'synccompleted', $sync_msg, 'success');
                    }
                }
            }
        }

        if ($syncRan) {
            $sync_msg = __('All synchronizations completed', 'wp-ai') . '. ' . __('Required time:', 'wp-ai') . ' ' . sprintf('%.1f ', microtime(true) - $tStart) . __('seconds', 'wp-ai');
        } else {
            $sync_msg = __('Settings updated', 'wp-ai');
        }

        if ($allowSettingsError) {
            add_settings_error('BK-WP AI', 'synccompleted', $sync_msg, 'success');
        }

        Tools::logIt($sync_msg . ' | ' . $mode);
        return;
    }
}
