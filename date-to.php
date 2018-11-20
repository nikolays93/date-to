<?php

/*
 * Plugin Name: Date to
 * Plugin URI: https://github.com/nikolays93
 * Description: New plugin boilerplate
 * Version: 0.1.2
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: _plugin
 * Domain Path: /languages/
 */

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');

define('ACTIVE_TO_USE_META', FALSE);
define('ACTIVE_TO_POSTMETA', '_active_to');
define('ACTIVE_TO_LABEL', 'До: ');
define('ACTIVE_TO_DEFAULT_SECONDS', DAY_IN_SECONDS * 2);

function touch_time_to( $edit = 1, $post_date = '0000-00-00 00:00:00', $tab_index = 0 )
{
    global $wp_locale;

    $post = get_post();

    // if ( $for_post )
    //     $edit = ! ( in_array($post->post_status, array('draft', 'pending') ) && (!$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );

    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
        $tab_index_attribute = " tabindex=\"$tab_index\"";

    // todo: Remove this?
    // echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';

    $time_adj = current_time('timestamp');
    // $post_date = ($for_post) ? $post->post_date : get_comment()->comment_date;
    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );

    $month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select name="mm_to"' . $tab_index_attribute . ">\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $monthnum = zeroise($i, 2);
        $monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
        $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
        /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
        $month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
    }
    $month .= '</select></label>';

    $day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" name="jj_to" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" name="aa_to" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" name="hh_to" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
    $minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" name="mn_to" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';

    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );

    echo '</div><input type="hidden" name="ss_to" value="' . $ss . '" />';

    ?>
    <p>
        <a href="#edit_timestamp" class="save-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
    </p>
    <?php
}

add_action( 'post_submitbox_misc_actions', 'date_to_field' );
function date_to_field($post)
{
    global $action;

    $post_type = $post->post_type;
    $post_type_object = get_post_type_object($post_type);
    $can_publish = current_user_can($post_type_object->cap->publish_posts);

    if ( $can_publish ) : // Contributors don't get to choose the date of publish

        if( ACTIVE_TO_USE_META ) {
            $post_date = get_post_meta( $post->ID, ACTIVE_TO_POSTMETA, true );
        }
        else {
            $post_date = $post->post_modified;
        }
        
        if( !$post_date ) {
            $now = current_time( 'mysql' );
            $post_date = date( 'Y-m-d H:i:s', strtotime( $now ) + ACTIVE_TO_DEFAULT_SECONDS );
        }

        $stamp = ACTIVE_TO_LABEL . '<b>%1$s</b>';
        $datef = __( 'M j, Y @ H:i' );
        $date = date_i18n( $datef, strtotime( $post_date ) );
        ?>
    <style type="text/css">
        #timestamp_to:before {
            content: "\f145";
            position: relative;
            top: -1px;

            font: normal 20px/1 dashicons;
            speak: none;
            display: inline-block;
            margin-left: -1px;
            padding-right: 3px;
            vertical-align: top;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;

            color: #82878c;
        }

        #timestamp_todiv select {
            height: 21px;
            line-height: 14px;
            padding: 0;
            vertical-align: top;
            font-size: 12px;
        }

        [name="aa_to"], [name="jj_to"], [name="hh_to"], [name="mn_to"] {
            padding: 1px;
            font-size: 12px;
        }

        [name="jj_to"], [name="hh_to"], [name="mn_to"] {
            width: 2em;
        }

        [name="aa_to"] {
            width: 3.4em;
        }
    </style>
    <div class="misc-pub-section curtime misc-pub-curtime">
        <span id="timestamp_to">
        <?php printf($stamp, $date, 0, 1); ?></span>
        <a href="#edit_timestamp_to" class="edit-timestamp_to hide-if-no-js" role="button"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit date and time' ); ?></span></a>
        <fieldset id="timestamp_todiv" class="hide-if-js">
        <legend class="screen-reader-text"><?php _e( 'Date and time' ); ?></legend>
        <?php
        touch_time_to( 1, $post_date );
        ?>
        </fieldset>
    </div><?php // /misc-pub-section ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $timestampdiv = $('#timestamp_todiv');
            var updateText;
            var stamp = $('#timestamp_to').html();

            updateText = function() {

                if ( ! $timestampdiv.length )
                    return true;

                var attemptedDate, originalDate, currentDate, publishOn, postStatus = $('#post_status'),
                    optPublish = $('option[value="publish"]', postStatus), aa = $('[name="aa_to"]').val(),
                    mm = $('[name="mm_to"]').val(), jj = $('[name="jj_to"]').val(), hh = $('[name="hh_to"]').val(), mn = $('[name="mn_to"]').val();

                attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
                // originalDate = new Date( $('#hidden_aa').val(), $('#hidden_mm').val() -1, $('#hidden_jj').val(), $('#hidden_hh').val(), $('#hidden_mn').val() );
                // currentDate = new Date( $('#cur_aa').val(), $('#cur_mm').val() -1, $('#cur_jj').val(), $('#cur_hh').val(), $('#cur_mn').val() );

                // Catch unexpected date problems.
                if ( attemptedDate.getFullYear() != aa || (1 + attemptedDate.getMonth()) != mm || attemptedDate.getDate() != jj || attemptedDate.getMinutes() != mn ) {
                    $timestampdiv.find('.timestamp-wrap').addClass('form-invalid');
                    return false;
                } else {
                    $timestampdiv.find('.timestamp-wrap').removeClass('form-invalid');
                }

                // Determine what the publish should be depending on the date and post status.
                // if ( attemptedDate > currentDate && $('#original_post_status').val() != 'future' ) {
                //     publishOn = postL10n.publishOnFuture;
                //     $('#publish').val( postL10n.schedule );
                // } else if ( attemptedDate <= currentDate && $('#original_post_status').val() != 'publish' ) {
                //     publishOn = postL10n.publishOn;
                //     $('#publish').val( postL10n.publish );
                // } else {
                //     publishOn = postL10n.publishOnPast;
                //     $('#publish').val( postL10n.update );
                // }

                // If the date is the same, set it to trigger update events.
                // if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) {
                //     // Re-set to the current value.
                //     $('#timestamp').html(stamp);
                // } else {
                    $('#timestamp_to').html(
                        '\n<?=ACTIVE_TO_LABEL?><b>' +
                        postL10n.dateFormat
                            .replace( '%1$s', $( 'option[value="' + mm + '"]', '[name="mm_to"]' ).attr( 'data-text' ) )
                            .replace( '%2$s', parseInt( jj, 10 ) )
                            .replace( '%3$s', aa )
                            .replace( '%4$s', ( '00' + hh ).slice( -2 ) )
                            .replace( '%5$s', ( '00' + mn ).slice( -2 ) ) +
                            '</b> '
                    );
                // }

                // Add "privately published" to post status when applies.
                // if ( $postVisibilitySelect.find('input:radio:checked').val() == 'private' ) {
                //     $('#publish').val( postL10n.update );
                //     if ( 0 === optPublish.length ) {
                //         postStatus.append('<option value="publish">' + postL10n.privatelyPublished + '</option>');
                //     } else {
                //         optPublish.html( postL10n.privatelyPublished );
                //     }
                //     $('option[value="publish"]', postStatus).prop('selected', true);
                //     $('#misc-publishing-actions .edit-post-status').hide();
                // } else {
                //     if ( $('#original_post_status').val() == 'future' || $('#original_post_status').val() == 'draft' ) {
                //         if ( optPublish.length ) {
                //             optPublish.remove();
                //             postStatus.val($('#hidden_post_status').val());
                //         }
                //     } else {
                //         optPublish.html( postL10n.published );
                //     }
                //     if ( postStatus.is(':hidden') )
                //         $('#misc-publishing-actions .edit-post-status').show();
                // }

                // Update "Status:" to currently selected status.
                // $('#post-status-display').html($('option:selected', postStatus).text());

                // Show or hide the "Save Draft" button.
                // if ( $('option:selected', postStatus).val() == 'private' || $('option:selected', postStatus).val() == 'publish' ) {
                //     $('#save-post').hide();
                // } else {
                //     $('#save-post').show();
                //     if ( $('option:selected', postStatus).val() == 'pending' ) {
                //         $('#save-post').show().val( postL10n.savePending );
                //     } else {
                //         $('#save-post').show().val( postL10n.saveDraft );
                //     }
                // }
                return true;
            };

            // Edit publish time click.
            $timestampdiv.siblings('a.edit-timestamp_to').click( function( event ) {
                if ( $timestampdiv.is( ':hidden' ) ) {
                    $timestampdiv.slideDown( 'fast', function() {
                        $( 'input, select', $timestampdiv.find( '.timestamp-wrap' ) ).first().focus();
                    } );
                    $(this).hide();
                }
                event.preventDefault();
            });

            // Cancel editing the publish time and hide the settings.
            // $timestampdiv.find('.cancel-timestamp').click( function( event ) {
            //     $timestampdiv.slideUp('fast').siblings('a.edit-timestamp__').show().focus();
            //     $('#mm').val($('#hidden_mm').val());
            //     $('#jj').val($('#hidden_jj').val());
            //     $('#aa').val($('#hidden_aa').val());
            //     $('#hh').val($('#hidden_hh').val());
            //     $('#mn').val($('#hidden_mn').val());
            //     updateText();
            //     event.preventDefault();
            // });

            // Save the changed timestamp.
            $timestampdiv.find('.save-timestamp').click( function( event ) { // crazyhorse - multiple ok cancels
                if ( updateText() ) {
                    $timestampdiv.slideUp('fast');
                    $timestampdiv.siblings('a.edit-timestamp_to').show().focus();
                }
                event.preventDefault();
            });
        });
    </script>
    <?php endif; ?>

    <?php /* ?>
    <div class="misc-pub-section curtime misc-pub-curtime">
        <span id="timestamp_to">До: <b>20.11.2018 12:04</b></span>

        <a href="#edit_timestamp_to" class="edit-timestamp hide-if-no-js" role="button">
            <span aria-hidden="true">Изменить</span>
            <span class="screen-reader-text">Изменить дату и время</span>
        </a>

        <fieldset id="timestamp_todiv" class="hide-if-js">
            <legend class="screen-reader-text">Дата и время</legend>

            <div class="timestamp_to-wrap">
                <label>
                    <span class="screen-reader-text">Месяц</span>
                    <select id="mm" name="mm">
                        <option value="01" data-text="Янв">01-Янв</option>
                        <option value="02" data-text="Фев">02-Фев</option>
                        <option value="03" data-text="Мар">03-Мар</option>
                        <option value="04" data-text="Апр">04-Апр</option>
                        <option value="05" data-text="Май">05-Май</option>
                        <option value="06" data-text="Июн">06-Июн</option>
                        <option value="07" data-text="Июл">07-Июл</option>
                        <option value="08" data-text="Авг">08-Авг</option>
                        <option value="09" data-text="Сен">09-Сен</option>
                        <option value="10" data-text="Окт">10-Окт</option>
                        <option value="11" data-text="Ноя" selected="selected">11-Ноя</option>
                        <option value="12" data-text="Дек">12-Дек</option>
                    </select>
                </label>
                <label>
                    <span class="screen-reader-text">День</span>
                    <input type="text" id="jj" name="jj" value="20" size="2" maxlength="2" autocomplete="off">
                </label>,
                <label><span class="screen-reader-text">Год</span><input type="text" id="aa" name="aa" value="2018" size="4" maxlength="4" autocomplete="off"></label>
                в <label><span class="screen-reader-text">Час</span><input type="text" id="hh" name="hh" value="12" size="2" maxlength="2" autocomplete="off"></label>
                :<label><span class="screen-reader-text">Минута</span><input type="text" id="mn" name="mn" value="04" size="2" maxlength="2" autocomplete="off"></label>
            </div>

            <!-- <input type="hidden" id="ss" name="ss" value="02"> -->

            <!-- <input type="hidden" id="hidden_mm" name="hidden_mm" value="11">
            <input type="hidden" id="cur_mm" name="cur_mm" value="11">
            <input type="hidden" id="hidden_jj" name="hidden_jj" value="20">
            <input type="hidden" id="cur_jj" name="cur_jj" value="20">
            <input type="hidden" id="hidden_aa" name="hidden_aa" value="2018">
            <input type="hidden" id="cur_aa" name="cur_aa" value="2018">
            <input type="hidden" id="hidden_hh" name="hidden_hh" value="12">
            <input type="hidden" id="cur_hh" name="cur_hh" value="12">
            <input type="hidden" id="hidden_mn" name="hidden_mn" value="04">
            <input type="hidden" id="cur_mn" name="cur_mn" value="04"> -->

            <p>
                <a href="#edit_timestamp_to" class="save-timestamp_to hide-if-no-js button">OK</a>
                <a href="#edit_timestamp_to" class="cancel-timestamp_to hide-if-no-js button-cancel">Отмена</a>
            </p>
        </fieldset>
    </div>
    <?php */
}

add_action( 'pending_overdue_post', 'pending_overdue_post', $priority = 10, $accepted_args = 1 );
function pending_overdue_post($post_ID) {
    if( !$post_ID = intval($post_ID) ) return false;

    $_post = array(
        'ID' => $post_ID,
        'post_status' => 'pending',
    );

    wp_update_post( $_post );
}

if( ACTIVE_TO_USE_META ) {
    add_action( 'save_post', 'save_post_at_date_to', 10, 3 );
}
else {
    add_filter( 'wp_insert_post_data', 'update_modified_at_date_to', 10, 2  );
    add_action( 'pre_post_update', function( $post_ID, $data ) {
        wp_unschedule_event( strtotime( $data['post_modified'] ), 'pending_overdue_post', array($post_ID) );
    }, $priority = 10, $accepted_args = 2 );
}

function get_date_to_from_post() {
    $aa = $_POST['aa_to']; // Год
    $mm = $_POST['mm_to']; // Месяц
    $jj = $_POST['jj_to']; // День
    $hh = $_POST['hh_to']; // Час
    $mn = $_POST['mn_to']; // Минута
    $ss = $_POST['ss_to']; // Секунда

    $aa = ($aa <= 0 ) ? date('Y') : $aa;
    $mm = ($mm <= 0 ) ? date('n') : $mm;
    $jj = ($jj > 31 ) ? 31 : $jj;
    $jj = ($jj <= 0 ) ? date('j') : $jj;
    $hh = ($hh > 23 ) ? $hh -24 : $hh;
    $mn = ($mn > 59 ) ? $mn -60 : $mn;
    $ss = ($ss > 59 ) ? $ss -60 : $ss;

    return sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
}

/**
 * @since 2.7.0
 */
function update_modified_at_date_to($data, $postarr) {
    $data['post_modified'] = get_date_to_from_post();
    $data['post_modified_gmt'] = get_gmt_from_date( $data['post_modified'] );

    $time = strtotime( $data['post_modified_gmt'] );
    $now = current_time( 'timestamp', true );

    if( $time < $now && !empty($postarr['ID']) ) {
        wp_schedule_single_event( $time, 'pending_overdue_post', array(intval($postarr['ID'])) );
    }

    return $data;
}

function save_post_at_date_to($postid, $post, $update)
{
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;
    if ( !current_user_can( 'edit_page', $postid ) ) return false;
    if(empty($postid) || $_POST['post_type'] == 'article' ) return false;

    $post_date_to = get_date_to_from_post();
    $valid_date = wp_checkdate( $mm, $jj, $aa, $post_date_to );
    if ( !$valid_date ) {
        return new WP_Error( 'invalid_date', __( 'Invalid date to.' ) );
    }

    /**
     * @todo think about. #Need?
     */
    if( false ) {
        $post_date_to_gmt = get_gmt_from_date( $post_date_to );
        update_post_meta( $postid, ACTIVE_TO_POSTMETA . '_gmt', $post_date_to );
    }

    update_post_meta( $postid, ACTIVE_TO_POSTMETA, $post_date_to );
}