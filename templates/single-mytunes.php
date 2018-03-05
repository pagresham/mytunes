<?php
/**
 * Template Name: MyTunes
 *
 * Created by PhpStorm.
 * User: piercegresham
 * Date: 2/20/18
 * Time: 2:22 PM
 */
get_header();


/*
 * Possible solutions to video/link issues
 * 1. Only allow youtube video links, and parse the nuber out to get the thumbnail
 * 2. Allow user to add different thumbnail image with link?
 * 3. Find out how to import vids from media library.
 *
 * 1. Allow youtube videos, or links to a song?
 * Both raw url and share url, have the last 11 characters as the unique key for the vid
 * https://youtu.be/aL-dUZJydcw - one example of a 'share' address
 * https://www.youtube.com/watch?v=aL-dUZJydcw - example of a raw url
 * either https://youtu.be || https://www.youtube
 * - if first, get rest of characters after .be/
 * - if second, get characters after v=
 */


if( $post && get_post_type() == 'mytunes' ) {
    $metaStatus = get_post_meta(get_the_ID(), '_mytunes_status', true);
    $metaKey = get_post_meta(get_the_ID(), '_mytunes_key', true);
    $metaSource = get_post_meta(get_the_ID(), '_mytunes_source', true);
    $metaLinks = get_post_meta(get_the_ID(), '_mytunes_links', false);

    $key = ($metaKey) ? $metaKey : "";
    $source = ($metaSource) ? $metaSource : "";
    $status = ($metaStatus) ? $metaStatus : "";
    $links = (is_array($metaLinks) && count($metaLinks) > 0) ? $metaLinks[0] : array();

    // https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
    // https://img.youtube.com/vi/<insert-youtube-video-id-here>/default.jpg
    // https://img.youtube.com/vi/<insert-youtube-video-id-here>/1.jpg
    // https://youtu.be/  id?    NJssP7wkr4A


    function check_youtube_link( $link ) {

        $share_link = 'https://youtu.be/';
        $raw_link = 'https://www.youtube.com/watch?v=';

        // $share_link is in the url
        if( strpos($link, $raw_link) !== false ) { //           print_r($link);
            $code = substr($link, strlen($raw_link));
            return $code;
        }
        // $raw_link is in the url
        else if( strpos($link, $share_link) !== false ) {
            $code = substr($link, strlen($share_link));
            return $code;
        }
        return false;
    }

    ?>



    <div class="wrap">
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">
                <h5><?php echo get_the_title() ?></h5>
                <?php
                $content = $post->post_content;
                ?>

                <h5><?php echo $content; ?></h5>
                <table class="table">
                    <tr><th class="h6">Key</th><th class="h6">Difficulty</th><th class="h6">Source</th></tr>
                    <tr>
                        <td><?php echo $key ?></td>
                        <td><?php echo $source ?></td>
                        <td><?php echo $status ?></td>
                    </tr>
                </table>

                <div>
                    <h5>Links</h5>
                    <table>
                        <?php
                            foreach($links as $link) {
                                $code = (check_youtube_link( $link )) ? check_youtube_link( $link ) : "";
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( $link ); ?>">

                                        <img src="https://img.youtube.com/vi/<?php echo $code ?>/default.jpg" alt="">
                                    </a>
                                </td>
                                <td>
                                    <span><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_url( $link ) ?></a></span>
                                </td>
                            </tr>
                        <?php
                            }
                        ?>
                    </table>
                </div>
<!--                <iframe width="420" height="315"-->
<!--                        src="https://www.youtube.com/embed/tgbNymZ7vqY">-->
<!--                </iframe>-->
                <div>
                    <a type="button" class="btn btn-sm btn-info"
                       href="<?php echo get_edit_post_link($post->ID); ?>">Edit</a>
                </div>

            </main>
        </div>
    </div>

<?php } // end if $post...

get_footer();